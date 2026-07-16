<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\BlogController;

Route::middleware(['module:Blog'])->group(function () {
    Route::get('/blogs', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blogs/{blog}', [BlogController::class, 'show'])->name('blog.show');
});

Route::middleware(['auth', 'verified', 'module:Blog'])->group(function () {
    Route::resource('blogs', BlogController::class)->names('blog')->except(['index', 'show']);
});
