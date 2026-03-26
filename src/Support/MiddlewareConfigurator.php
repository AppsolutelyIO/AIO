<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Support;

use Appsolutely\AIO\Http\Middleware\ApplyThemeMiddleware;
use Appsolutely\AIO\Http\Middleware\QueryParamsToCookie;
use Appsolutely\AIO\Http\Middleware\RestrictAdminDomainToAdminRoutes;
use Appsolutely\AIO\Http\Middleware\RestrictRoutePrefixes;
use Appsolutely\AIO\Http\Middleware\SecurityHeaders;
use Appsolutely\AIO\Http\Middleware\StagingAccessGate;
use Appsolutely\AIO\Http\Middleware\ThrottleFormSubmissions;
use Illuminate\Foundation\Configuration\Middleware;

class MiddlewareConfigurator
{
    /**
     * Configure all AIO middleware on the application.
     *
     * Call this from bootstrap/app.php within the withMiddleware callback.
     * Registers global, web, and API middleware plus route aliases.
     */
    public static function configure(Middleware $middleware): void
    {
        // Staging access gate — blocks all routes when STAGING_ACCESS_ENABLED=true
        $middleware->prepend(StagingAccessGate::class);

        // Exclude staging cookie from Laravel's encryption (read/written as raw)
        $middleware->encryptCookies(except: ['staging_access']);

        // Web middleware group
        $middleware->web(append: [
            SecurityHeaders::class,
            RestrictAdminDomainToAdminRoutes::class,
            RestrictRoutePrefixes::class,
            ApplyThemeMiddleware::class,
            QueryParamsToCookie::class,
        ]);

        // API middleware group
        $middleware->api(append: [
            SecurityHeaders::class,
            RestrictRoutePrefixes::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'theme'         => ApplyThemeMiddleware::class,
            'throttle.form' => ThrottleFormSubmissions::class,
        ]);
    }
}
