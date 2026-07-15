<?php

use Illuminate\Support\Facades\Route;
use Modules\Shipment\Http\Controllers\ShipmentController;

Route::middleware(['auth', 'verified', 'module:Shipment'])->group(function () {
    Route::get('settings/shipping', [ShipmentController::class, 'edit'])->name('shipping.edit');
    Route::patch('settings/shipping', [ShipmentController::class, 'update'])->name('shipping.update');

    Route::resource('shipments', ShipmentController::class)->names('shipment');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', \App\Http\Middleware\EnsureTeamMembership::class, 'module:Shipment'])
    ->group(function () {
        Route::post('dashboard/orders/{transaction}/ship', [ShipmentController::class, 'shipOrder'])->name('dashboard.orders.ship');
        Route::post('dashboard/orders/{transaction}/shipping', [ShipmentController::class, 'updateShipping'])->name('dashboard.orders.shipping');
    });
