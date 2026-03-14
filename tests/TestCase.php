<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests;

use Appsolutely\AIO\AdminServiceProvider;
use Appsolutely\AIO\AIOServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Resolve AIO model factories from the correct namespace
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            if (str_starts_with($modelName, 'Appsolutely\\AIO\\Models\\')) {
                $modelBaseName = class_basename($modelName);

                return "Appsolutely\\AIO\\Database\\Factories\\{$modelBaseName}Factory";
            }

            // Fall back to Laravel's default convention
            $appNamespace = 'App\\';

            $modelBaseName = str_starts_with($modelName, $appNamespace.'Models\\')
                ? substr($modelName, \strlen($appNamespace.'Models\\'))
                : substr($modelName, \strlen($appNamespace));

            return 'Database\\Factories\\'.$modelBaseName.'Factory';
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Livewire\LivewireServiceProvider::class,
            \Qirolab\Theme\ThemeServiceProvider::class,
            \Spatie\ResponseCache\ResponseCacheServiceProvider::class,
            AdminServiceProvider::class,
            AIOServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'                  => 'sqlite',
            'database'                => ':memory:',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));

        // AIO helpers expect these config values
        $app['config']->set('appsolutely.prefix', 'aio_test');
        $app['config']->set('appsolutely.time_format', 'Y-m-d H:i:s');
        $app['config']->set('appsolutely.date_format', 'Y-m-d');
        $app['config']->set('appsolutely.local_timezone', 'UTC');

        // Load AIO config
        $app['config']->set('aio', require __DIR__.'/../config/aio.php');

        if (file_exists(__DIR__.'/../config/admin.php')) {
            $app['config']->set('admin', require __DIR__.'/../config/admin.php');
        }

        // Admin auth guard
        $app['config']->set('auth.guards.admin', [
            'driver'   => 'session',
            'provider' => 'admin',
        ]);

        $app['config']->set('auth.providers.admin', [
            'driver' => 'eloquent',
            'model'  => \Appsolutely\AIO\Models\Administrator::class,
        ]);
    }

    /**
     * Load Laravel default migrations before AIO migrations so that
     * base tables (users, cache, jobs) exist when AIO's alter-table
     * migrations run.
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(\Orchestra\Testbench\default_migration_path());
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
