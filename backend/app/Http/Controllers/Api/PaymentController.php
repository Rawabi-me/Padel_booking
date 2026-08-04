<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\ThawaniPaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private ThawaniPaymentService $thawani)
    {
    }

    /**
     * GET /api/payment/verify?ref=PB-XXXX
     * يستدعيها الفرونت اند بعد رجوع العميل من صفحة ثواني للتأكد الفعلي من حالة الدفع.
     */
    public function verify(Request $request)
    {
        $request->validate(['ref' => ['required', 'string']]);

        $booking = Booking::where('booking_reference', $request->query('ref'))->firstOrFail();

        if ($booking->payment_method !== 'thawani' || ! $booking->thawani_session_id) {
            return response()->json(['message' => 'لا يوجد جلسة دفع إلكتروني لهذا الحجز.'], 422);
        }

        $status = $this->thawani->checkSessionStatus($booking->thawani_session_id);

        $booking->update([
            'payment_status' => match ($status) {
                'paid' => 'paid',
                'expired' => 'failed',
                default => 'pending',
            },
        ]);

        return response()->json([
            'booking_reference' => $booking->booking_reference,
            'payment_status' => $booking->payment_status,
        ]);
    }
}
