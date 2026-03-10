<?php

namespace Appsolutely\AIO\Http\Middleware;

use Appsolutely\AIO\Admin;

class Application
{
    public function handle($request, \Closure $next, $app = null)
    {
        if ($app) {
            Admin::app()->switch($app);
        }

        return $next($request);
    }
}
