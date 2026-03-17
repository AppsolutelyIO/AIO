<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Illuminate\Support\Facades\View;
use Qirolab\Theme\Theme;
use Qirolab\Theme\ThemeViewFinder;

final readonly class ThemeService implements ThemeServiceInterface
{
    public function resolveThemeName(): ?string
    {
        $basicTheme = basic_config('theme');
        if (! empty($basicTheme) && file_exists(themed_absolute_path($basicTheme, 'views'))) {
            return $basicTheme;
        }

        return config('theme.active');
    }

    public function getParentTheme(): ?string
    {
        return config('theme.parent');
    }

    public function setupTheme(string $themeName, ?string $parentTheme = null): void
    {
        $viewFinder = View::getFinder();

        // Create new theme finder if needed
        if (! ($viewFinder instanceof ThemeViewFinder)) {
            $oldHints = $viewFinder->getHints();

            View::setFinder(app('theme.finder'));
            $viewFinder = View::getFinder();

            // Preserve namespace hints registered by service providers (e.g. "page-builder")
            foreach ($oldHints as $namespace => $paths) {
                foreach ($paths as $path) {
                    $viewFinder->addNamespace($namespace, $path);
                }
            }
        }

        // Set the active theme (registers site theme paths via Qirolab)
        Theme::set($themeName, $parentTheme);

        // Build explicit path cascade:
        // 1. Site active theme  (./themes/{name}/views)
        // 2. Package active theme (aio/themes/{name}/views)
        // 3. Site parent theme  (./themes/{parent}/views)
        // 4. Package parent theme (aio/themes/{parent}/views)
        // 5. Default resources  (./resources/views)
        $this->buildViewPathCascade($viewFinder, $themeName, $parentTheme);
    }

    public function ensureSetup(): void
    {
        $themeName = Theme::active() ?? $this->resolveThemeName();

        if ($themeName === null) {
            return;
        }

        $parentTheme = $this->getParentTheme();
        $this->setupTheme($themeName, $parentTheme);
    }

    public function getThemeViewPath(string $themeName): string
    {
        // Site themes take priority over package themes
        $sitePath = $this->getSiteThemeViewPath($themeName);
        if ($sitePath !== null) {
            return $sitePath;
        }

        $packagePath = $this->getPackageThemeViewPath($themeName);
        if ($packagePath !== null) {
            return $packagePath;
        }

        return themed_absolute_path($themeName, 'views');
    }

    /**
     * Get the view path for a theme in the site's themes directory.
     */
    private function getSiteThemeViewPath(string $themeName): ?string
    {
        $path = base_path('themes/' . $themeName . '/views');

        return is_dir($path) ? $path : null;
    }

    /**
     * Get the view path for a theme bundled with the AIO package.
     */
    private function getPackageThemeViewPath(string $themeName): ?string
    {
        $path = dirname(__DIR__, 2) . '/themes/' . $themeName . '/views';

        return is_dir($path) ? $path : null;
    }

    /**
     * Build the view path cascade in priority order:
     * site active → package active → site parent → package parent → resources/views
     */
    private function buildViewPathCascade(ThemeViewFinder $viewFinder, string $themeName, ?string $parentTheme): void
    {
        $existingPaths = $viewFinder->getPaths();
        $cascadePaths  = [];

        // 1. Site active theme
        $sitePath = $this->getSiteThemeViewPath($themeName);
        if ($sitePath !== null) {
            $cascadePaths[] = $sitePath;
        }

        // 2. Package active theme
        $packagePath = $this->getPackageThemeViewPath($themeName);
        if ($packagePath !== null) {
            $cascadePaths[] = $packagePath;
        }

        if ($parentTheme) {
            // 3. Site parent theme
            $siteParentPath = $this->getSiteThemeViewPath($parentTheme);
            if ($siteParentPath !== null) {
                $cascadePaths[] = $siteParentPath;
            }

            // 4. Package parent theme
            $packageParentPath = $this->getPackageThemeViewPath($parentTheme);
            if ($packageParentPath !== null) {
                $cascadePaths[] = $packageParentPath;
            }
        }

        // 5. Append remaining paths (resources/views, etc.) preserving their order
        //    Skip non-existent directories to prevent view:cache failures
        foreach ($existingPaths as $path) {
            if (! in_array($path, $cascadePaths, true) && is_dir($path)) {
                $cascadePaths[] = $path;
            }
        }

        $viewFinder->setPaths($cascadePaths);
    }
}
