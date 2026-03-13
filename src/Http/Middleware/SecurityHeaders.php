<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to add security headers to HTTP responses
 *
 * Adds common security headers to protect against various attacks:
 * - XSS protection
 * - Clickjacking protection
 * - MIME type sniffing prevention
 * - Referrer policy
 * - Content Security Policy (configurable)
 * - HSTS (HTTP Strict Transport Security) for HTTPS
 */
final class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff', false);

        // Enable XSS protection (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block', false);

        // Control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);

        // Permissions Policy (formerly Feature-Policy)
        $response->headers->set('Permissions-Policy', $this->getPermissionsPolicy(), false);

        // Content Security Policy (basic, can be customized per route if needed)
        $csp = $this->getContentSecurityPolicy($request);
        if ($csp) {
            $response->headers->set('Content-Security-Policy', $csp, false);
        }

        // HSTS - Only add in production with HTTPS
        if ($this->shouldAddHsts($request)) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload',
                false
            );
        }

        return $response;
    }

    /**
     * Get Content Security Policy header value
     */
    private function getContentSecurityPolicy(Request $request): ?string
    {
        // Basic CSP - can be customized per route if needed
        // For now, use a permissive policy that allows common patterns
        // In production, this should be tightened based on actual requirements
        $csp = config('appsolutely.security.csp', null);

        if ($csp) {
            return $csp;
        }

        // Default permissive CSP for development
        // Production should have stricter CSP
        if (app()->isProduction()) {
            // Stricter CSP for production
            return "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; media-src 'self' https:; connect-src 'self' https:; frame-ancestors 'self';";
        }

        // More permissive for development
        return "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https: http:; font-src 'self' data: https: http:; media-src 'self' https: http:; connect-src 'self' https: http: ws: wss:; frame-ancestors 'self';";
    }

    /**
     * Get Permissions Policy header value
     */
    private function getPermissionsPolicy(): string
    {
        // Restrict certain browser features for security
        return implode(', ', [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
        ]);
    }

    /**
     * Determine if HSTS header should be added
     */
    private function shouldAddHsts(Request $request): bool
    {
        // Only add HSTS in production and when using HTTPS
        return app()->isProduction()
            && $request->secure();
    }
}
