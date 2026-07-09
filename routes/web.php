<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StorefrontController;

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/shop', [StorefrontController::class, 'shop'])->name('shop');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('dashboard/products/{product}/stock', [DashboardController::class, 'updateStock'])->name('dashboard.products.stock');
        Route::post('dashboard/orders/{transaction}/ship', [DashboardController::class, 'shipOrder'])->name('dashboard.orders.ship');
        Route::post('dashboard/orders/{transaction}/cancel', [DashboardController::class, 'cancelOrder'])->name('dashboard.orders.cancel');
        Route::post('dashboard/coupons', [DashboardController::class, 'storeCoupon'])->name('dashboard.coupons.store');
        Route::post('dashboard/coupons/{coupon}/toggle', [DashboardController::class, 'toggleCoupon'])->name('dashboard.coupons.toggle');
        Route::delete('dashboard/coupons/{coupon}', [DashboardController::class, 'destroyCoupon'])->name('dashboard.coupons.destroy');
    });

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('customer.dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
