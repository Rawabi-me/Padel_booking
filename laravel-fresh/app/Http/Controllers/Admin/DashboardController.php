<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'courts_count' => Court::count(),
            'active_courts_count' => Court::where('is_active', true)->count(),
            'bookings_today' => Booking::whereHas('slots', fn ($q) => $q->whereDate('date', now()->toDateString()))->count(),
            'revenue_this_month' => Booking::where('payment_status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount'),
        ];

        $recentBookings = Booking::with('slots')->latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentBookings'));
    }
}
