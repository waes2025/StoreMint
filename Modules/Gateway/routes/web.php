<?php

use Illuminate\Support\Facades\Route;
use Modules\Gateway\Http\Controllers\GatewayController;

Route::middleware(['auth', 'verified', 'module:Gateway'])->group(function () {
    Route::get('settings/gateways', [GatewayController::class, 'edit'])->name('gateways.edit');
    Route::patch('settings/gateways', [GatewayController::class, 'update'])->name('gateways.update');

    Route::resource('gateways', GatewayController::class)->names('gateway');
});
