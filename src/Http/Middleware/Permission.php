<?php

namespace Appsolutely\AIO\Http\Middleware;

use Appsolutely\AIO\Admin;
use Appsolutely\AIO\Exception\RuntimeException;
use Appsolutely\AIO\Http\Auth\Permission as Checker;
use Appsolutely\AIO\Support\UrlHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Permission
{
    /**
     * @var string
     */
    protected $middlewarePrefix = 'admin.permission:';

    /**
     * Handle an incoming request.
     *
     * @param  array  $args
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, ...$args)
    {
        $user = Admin::user();

        if (
            ! $user
            || ! empty($args)
            || ! config('admin.permission.enable')
            || $this->shouldPassThrough($request)
            || $user->isAdministrator()
            || $this->checkRoutePermission($request)
        ) {
            return $next($request);
        }

        if (! $user->allPermissions()->first(function ($permission) use ($request) {
            return $permission->shouldPassThrough($request);
        })) {
            Checker::error();
        }

        return $next($request);
    }

    /**
     * If the route of current request contains a middleware prefixed with 'admin.permission:',
     * then it has a manually set permission middleware, we need to handle it first.
     *
     * @return bool
     */
    public function checkRoutePermission(Request $request)
    {
        if (! $middleware = collect($request->route()->middleware())->first(function ($middleware) {
            return Str::startsWith($middleware, $this->middlewarePrefix);
        })) {
            return false;
        }

        $args = explode(',', str_replace($this->middlewarePrefix, '', $middleware));

        $method = array_shift($args);

        if (! method_exists(Checker::class, $method)) {
            throw new RuntimeException("Invalid permission method [$method].");
        }

        Checker::$method($args);

        return true;
    }

    /**
     * @param  Request  $request
     * @return bool
     */
    protected function isApiRoute($request)
    {
        return $request->routeIs(admin_api_route_name('*'));
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param  Request  $request
     * @return bool
     */
    public function shouldPassThrough($request)
    {
        if ($this->isApiRoute($request) || Authenticate::shouldPassThrough($request)) {
            return true;
        }

        $excepts = array_merge(
            (array) config('admin.permission.except', []),
            Admin::context()->getArray('permission.except')
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
