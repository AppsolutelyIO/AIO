<?php

namespace Appsolutely\AIO\Tests\Integration;

use Appsolutely\AIO\AdminServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [AdminServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);

        $app['config']->set('admin', array_merge(
            require __DIR__.'/../../config/admin.php',
            [
                'auth' => [
                    'enable' => false,
                    'guard'  => 'admin',
                    'guards' => [
                        'admin' => [
                            'driver'   => 'session',
                            'provider' => 'admin',
                        ],
                    ],
                    'providers' => [
                        'admin' => [
                            'driver' => 'eloquent',
                            'model'  => \Appsolutely\AIO\Models\Administrator::class,
                        ],
                    ],
                ],
                'permission' => ['enable' => false],
                'https' => false,
                'assets_server' => '',
                'layout' => [
                    'color' => 'default',
                    'body_class' => '',
                    'sidebar_style' => 'light',
                    'sidebar_dark' => false,
                ],
                'route' => [
                    'prefix' => 'admin',
                    'middleware' => ['web'],
                ],
            ]
        ));
    }
}
