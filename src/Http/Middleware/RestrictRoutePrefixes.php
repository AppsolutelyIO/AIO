<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Middleware;

use Appsolutely\AIO\Services\Contracts\RouteRestrictionServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RestrictRoutePrefixes
{
    public function __construct(
        protected RouteRestrictionServiceInterface $routeRestrictionService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $firstSegment = $request->segment(1);

        if ($firstSegment && $this->routeRestrictionService->isPrefixDisabled($firstSegment)) {
            abort(404);
        }

        return $next($request);
    }
}
