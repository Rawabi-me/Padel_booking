<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\CourtClosure;
use App\Models\PricingTier;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * يحتوي هذا الصف على منطق النظام الأهم:
 * - حساب الأوقات المتاحة للعميل بدون كشف اسم أي ملعب.
 * - عرض الوقت طالما هناك ملعب واحد متاح على الأقل (يختفي فقط عند استنفاد كل الملاعب).
 * - تخصيص ملعب متاح بشكل عشوائي فقط عند تأكيد الحجز فعلياً.
 * - حساب السعر حسب عدد الساعات (عروض الأسعار) لكل يوم على حدة.
 */
class BookingAvailabilityService
{
    private const SLOT_MINUTES = 60;

    /**
     * إرجاع الأوقات المتاحة ليوم واحد، كل وقت مع عدد الملاعب المتاحة (بدون أسماء).
     */
    public function getAvailableSlotsForDate(Carbon $date): array
    {
        $courts = Court::where('is_active', true)->with('workingHours', 'closures')->get();
        $dayOfWeek = $date->dayOfWeek; // 0=Sunday ... 6=Saturday

        // كل الحجوزات المؤكدة في هذا اليوم مسبقاً، مجمعة حسب (court_id, start_time)
        $bookedPairs = BookingSlot::whereDate('date', $date->toDateString())
            ->get(['court_id', 'start_time'])
            ->map(fn ($s) => $s->court_id.'|'.substr($s->start_time, 0, 5))
            ->flip();

        $now = Carbon::now();
        $isToday = $date->isSameDay($now);

        // slot_start_time => عدد الملاعب المتاحة
        $availability = [];

        foreach ($courts as $court) {
            if ($this->isCourtClosedOnDate($court, $date)) {
                continue;
            }

            $wh = $court->workingHours->firstWhere('day_of_week', $dayOfWeek);
            if (! $wh || $wh->is_closed || ! $wh->opens_at || ! $wh->closes_at) {
                continue; // لا يوجد دوام معرف لهذا اليوم = مغلق
            }

            foreach ($this->generateHourSlots($wh->opens_at, $wh->closes_at) as [$start, $end]) {
                if ($isToday && Carbon::parse($date->toDateString().' '.$start)->lessThanOrEqualTo($now)) {
                    continue; // منع حجز وقت ماضٍ
                }

                $key = $court->id.'|'.$start;
                if (isset($bookedPairs[$key])) {
                    continue; // هذا الملعب محجوز فعلاً في هذا الوقت
                }

                $availability[$start] = ($availability[$start] ?? 0) + 1;
            }
        }

        ksort($availability);

        $result = [];
        foreach ($availability as $start => $count) {
            $result[] = [
                'start_time' => $start,
                'end_time' => Carbon::parse($start)->addMinutes(self::SLOT_MINUTES)->format('H:i'),
                'available_courts_count' => $count, // معلومة مفيدة فقط، لا تكشف أي هوية ملعب
            ];
        }

        return $result;
    }

    /**
     * إرجاع معرفات الملاعب المتاحة فعلياً لتاريخ ووقت محددين (تستخدم داخلياً وقت التأكيد فقط).
     */
    public function getAvailableCourtIds(Carbon $date, string $startTime): array
    {
        $dayOfWeek = $date->dayOfWeek;
        $courts = Court::where('is_active', true)->with('workingHours', 'closures')->get();

        $alreadyBookedCourtIds = BookingSlot::whereDate('date', $date->toDateString())
            ->where('start_time', $startTime)
            ->pluck('court_id')
            ->all();

        $available = [];
        foreach ($courts as $court) {
            if (in_array($court->id, $alreadyBookedCourtIds, true)) {
                continue;
            }
            if ($this->isCourtClosedOnDate($court, $date)) {
                continue;
            }
            $wh = $court->workingHours->firstWhere('day_of_week', $dayOfWeek);
            if (! $wh || $wh->is_closed || ! $wh->opens_at || ! $wh->closes_at) {
                continue;
            }
            $opens = substr($wh->opens_at, 0, 5);
            $closesMinusHour = Carbon::parse($wh->closes_at)->subMinutes(self::SLOT_MINUTES)->format('H:i');
            if ($startTime < $opens || $startTime > $closesMinusHour) {
                continue;
            }
            $available[] = $court->id;
        }

        return $available;
    }

    /**
     * إنشاء حجز كامل (قد يشمل عدة ساعات وعدة أيام ضمن نفس العملية) بشكل ذري (atomic).
     * $slots = [['date' => 'Y-m-d', 'start_time' => 'HH:MM'], ...]
     */
    public function createBooking(array $slots, string $paymentMethod, ?string $name, string $phone, ?string $email): Booking
    {
        if (empty($slots)) {
            throw new RuntimeException('لا يوجد أي وقت محدد للحجز.');
        }

        // تجميع الساعات حسب اليوم لحساب السعر (العرض يعتمد على عدد الساعات في نفس اليوم)
        $byDate = [];
        foreach ($slots as $slot) {
            $byDate[$slot['date']][] = $slot['start_time'];
        }

        $tiers = PricingTier::orderByDesc('min_hours')->get();
        if ($tiers->isEmpty()) {
            throw new RuntimeException('لم يتم تحديد أي سعر من قبل الإدارة بعد.');
        }

        return DB::transaction(function () use ($slots, $byDate, $tiers, $paymentMethod, $name, $phone, $email) {
            $now = Carbon::now();

            // احتساب السعر لكل يوم بناءً على عدد ساعاته المطلوبة
            $pricePerHourByDate = [];
            foreach ($byDate as $date => $hours) {
                $hoursCount = count($hours);
                $tier = $tiers->first(fn ($t) => $hoursCount >= $t->min_hours);
                $pricePerHourByDate[$date] = $tier ? (float) $tier->price_per_hour : (float) $tiers->last()->price_per_hour;
            }

            $totalAmount = 0;
            $createdSlotsData = [];

            foreach ($slots as $slot) {
                $date = Carbon::parse($slot['date'])->startOfDay();
                $startTime = $slot['start_time'];

                if ($date->isSameDay($now) && Carbon::parse($date->toDateString().' '.$startTime)->lessThanOrEqualTo($now)) {
                    throw new RuntimeException("لا يمكن حجز وقت ماضٍ ({$startTime}).");
                }

                $availableCourtIds = $this->getAvailableCourtIds($date, $startTime);
                if (empty($availableCourtIds)) {
                    throw new RuntimeException("عذراً، الوقت {$startTime} بتاريخ {$slot['date']} لم يعد متاحاً.");
                }

                // تخصيص عشوائي لملعب متاح - مع إعادة المحاولة في حال تعارض نادر (race condition)
                shuffle($availableCourtIds);
                $assignedCourtId = null;
                foreach ($availableCourtIds as $courtId) {
                    if (BookingSlot::where('court_id', $courtId)
                        ->whereDate('date', $date->toDateString())
                        ->where('start_time', $startTime)
                        ->exists()) {
                        continue;
                    }
                    $assignedCourtId = $courtId;
                    break;
                }

                if (! $assignedCourtId) {
                    throw new RuntimeException("عذراً، الوقت {$startTime} بتاريخ {$slot['date']} لم يعد متاحاً.");
                }

                $price = $pricePerHourByDate[$slot['date']];
                $totalAmount += $price;

                $createdSlotsData[] = [
                    'court_id' => $assignedCourtId,
                    'date' => $date->toDateString(),
                    'start_time' => $startTime,
                    'end_time' => Carbon::parse($startTime)->addMinutes(self::SLOT_MINUTES)->format('H:i'),
                    'price' => $price,
                ];
            }

            $booking = Booking::create([
                'booking_reference' => strtoupper('PB-'.Str::random(8)),
                'customer_phone' => $phone,
                'customer_name' => $name,
                'customer_email' => $email,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'total_amount' => $totalAmount,
                'status' => 'confirmed',
            ]);

            try {
                foreach ($createdSlotsData as $data) {
                    $booking->slots()->create($data);
                }
            } catch (QueryException $e) {
                // تعارض نادر جداً على مستوى قاعدة البيانات (unique constraint) => إلغاء العملية بالكامل
                throw new RuntimeException('تعذر إتمام الحجز، أحد الأوقات تم حجزه للتو من عميل آخر. الرجاء المحاولة مجدداً.');
            }

            return $booking->load('slots');
        });
    }

    private function isCourtClosedOnDate(Court $court, Carbon $date): bool
    {
        foreach ($court->closures as $closure) {
            if ($date->between($closure->start_date, $closure->end_date)) {
                return true;
            }
        }

        return CourtClosure::whereNull('court_id')
            ->whereDate('start_date', '<=', $date->toDateString())
            ->whereDate('end_date', '>=', $date->toDateString())
            ->exists();
    }

    /** @return array<array{0:string,1:string}> */
    private function generateHourSlots(string $opensAt, string $closesAt): array
    {
        $slots = [];
        $cursor = Carbon::parse($opensAt);
        $close = Carbon::parse($closesAt);

        while ($cursor->copy()->addMinutes(self::SLOT_MINUTES)->lessThanOrEqualTo($close)) {
            $start = $cursor->format('H:i');
            $end = $cursor->copy()->addMinutes(self::SLOT_MINUTES)->format('H:i');
            $slots[] = [$start, $end];
            $cursor->addMinutes(self::SLOT_MINUTES);
        }

        return $slots;
    }
}
