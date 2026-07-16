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

        // Pathao location lookups
        Route::get('dashboard/shipments/cities', [ShipmentController::class, 'getPathaoCities'])->name('dashboard.shipments.cities');
        Route::get('dashboard/shipments/zones/{cityId}', [ShipmentController::class, 'getPathaoZones'])->name('dashboard.shipments.zones');
        Route::get('dashboard/shipments/areas/{zoneId}', [ShipmentController::class, 'getPathaoAreas'])->name('dashboard.shipments.areas');

        // Pathao shipment actions
        Route::post('dashboard/orders/{transaction}/pathao-estimate', [ShipmentController::class, 'estimatePathaoCharge'])->name('dashboard.orders.pathao-estimate');
        Route::post('dashboard/orders/{transaction}/pathao-book', [ShipmentController::class, 'bookPathaoShipment'])->name('dashboard.orders.pathao-book');
        Route::post('dashboard/orders/{transaction}/pathao-sync', [ShipmentController::class, 'syncPathaoShipment'])->name('dashboard.orders.pathao-sync');
        Route::post('dashboard/orders/{transaction}/pathao-cancel', [ShipmentController::class, 'cancelPathaoShipment'])->name('dashboard.orders.pathao-cancel');
    });
