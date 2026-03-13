<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Middleware;

use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetThemeMiddleware
{
    public function __construct(
        protected ThemeServiceInterface $themeService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $themeName = $this->themeService->resolveThemeName();

        if ($themeName && $this->themeService->shouldApplyTheme($request->path())) {
            $parentTheme = $this->themeService->getParentTheme();
            $this->themeService->setupTheme($themeName, $parentTheme);
        }

        return $next($request);
    }
}
