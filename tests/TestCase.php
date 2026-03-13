<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests;

use Appsolutely\AIO\AdminServiceProvider;
use Appsolutely\AIO\AIOServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AdminServiceProvider::class,
            AIOServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);

        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));

        // Load AIO config
        $app['config']->set('aio', require __DIR__.'/../config/aio.php');

        if (file_exists(__DIR__.'/../config/admin.php')) {
            $app['config']->set('admin', require __DIR__.'/../config/admin.php');
        }
    }
}
