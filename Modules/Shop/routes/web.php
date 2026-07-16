<?php

use Illuminate\Support\Facades\Route;
use Modules\Shop\Http\Controllers\ShopController;

Route::middleware(['auth', 'verified', 'module:Shop'])->group(function () {
    Route::resource('shops', ShopController::class)->names('shop');
});
