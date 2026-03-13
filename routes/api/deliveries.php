<?php

declare(strict_types=1);

use Appsolutely\AIO\Http\Controllers\Api\DeliveryController;
use Illuminate\Support\Facades\Route;

/**
 * Delivery API routes — for external systems to fulfill virtual product deliveries.
 *
 * GET  /api/deliveries/{token}  — Check delivery token status (auth required)
 * POST /api/deliveries/fulfill  — Fulfill a delivery via token (auth required)
 */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('deliveries/{token}', [DeliveryController::class, 'show'])->name('api.deliveries.show');
    Route::post('deliveries/fulfill', [DeliveryController::class, 'fulfill'])->name('api.deliveries.fulfill');
});
