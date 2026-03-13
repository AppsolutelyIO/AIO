<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to throttle form submissions
 *
 * Applies rate limiting to prevent spam and abuse of form endpoints.
 * Uses the 'form-submission' rate limiter configured in AppServiceProvider.
 */
final class ThrottleFormSubmissions
{
    public function __construct(
        private readonly ThrottleRequests $throttleRequests
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $this->throttleRequests->handle(
            $request,
            $next,
            'form-submission'
        );
    }
}
