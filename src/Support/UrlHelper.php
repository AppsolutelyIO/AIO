<?php

namespace Appsolutely\AIO\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UrlHelper
{
    /**
     * 给 URL 添加查询参数.
     */
    public static function withQuery(?string $url, array $query = []): ?string
    {
        if (! $url || ! $query) {
            return $url;
        }

        $array = explode('?', $url);

        $url = $array[0];

        parse_str($array[1] ?? '', $originalQuery);

        return $url . '?' . http_build_query(array_merge($originalQuery, $query));
    }

    /**
     * 从 URL 移除查询参数.
     */
    public static function withoutQuery($url, $keys): string
    {
        if (! Str::contains($url, '?') || ! $keys) {
            return $url;
        }

        if ($keys instanceof Arrayable) {
            $keys = $keys->toArray();
        }

        $keys = (array) $keys;

        $urlInfo = parse_url($url);

        parse_str($urlInfo['query'], $query);

        Arr::forget($query, $keys);

        $baseUrl = explode('?', $url)[0];

        return $query
            ? $baseUrl . '?' . http_build_query($query)
            : $baseUrl;
    }

    /**
     * 从当前完整 URL 移除查询参数.
     */
    public static function fullUrlWithoutQuery($keys): string
    {
        return static::withoutQuery(request()->fullUrl(), $keys);
    }

    /**
     * 判断 URL 是否包含指定查询参数.
     */
    public static function hasQuery(string $url, $keys): bool
    {
        $value = explode('?', $url);

        if (empty($value[1])) {
            return false;
        }

        parse_str($value[1], $query);

        foreach ((array) $keys as $key) {
            if (Arr::has($query, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 匹配请求路径.
     */
    public static function matchRequestPath($path, ?string $current = null): bool
    {
        $request = request();
        $current = $current ?: $request->decodedPath();

        if (Str::contains($path, ':')) {
            [$methods, $path] = explode(':', $path);

            $methods = array_map('strtoupper', explode(',', $methods));

            if (! empty($methods) && ! in_array($request->method(), $methods, true)) {
                return false;
            }
        }

        if ($request->routeIs($path) || $request->routeIs(admin_route_name($path))) {
            return true;
        }

        if (! Str::contains($path, '*')) {
            return $path === $current;
        }

        $path = str_replace(['*', '/'], ['([0-9a-z-_,])*', "\/"], $path);

        return (bool) preg_match("/$path/i", $current);
    }
}
