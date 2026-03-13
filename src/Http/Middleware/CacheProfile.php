<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

final class CacheProfile extends CacheAllSuccessfulGetRequests
{
    /**
     * Determine if the given request should be cached.
     */
    public function shouldCacheRequest(Request $request): bool
    {
        // Don't cache if parent says no
        if (! parent::shouldCacheRequest($request)) {
            return false;
        }

        // Exclude Livewire requests (they use X-Livewire header, not X-Requested-With)
        // This includes: component updates, lazy loading, polling, wire:navigate, etc.
        if ($request->hasHeader('X-Livewire')) {
            return false;
        }

        // Exclude requests to admin domain (configurable via ADMIN_ROUTE_DOMAIN in .env)
        $adminDomain = config('admin.route.domain');
        if ($adminDomain && $request->getHost() === $adminDomain) {
            return false;
        }

        // Exclude admin dashboard routes (configurable via ADMIN_ROUTE_PREFIX in .env)
        $adminPrefix = config('admin.route.prefix', 'admin');
        if ($request->is($adminPrefix) || $request->is($adminPrefix . '/*')) {
            return false;
        }

        // Exclude requests when admin is logged in
        // This ensures admin users always see fresh content
        $adminGuard = config('admin.auth.guard', 'admin');
        if (Auth::guard($adminGuard)->check()) {
            return false;
        }

        // Exclude authenticated routes (routes that require authentication)
        // This ensures any authenticated user-specific content is not cached
        if ($request->user()) {
            return false;
        }

        return true;
    }
}
