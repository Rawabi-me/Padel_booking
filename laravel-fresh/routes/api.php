<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PricingController;
use Illuminate\Support\Facades\Route;

// ============ واجهة العميل (Public API) - لا تتطلب أي مصادقة ============
Route::get('/availability', AvailabilityController::class);
Route::get('/pricing', PricingController::class);
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/bookings/{reference}', [BookingController::class, 'show']);
Route::get('/payment/verify', [PaymentController::class, 'verify']);
