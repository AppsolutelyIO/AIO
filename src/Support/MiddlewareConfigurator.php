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
use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    /**
     * Configure AIO exception handling on the application.
     *
     * Call this from bootstrap/app.php within the withExceptions callback.
     * Registers themed 404 error pages with theme setup.
     */
    public static function configureExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'code'    => 404,
                    'message' => 'Route not found',
                ], 404);
            }

            // Set up theme for error pages
            app(ThemeServiceInterface::class)->ensureSetup();

            // Try to find themed error view
            if (view()->exists('errors.404')) {
                return response()->view('errors.404', [], 404);
            }

            // Fallback to Laravel's default
        });
    }
}
