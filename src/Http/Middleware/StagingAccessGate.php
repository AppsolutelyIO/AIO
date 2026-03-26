<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate middleware for staging/preview environments.
 *
 * When STAGING_ACCESS_ENABLED=true, all routes require a valid token.
 * The token is derived from APP_URL — unique per environment, stable
 * across redeployments. Visitors without a valid token receive a 404.
 *
 * Usage: visit any URL with ?token=<value> to authenticate.
 * Run `php artisan staging:token` to retrieve the token.
 * A cookie is set for subsequent requests (7-day TTL).
 */
class StagingAccessGate
{
    private const COOKIE_NAME = 'staging_access';

    private const COOKIE_LIFETIME_MINUTES = 60 * 24 * 7; // 7 days

    /** @var list<string> Routes that handle their own token verification */
    private const EXCLUDED_PATHS = [
        'api/staging-registry',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('aio.staging_access_enabled')) {
            return $next($request);
        }

        if ($request->is(...self::EXCLUDED_PATHS)) {
            return $next($request);
        }

        $token        = static::generateToken();
        $expectedHash = hash('sha256', $token);

        // Check query param token
        if ($request->query('token') === $token) {
            $cookie = Cookie::make(
                self::COOKIE_NAME,
                $expectedHash,
                self::COOKIE_LIFETIME_MINUTES,
                '/',
                null,
                true,  // secure
                true,  // httpOnly
                false, // raw
                'Lax',
            );

            return redirect()
                ->to($this->stripTokenParam($request))
                ->withCookie($cookie);
        }

        // Read raw cookie — this middleware runs before EncryptCookies,
        // so we read directly from the request headers
        $cookieValue = $request->cookies->get(self::COOKIE_NAME);

        if ($cookieValue === $expectedHash) {
            return $next($request);
        }

        // No valid token, no valid cookie → pretend nothing exists
        abort(404);
    }

    /**
     * Derive a deterministic token from the application URL.
     */
    public static function generateToken(?string $url = null): string
    {
        return substr(hash('sha256', 'staging-gate:' . ($url ?? config('app.url'))), 0, 32);
    }

    private function stripTokenParam(Request $request): string
    {
        $query = $request->except('token');

        $url = $request->url();

        if (! empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }
}
