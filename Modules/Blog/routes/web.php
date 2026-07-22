<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\BlogController;

Route::middleware(['module:Blog'])->group(function () {
    Route::get('/blogs', [BlogController::class, 'index'])->name('blog.index');
});

Route::middleware(['auth', 'verified', 'module:Blog'])->group(function () {
    Route::get('/admin/blogs', [BlogController::class, 'adminIndex'])->name('blog.adminIndex');
    Route::resource('blogs', BlogController::class)->names('blog')->except(['index', 'show']);
});

Route::middleware(['module:Blog'])->group(function () {
    Route::get('/blogs/{blog}', [BlogController::class, 'show'])->name('blog.show');
});
