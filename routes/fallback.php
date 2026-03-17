<?php

use Appsolutely\AIO\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::localized(function () {
    Route::middleware('web')->group(function () {
        Route::get('', [PageController::class, 'show'])->name('home');
        Route::get('{slug?}', [PageController::class, 'show'])
            ->where('slug', aio_slug_pattern())
            ->name('pages.show');
    });
});
