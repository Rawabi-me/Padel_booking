<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ClosureController;
use App\Http\Controllers\Admin\CourtController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PricingController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('courts', CourtController::class)->except(['show']);
    Route::post('courts/{court}/working-hours', [CourtController::class, 'updateWorkingHours'])->name('courts.working-hours');

    Route::get('closures', [ClosureController::class, 'index'])->name('closures.index');
    Route::post('closures', [ClosureController::class, 'store'])->name('closures.store');
    Route::delete('closures/{closure}', [ClosureController::class, 'destroy'])->name('closures.destroy');

    Route::get('pricing', [PricingController::class, 'index'])->name('pricing.index');
    Route::post('pricing', [PricingController::class, 'store'])->name('pricing.store');
    Route::put('pricing/{pricing}', [PricingController::class, 'update'])->name('pricing.update');
    Route::delete('pricing/{pricing}', [PricingController::class, 'destroy'])->name('pricing.destroy');

    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
});
