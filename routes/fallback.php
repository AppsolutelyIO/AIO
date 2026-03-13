<?php

use Appsolutely\AIO\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::localized(function () {
    Route::get('', [PageController::class, 'show'])->name('home');
    Route::get('{slug?}', [PageController::class, 'show'])
        ->where('slug', '[a-zA-Z0-9\/_\-\.~%]+')
        ->name('pages.show');
});
