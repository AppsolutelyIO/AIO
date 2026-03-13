<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class QueryParamsToCookie
{
    /**
     * Handle an incoming request.
     *
     * Captures configured query parameters from the URL and persists them as cookies.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        /** @var array<string, array{cookie_name?: string, lifetime?: int, overwrite?: bool}> $parameters */
        $parameters      = config('query_params_cookie.parameters', []);
        $defaultLifetime = (int) config('query_params_cookie.default_lifetime', 43200);

        foreach ($parameters as $queryParam => $options) {
            $value = $request->query($queryParam);

            if (! is_string($value) || $value === '') {
                continue;
            }

            $cookieName = $options['cookie_name'] ?? $queryParam;
            $lifetime   = $options['lifetime'] ?? $defaultLifetime;
            $overwrite  = $options['overwrite'] ?? true;

            if (! $overwrite && $request->cookie($cookieName) !== null) {
                continue;
            }

            $response->headers->setCookie(
                cookie($cookieName, $value, $lifetime)
            );
        }

        return $response;
    }
}
