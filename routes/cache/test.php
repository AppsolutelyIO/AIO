<?php

use Appsolutely\AIO\Services\MenuService;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    $basicTheme=\Appsolutely\AIO\Config\BasicConfig::getTitle();
    dd($basicTheme);
})->name('routes');


Route::get('/routes', function () {
    $routes = collect(Route::getRoutes())->map(function ($route) {
        return [
            'uri'        => $route->uri(),
            'methods'    => $route->methods(),
            'name'       => $route->getName(),
            'action'     => $route->getActionName(),
            'middleware' => $route->gatherMiddleware(),
        ];
    });
dd($routes->toArray());
})->name('routes');
