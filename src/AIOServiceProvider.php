<?php

declare(strict_types=1);

namespace Appsolutely\AIO;

use Illuminate\Support\ServiceProvider;

class AIOServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/aio.php', 'aio');

        $this->registerServices();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
    }

    /**
     * Register service interface bindings from config.
     *
     * Host applications can override any binding by rebinding
     * the interface in their own service provider (which runs after package providers).
     */
    protected function registerServices(): void
    {
        foreach ((array) config('aio.services') as $interface => $implementation) {
            $this->app->singleton($interface, $implementation);
        }
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            [__DIR__.'/../config/aio.php' => config_path('aio.php')],
            'aio-config'
        );
    }
}
