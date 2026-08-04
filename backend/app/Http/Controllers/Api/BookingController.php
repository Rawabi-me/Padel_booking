<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Services\BookingAvailabilityService;
use App\Services\ThawaniPaymentService;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BookingController extends Controller
{
    public function __construct(
        private BookingAvailabilityService $availability,
        private ThawaniPaymentService $thawani,
    ) {
    }

    /**
     * POST /api/bookings
     * ينشئ الحجز، ويعيد رابط الدفع الإلكتروني إن تم اختيار ثواني.
     */
    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->availability->createBooking(
                slots: $request->input('slots'),
                paymentMethod: $request->input('payment_method'),
                name: $request->input('customer_name'),
                phone: $request->input('customer_phone'),
                email: $request->input('customer_email'),
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $payload = [
            'booking_reference' => $booking->booking_reference,
            'total_amount' => $booking->total_amount,
            'payment_method' => $booking->payment_method,
        ];

        if ($booking->payment_method === 'thawani') {
            try {
                $successUrl = config('app.frontend_url').'/payment/success?ref='.$booking->booking_reference;
                $cancelUrl = config('app.frontend_url').'/payment/cancel?ref='.$booking->booking_reference;
                $payload['payment_url'] = $this->thawani->createCheckoutSession($booking, $successUrl, $cancelUrl);
            } catch (RuntimeException $e) {
                Log::error('Thawani session creation failed: '.$e->getMessage());

                return response()->json(['message' => 'تم إنشاء الحجز لكن تعذر فتح صفحة الدفع الإلكتروني، الرجاء التواصل معنا أو المحاولة لاحقاً.'], 502);
            }
        }

        return response()->json($payload, 201);
    }

    /**
     * GET /api/bookings/{reference}
     * لعرض حالة الحجز/الدفع للعميل (بدون كشف اسم الملعب).
     */
    public function show(string $reference)
    {
        $booking = Booking::where('booking_reference', $reference)->firstOrFail();

        return response()->json([
            'booking_reference' => $booking->booking_reference,
            'status' => $booking->status,
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
            'total_amount' => $booking->total_amount,
            'slots' => $booking->slots->map(fn ($s) => [
                'date' => $s->date->toDateString(),
                'start_time' => substr($s->start_time, 0, 5),
                'end_time' => substr($s->end_time, 0, 5),
                'price' => $s->price,
            ]),
        ]);
    }
}
