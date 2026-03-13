<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/**
 * Authenticated user routes
 * Requires authentication and email verification
 */
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
