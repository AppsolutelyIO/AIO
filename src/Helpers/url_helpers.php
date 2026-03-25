<?php

declare(strict_types=1);

if (! function_exists('path_join')) {
    /**
     * Concatenate a base path and a file ensuring a single slash boundary.
     */
    function path_join(string $basePath, string $file): string
    {
        return rtrim($basePath, '/') . '/' . ltrim($file, '/');
    }
}

if (! function_exists('app_url')) {
    /**
     * Generate a full app URL from the configured base URL, or fall back to Laravel's url() helper.
     */
    function app_url(string $uri = ''): string
    {
        $baseUrl = config('appsolutely.url');

        if ($baseUrl) {
            return path_join($baseUrl, $uri);
        }

        return url($uri);
    }
}

if (! function_exists('dashboard_url')) {
    /**
     * Generate a full dashboard/admin URL from the configured admin domain.
     */
    function dashboard_url(string $uri = ''): string
    {
        $baseUrl = config('admin.route.domain');

        if ($baseUrl) {
            return URL::formatScheme('') . path_join($baseUrl, $uri);
        }

        return url($uri);
    }
}

if (! function_exists('admin_route_prefix')) {
    /**
     * Get the admin route prefix with a trailing slash.
     */
    function admin_route_prefix(): string
    {
        return config('admin.route.prefix') . '/';
    }
}

if (! function_exists('upload_url')) {
    /**
     * Generate a dashboard file upload URL for viewing uploaded files.
     */
    function upload_url(string $uri = ''): string
    {
        $storagePath = config('appsolutely.storage.dash_files', 'uploads/');
        $uri         = admin_route_prefix() . $storagePath . $uri;

        return dashboard_url($uri);
    }
}

if (! function_exists('upload_to_api')) {
    /**
     * Generate the file upload API endpoint URL with optional query parameters.
     */
    function upload_to_api(?string $class = null, ?string $id = null, ?string $type = null, ?string $token = null): string
    {
        $data = array_filter([
            'class'  => $class,
            'id'     => $id,
            'type'   => $type,
            '_token' => $token,
        ]);

        $baseUrl = admin_route('api.files.upload');

        return empty($data) ? $baseUrl : $baseUrl . '?' . http_build_query($data);
    }
}

if (! function_exists('build_hash')) {
    /**
     * Generate a short cache-busting hash from the Vite build manifest.
     */
    function build_hash(): string
    {
        return cache()->remember('build_hash', 3600, function () {
            $manifestPath = public_path('build/manifest.json');

            if (file_exists($manifestPath)) {
                return substr(md5((string) filemtime($manifestPath) . file_get_contents($manifestPath)), 0, 8);
            }

            return substr(md5((string) config('app.version', time())), 0, 8);
        });
    }
}

if (! function_exists('asset_url')) {
    /**
     * Generate a full asset URL with optional cache-busting hash.
     * Returns absolute URLs unchanged. Checks for a dedicated asset CDN first,
     * then falls back to the app storage assets path.
     */
    function asset_url(?string $uri = null, bool $withHash = true): string
    {
        $uri  = $uri ?? '';
        $hash = $withHash ? '?v=' . build_hash() : '';

        if ($uri !== '' && str_starts_with($uri, 'http')) {
            return $uri;
        }

        $cdnUrl = config('appsolutely.asset_url');

        if (! empty($cdnUrl)) {
            return path_join($cdnUrl, $uri) . $hash;
        }

        $storagePath = config('appsolutely.storage.assets', 'assets/');

        return app_url($storagePath . $uri . $hash);
    }
}

if (! function_exists('public_url')) {
    /**
     * Generate a URL for publicly accessible storage files.
     */
    function public_url(string $uri = ''): string
    {
        $storagePath = config('appsolutely.storage.public', 'public/');

        return app_url($storagePath . $uri);
    }
}

if (! function_exists('slug_pattern')) {
    /**
     * Build the regex pattern for the CMS catch-all route, excluding reserved slugs.
     *
     * Reserved slugs (e.g. "up" for Laravel's health check) are defined in
     * config('aio.routes.reserved_slugs') and excluded via negative lookahead.
     */
    function slug_pattern(): string
    {
        $reserved = config('aio.routes.reserved_slugs', []);

        $healthPath = config('aio.routes.health');
        if ($healthPath) {
            $reserved[] = ltrim($healthPath, '/');
        }

        // Framework paths that must never be captured by the catch-all route.
        // Livewire v4 uses a hashed prefix (e.g. "livewire-f89580e0/...").
        $frameworkPrefixes = ['livewire'];

        $lookaheads = [];

        // Exact-match exclusions (anchored with $)
        foreach ($reserved as $slug) {
            $lookaheads[] = preg_quote($slug, '/') . '$';
        }

        // Prefix exclusions (match start, no $ anchor)
        foreach ($frameworkPrefixes as $prefix) {
            $lookaheads[] = preg_quote($prefix, '/');
        }

        if (empty($lookaheads)) {
            return '[a-zA-Z0-9\/_\-\.~%]+';
        }

        return '(?!' . implode('|', $lookaheads) . ')[a-zA-Z0-9\/_\-\.~%]+';
    }
}

if (! function_exists('normalize_slug')) {
    /**
     * Normalize a slug for consistent storage and lookup.
     * Trims input and collapses multiple slashes.
     *
     * @param  string|null  $slug  Raw slug (e.g. from route, redirect_url, or DB)
     * @param  bool  $withLeadingSlash  True: /path (for URL resolution). False: path (segment format, no trim needed)
     * @return string Normalized slug
     */
    function normalize_slug(?string $slug, bool $withLeadingSlash = true): string
    {
        if ($slug === null) {
            return $withLeadingSlash ? '/' : '';
        }

        $slug = trim($slug);
        if ($slug === '') {
            return $withLeadingSlash ? '/' : '';
        }

        if (! str_starts_with($slug, '/')) {
            $slug = '/' . $slug;
        }

        $slug = preg_replace('#/+#', '/', $slug);
        $slug = $slug === '/' ? '/' : rtrim($slug, '/');

        return $withLeadingSlash ? $slug : trim($slug, '/');
    }
}

if (! function_exists('current_uri')) {
    /**
     * Get the current request URI path, optionally including the query string.
     */
    function current_uri(bool $withQueryString = false): string
    {
        if ($withQueryString) {
            return request()->getRequestUri();
        }

        return request()->getPathInfo();
    }
}

if (! function_exists('nested_url')) {
    /**
     * Generate a full URL by appending a path to the current request URI.
     */
    function nested_url(string $path = ''): string
    {
        $currentPath = current_uri();

        if (empty($path)) {
            return app_url($currentPath);
        }

        return app_url(path_join($currentPath, $path));
    }
}

if (! function_exists('app_uri')) {
    /**
     * Ensure a path starts with a leading slash, or return a no-op link for null.
     */
    function app_uri(?string $path = ''): string
    {
        if (is_null($path)) {
            return 'javascript:void(0);';
        }

        return '/' . ltrim($path, '/');
    }
}
