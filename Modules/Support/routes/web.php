<?php

use Illuminate\Support\Facades\Route;
use Modules\Support\Http\Controllers\AdminSupportController;
use Modules\Support\Http\Controllers\SupportController;
use App\Http\Middleware\EnsureTeamMembership;

// Customer facing support routes
Route::middleware(['auth', 'verified', 'module:Support'])->group(function () {
    Route::post('customer/support/tickets', [SupportController::class, 'store'])->name('customer.support.store');
    Route::post('customer/support/tickets/{ticket}/reply', [SupportController::class, 'reply'])->name('customer.support.reply');
});

// Admin/Team facing support routes
Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class, 'module:Support'])
    ->group(function () {
        Route::get('support', [AdminSupportController::class, 'index'])->name('admin.support.index');
        Route::post('support/{ticket}/reply', [AdminSupportController::class, 'reply'])->name('admin.support.reply');
        Route::post('support/{ticket}/status', [AdminSupportController::class, 'updateStatus'])->name('admin.support.status');
    });
