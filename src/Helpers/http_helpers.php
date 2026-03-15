<?php

declare(strict_types=1);
use Illuminate\Http\Request;

if (! function_exists('client_ip')) {
    /**
     * Get the client IP from the request, respecting proxy and CDN headers.
     * Priority: CF-Connecting-IP (Cloudflare) -> True-Client-IP (Akamai etc.) ->
     * X-Forwarded-For (leftmost = client) -> X-Real-IP -> REMOTE_ADDR.
     * When behind Cloudflare, X-Forwarded-For may contain the edge IP; use
     * CF-Connecting-IP for the real visitor IP.
     *
     * @return string|null Valid IP or null
     */
    function client_ip(?Request $request = null): ?string
    {
        $request = $request ?? request();
        $ip      = null;

        // Cloudflare: real visitor IP (X-Forwarded-For can be overwritten by edge)
        $cfIp = $request->header('CF-Connecting-IP');
        if ($cfIp !== null && $cfIp !== '' && filter_var($cfIp, FILTER_VALIDATE_IP)) {
            return $cfIp;
        }

        // Akamai / some CDNs
        $trueClientIp = $request->header('True-Client-IP');
        if ($trueClientIp !== null && $trueClientIp !== '' && filter_var($trueClientIp, FILTER_VALIDATE_IP)) {
            return $trueClientIp;
        }

        // X-Forwarded-For: "client, proxy1, proxy2" — leftmost is the original client
        $forwarded = $request->header('X-Forwarded-For');
        if ($forwarded !== null && $forwarded !== '') {
            $parts = array_map('trim', explode(',', (string) $forwarded));
            foreach ($parts as $part) {
                if ($part !== '' && filter_var($part, FILTER_VALIDATE_IP)) {
                    $ip = $part;
                    break;
                }
            }
        }

        if ($ip === null) {
            $realIp = $request->header('X-Real-IP');
            if ($realIp !== null && $realIp !== '' && filter_var($realIp, FILTER_VALIDATE_IP)) {
                $ip = $realIp;
            }
        }

        if ($ip === null) {
            $ip = $request->ip();
        }

        return ($ip !== null && filter_var($ip, FILTER_VALIDATE_IP) ? $ip : $request->ip()) ?? null;
    }
}

if (! function_exists('api_bearer_or_query_token')) {
    /**
     * Resolve API token from request: Authorization Bearer header or query parameter.
     * Use for APIs that accept token in header (Authorization: Bearer <token>) or query (?token=...).
     *
     * @param  Request|null  $request  Defaults to request()
     * @param  string  $queryKey  Query parameter name (default "token")
     * @return string|null The token or null if not present
     */
    function api_bearer_or_query_token(?Request $request = null, string $queryKey = 'token'): ?string
    {
        $request = $request ?? request();
        $header  = $request->header('Authorization')
            ?? $request->server('REDIRECT_HTTP_AUTHORIZATION');

        if ($header !== null && $header !== '' && str_starts_with(strtolower($header), 'bearer ')) {
            $token = trim(substr($header, 7));

            return $token !== '' ? $token : null;
        }

        $queryToken = $request->query($queryKey);

        return is_string($queryToken) && $queryToken !== '' ? $queryToken : null;
    }
}
