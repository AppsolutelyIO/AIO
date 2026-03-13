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

    public function shouldApplyTheme(string $path): bool
    {
        $adminPrefix = config('admin.route.prefix', 'admin');

        return ! str_starts_with($path, $adminPrefix);
    }

    public function setupTheme(string $themeName, ?string $parentTheme = null): void
    {
        $viewFinder = View::getFinder();

        // Create new theme finder if needed
        if (! ($viewFinder instanceof ThemeViewFinder)) {
            // Force the view finder to be the theme finder
            View::setFinder(app('theme.finder'));
            $viewFinder = View::getFinder();
        }

        // Set the active theme
        Theme::set($themeName, $parentTheme);

        // Ensure the view finder is properly set
        $paths = $viewFinder->getPaths();

        // Verify the paths include the theme path
        $themePath = $this->getThemeViewPath($themeName);

        // Make sure the theme path is the first path in the list
        if (! in_array($themePath, $paths, true) || array_search($themePath, $paths, true) !== 0) {
            $newPaths = array_merge([$themePath], array_diff($paths, [$themePath]));
            $viewFinder->setPaths($newPaths);
        }
    }

    public function getThemeViewPath(string $themeName): string
    {
        return themed_absolute_path($themeName, 'views');
    }
}
