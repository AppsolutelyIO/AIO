<?php

declare(strict_types=1);

use Appsolutely\AIO\Http\Middleware\StagingAccessGate;
use Appsolutely\AIO\Services\StagingRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Staging Registry API
|--------------------------------------------------------------------------
|
| Returns all registered staging/preview environments. Protected by the
| same staging access token — only accessible to someone who already
| has a valid token for this environment.
|
*/

Route::get('/staging-registry', function (Request $request): JsonResponse {
    $token = StagingAccessGate::generateToken();

    if ($request->query('token') !== $token) {
        abort(404);
    }

    $registry = app(StagingRegistryService::class);
    $registry->heartbeat();

    return response()->json([
        'environments' => $registry->list(),
    ]);
})->name('api.staging-registry');
