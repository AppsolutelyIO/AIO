<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Middleware;

use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApplyThemeMiddleware
{
    public function __construct(
        protected ThemeServiceInterface $themeService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->themeService->ensureSetup();

        return $next($request);
    }
}
