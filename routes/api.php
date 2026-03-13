<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Main API routes file
 *
 * Routes are organized by feature/domain:
 * - api/releases.php - Release API routes
 */

// User authentication route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum')->name('api.user');

// Load feature-based route files
require __DIR__ . '/api/deliveries.php';
require __DIR__ . '/api/forms.php';
require __DIR__ . '/api/releases.php';
