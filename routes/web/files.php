<?php

declare(strict_types=1);

use Appsolutely\AIO\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

/**
 * File and asset routes
 * These routes serve public assets and storage files
 */
Route::middleware(['throttle:web'])->group(function () {
    Route::get('assets/{path?}', [FileController::class, 'retrieve'])
        ->where('path', '(.*)')
        ->name('file.public.assets');

    Route::get('storage/{path?}', [FileController::class, 'retrieve'])
        ->where('path', '(.*)')
        ->name('file.public.storage');
});
