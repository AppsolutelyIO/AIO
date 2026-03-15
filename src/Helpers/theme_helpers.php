<?php

declare(strict_types=1);

use Qirolab\Theme\Theme;

if (! function_exists('themed_absolute_path')) {
    /**
     * Get the path to a theme's directory.
     *
     * Checks the site's themes directory first, then falls back to the AIO
     * package's bundled themes directory.
     *
     * @param  string  $themeName  The name of the theme
     * @param  string  $path  The path within the theme directory
     * @return string The full path to the theme directory or a path within it
     */
    function themed_absolute_path(string $themeName = '', string $path = ''): string
    {
        $sitePath = base_path(themed_path($themeName));

        // Check if the theme exists in the AIO package when not found in site
        if (! empty($themeName) && ! is_dir($sitePath)) {
            $packagePath = dirname(__DIR__, 2) . '/themes/' . $themeName;
            if (is_dir($packagePath)) {
                $sitePath = $packagePath;
            }
        }

        if (empty($path)) {
            return $sitePath;
        }

        return $sitePath . '/' . ltrim($path, '/');
    }
}

if (! function_exists('themed_build_path')) {
    /**
     * Get the build path for a theme's assets.
     *
     * @param  string  $themeName  The name of the theme
     * @return string The build path for the theme
     */
    function themed_build_path(string $themeName = ''): string
    {
        return 'build/' . themed_path($themeName);
    }
}

if (! function_exists('themed_path')) {
    /**
     * Get the relative path to a theme directory.
     *
     * @param  string  $themeName  The name of the theme (defaults to active theme)
     * @return string The relative path to the theme directory
     */
    function themed_path(string $themeName = ''): string
    {
        if (empty($themeName)) {
            $themeName = Theme::active();
        }

        return 'themes/' . $themeName;
    }
}

if (! function_exists('themed_view')) {
    function themed_view($view, $data = [], $mergeData = [])
    {
        if (! view()->exists($view)) {
            throw new \RuntimeException(
                sprintf('View "%s" not found in theme "%s".', $view, Theme::active())
            );
        }

        return view($view, $data, $mergeData);
    }
}

if (! function_exists('themed_assets')) {
    function themed_assets(string $path, ?string $theme = null): string
    {
        $theme           = $theme ?? config('appsolutely.theme.name');
        $buildPath       = themed_build_path($theme);

        if (app()->isProduction()) {
            $manifest = cache()->rememberForever("vite_manifest_{$theme}", function () use ($buildPath) {
                return load_vite_manifest($buildPath);
            });
        } else {
            $manifest = load_vite_manifest($buildPath);
        }

        $key = path_join(themed_path(), $path);

        if (! isset($manifest[$key])) {
            throw new \RuntimeException(
                "Image asset '{$key}' not found in Vite manifest. Build path: {$buildPath}"
            );
        }

        return asset(path_join($buildPath, $manifest[$key]['file']));
    }
}

if (! function_exists('load_vite_manifest')) {
    function load_vite_manifest(string $path): array
    {
        $manifestPath    = public_path(path_join($path, 'manifest.json'));
        if (! file_exists($manifestPath)) {
            throw new \RuntimeException(
                "Vite manifest.json not found at path: {$manifestPath}. Ensure Vite build has been run."
            );
        }

        return json_decode(file_get_contents($manifestPath), true);
    }
}

if (! function_exists('themed_styles')) {
    /**
     * Get CSS URLs for theme styling.
     * Used by Page Builder canvas to style rendered blocks.
     * Returns config styles (e.g. Bootstrap CDN) plus theme's built CSS from Vite.
     *
     * @return array<int, string> Array of CSS URLs
     */
    function themed_styles(): array
    {
        $configStyles = config('appsolutely.theme.styles') ?? [];
        $configStyles = is_array($configStyles) ? $configStyles : [];

        $themePath    = themed_path();
        $buildPath    = themed_build_path();
        $manifestPath = public_path(path_join($buildPath, 'manifest.json'));

        if (! file_exists($manifestPath)) {
            return $configStyles;
        }

        try {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (! is_array($manifest)) {
                return $configStyles;
            }

            $cssKeys = [
                $themePath . '/sass/app.scss',
                $themePath . '/css/app.css',
            ];

            foreach ($cssKeys as $key) {
                $entry = $manifest[$key] ?? null;
                if (! is_array($entry)) {
                    continue;
                }

                if (isset($entry['file']) && str_ends_with(strtolower((string) $entry['file']), '.css')) {
                    return [asset(path_join($buildPath, $entry['file']))];
                }

                if (isset($entry['css']) && is_array($entry['css'])) {
                    return array_map(
                        fn (string $f) => asset(path_join($buildPath, $f)),
                        $entry['css']
                    );
                }
            }
        } catch (\Throwable) {
            // Ignore manifest errors
        }

        return $configStyles;
    }
}
