<?php

use Illuminate\Support\Facades\Route;
use Modules\Cart\Http\Controllers\CartController;
use Modules\Cart\Http\Controllers\CheckoutController;
use Modules\Cart\Http\Controllers\CouponsController;
use Modules\Cart\Http\Controllers\OrdersController;

Route::middleware(['module:Cart'])->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.place');

    Route::get('/cart-items', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart-items', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart-items/{product_id}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::prefix('{current_team}')
        ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureTeamMembership::class])
        ->group(function () {
            Route::post('dashboard/orders/{transaction}/cancel', [OrdersController::class, 'cancelOrder'])->name('dashboard.orders.cancel');
            Route::post('dashboard/coupons', [CouponsController::class, 'storeCoupon'])->name('dashboard.coupons.store');
            Route::put('dashboard/coupons/{coupon}', [CouponsController::class, 'updateCoupon'])->name('dashboard.coupons.update');
            Route::post('dashboard/coupons/{coupon}/toggle', [CouponsController::class, 'toggleCoupon'])->name('dashboard.coupons.toggle');
            Route::delete('dashboard/coupons/{coupon}', [CouponsController::class, 'destroyCoupon'])->name('dashboard.coupons.destroy');
            Route::delete('dashboard/carts/{cart}', [CartController::class, 'destroyCart'])->name('dashboard.carts.destroy');
        });
});
