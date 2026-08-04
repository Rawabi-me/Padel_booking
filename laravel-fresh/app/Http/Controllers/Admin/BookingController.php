<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * عرض جميع الحجوزات مع إمكانية الفلترة حسب الملعب، التاريخ، حالة الحجز،
     * طريقة الدفع، ورقم هاتف العميل.
     */
    public function index(Request $request)
    {
        $query = Booking::with('slots.court')->latest();

        if ($request->filled('phone')) {
            $query->where('customer_phone', 'like', '%'.$request->input('phone').'%');
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }
        if ($request->filled('date') || $request->filled('court_id')) {
            $query->whereHas('slots', function ($q) use ($request) {
                if ($request->filled('date')) {
                    $q->whereDate('date', $request->input('date'));
                }
                if ($request->filled('court_id')) {
                    $q->where('court_id', $request->input('court_id'));
                }
            });
        }

        $bookings = $query->paginate(20)->withQueryString();
        $courts = Court::orderBy('name')->get();

        return view('admin.bookings.index', compact('bookings', 'courts'));
    }
}
