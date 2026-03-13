<?php

declare(strict_types=1);

use Appsolutely\AIO\Http\Controllers\Api\FormEntriesPullController;
use Illuminate\Support\Facades\Route;

/**
 * Form entries pull API (external systems).
 * Requires form_slug; optional filters: from_date, to_date, entry_id_from, entry_id_to.
 * Auth: Bearer token or query param "token" must match the form's api_access_token.
 * Both with and without trailing slash so nginx redirects still hit the API and return JSON.
 */
Route::get('forms/entries', FormEntriesPullController::class)
    ->middleware('throttle:api')
    ->name('api.forms.entries.pull');
Route::get('forms/entries/', FormEntriesPullController::class)
    ->middleware('throttle:api');
