<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(private BookingAvailabilityService $availability)
    {
    }

    /**
     * GET /api/availability?date=2026-08-05
     * يعيد الأوقات المتاحة فقط (بدون كشف أي أسماء ملاعب).
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $date = Carbon::parse($request->query('date'));

        return response()->json([
            'date' => $date->toDateString(),
            'slots' => $this->availability->getAvailableSlotsForDate($date),
        ]);
    }
}
