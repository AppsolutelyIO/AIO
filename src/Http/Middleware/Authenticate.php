<?php

namespace Appsolutely\AIO\Http\Middleware;

use Appsolutely\AIO\Admin;
use Appsolutely\AIO\Support\UrlHelper;
use Closure;
use Illuminate\Http\Request;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (
            ! config('admin.auth.enable', true)
            || ! Admin::guard()->guest()
            || $this->shouldPassThrough($request)
        ) {
            return $next($request);
        }

        return admin_redirect('auth/login', 401);
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param  Request  $request
     * @return bool
     */
    public static function shouldPassThrough($request)
    {
        $excepts = array_merge(
            (array) config('admin.auth.except', []),
            Admin::context()->getArray('auth.except')
        );

        foreach ($excepts as $except) {
            if ($request->routeIs($except) || $request->routeIs(admin_route_name($except))) {
                return true;
            }

            $except = admin_base_path($except);

            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if (UrlHelper::matchRequestPath($except)) {
                return true;
            }
        }

        return false;
    }
}
