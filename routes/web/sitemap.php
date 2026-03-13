<?php

declare(strict_types=1);

use Appsolutely\AIO\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/**
 * Sitemap routes
 * These routes are not localized as they're XML files
 */
Route::middleware([])->group(function () {
    Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
    Route::get('sitemap-{type}.xml', [SitemapController::class, 'type'])
        ->where('type', 'page|article|product')
        ->name('sitemap.type');
});
