<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When ADMIN_ROUTE_DOMAIN is set, the admin domain must only serve admin routes.
 * Requests to the admin domain for non-admin paths (e.g. /, /about) return 404.
 */
final class RestrictAdminDomainToAdminRoutes
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminDomain = config('admin.route.domain');
        if (! $adminDomain || $request->getHost() !== $adminDomain) {
            return $next($request);
        }

        $adminPrefix = config('admin.route.prefix', 'admin');
        if (! $adminPrefix) {
            return $next($request);
        }

        $path        = $request->path();
        $isAdminPath = $path === $adminPrefix || str_starts_with($path, $adminPrefix . '/');
        if (! $isAdminPath) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'code'    => 404,
                    'message' => 'Route not found',
                ], 404);
            }

            $viewPath = resource_path('views/errors/404.blade.php');

            return response(
                file_exists($viewPath) ? view()->file($viewPath) : 'Not Found',
                404
            );
        }

        return $next($request);
    }
}
