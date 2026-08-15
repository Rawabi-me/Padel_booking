<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\CourtWorkingHour;
use App\Models\PricingTier;
use App\Services\BookingAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private BookingAvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BookingAvailabilityService();
        PricingTier::create(['min_hours' => 1, 'price_per_hour' => 10]);
    }

    private function makeCourt(string $name = 'Court A'): Court
    {
        $court = Court::create(['name' => $name, 'is_active' => true]);
        foreach (range(0, 6) as $day) {
            CourtWorkingHour::create([
                'court_id' => $court->id,
                'day_of_week' => $day,
                'opens_at' => '09:00',
                'closes_at' => '23:00',
                'is_closed' => false,
            ]);
        }
        return $court;
    }

    /** @test */
    public function available_slots_never_expose_court_names_or_ids(): void
    {
        $this->makeCourt();
        $date = Carbon::tomorrow();

        $slots = $this->service->getAvailableSlotsForDate($date);

        $this->assertNotEmpty($slots);
        foreach ($slots as $slot) {
            $this->assertArrayNotHasKey('court_id', $slot);
            $this->assertArrayNotHasKey('court_name', $slot);
            $this->assertArrayHasKey('available_courts_count', $slot);
        }
    }

    /** @test */
    public function a_time_slot_disappears_only_after_all_courts_are_booked(): void
    {
        $this->makeCourt('Court A');
        $this->makeCourt('Court B');
        $date = Carbon::tomorrow();

        $this->service->createBooking(
            slots: [['date' => $date->toDateString(), 'start_time' => '10:00']],
            paymentMethod: 'pay_on_arrival',
            name: null,
            phone: '99999999',
            email: null,
        );

        $slots = collect($this->service->getAvailableSlotsForDate($date));
        $tenAm = $slots->firstWhere('start_time', '10:00');

        $this->assertNotNull($tenAm);
        $this->assertEquals(1, $tenAm['available_courts_count']);
    }

    /** @test */
    public function past_time_slots_are_never_returned_for_today(): void
    {
        $this->makeCourt();
        Carbon::setTestNow(Carbon::today()->setTime(15, 0));

        $slots = collect($this->service->getAvailableSlotsForDate(Carbon::today()));

        $this->assertTrue($slots->every(fn ($s) => $s['start_time'] > '15:00'));

        Carbon::setTestNow();
    }
}