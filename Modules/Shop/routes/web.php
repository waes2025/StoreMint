<?php

use Illuminate\Support\Facades\Route;
use Modules\Shop\Http\Controllers\ShopController;
use Modules\Shop\Http\Controllers\StorefrontController;

Route::get('/', [StorefrontController::class, 'index'])->name('home');

Route::middleware(['module:Shop'])->group(function () {
    Route::get('/shop', [StorefrontController::class, 'shop'])->name('shop');
});

Route::middleware(['auth', 'verified', 'module:Shop'])->group(function () {
    Route::resource('shops', ShopController::class)->names('shop');
});
