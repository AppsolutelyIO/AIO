<?php

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Admin;
use Appsolutely\AIO\Models\Administrator;
use Appsolutely\AIO\Tests\Integration\TestCase as IntegrationTestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadAdminTranslations();
        $this->runAdminMigrations();
    }

    protected function loadAdminTranslations()
    {
        $this->app['translator']->addNamespace('admin', __DIR__.'/../../resources/lang');

        // Load the admin translations into the default namespace so __('admin.xxx') works
        $langPath = __DIR__.'/../../resources/lang';
        if (is_dir($langPath)) {
            $this->app['translator']->addJsonPath($langPath);
            foreach (glob($langPath.'/*', GLOB_ONLYDIR) as $localeDir) {
                $locale = basename($localeDir);
                foreach (glob($localeDir.'/*.php') as $file) {
                    $group = basename($file, '.php');
                    $translations = require $file;
                    if (is_array($translations)) {
                        $this->app['translator']->addLines(
                            collect($translations)->mapWithKeys(fn ($v, $k) => ["{$group}.{$k}" => $v])->all(),
                            $locale
                        );
                    }
                }
            }
        }
    }

    protected function runAdminMigrations()
    {
        $config = config('admin.database');

        // Users table
        if (! Schema::hasTable($config['users_table'])) {
            Schema::create($config['users_table'], function ($table) {
                $table->bigIncrements('id');
                $table->string('username', 120)->unique();
                $table->string('password', 80);
                $table->string('name');
                $table->string('avatar')->nullable();
                $table->string('remember_token', 100)->nullable();
                $table->timestamps();
            });
        }

        // Roles table
        if (! Schema::hasTable($config['roles_table'])) {
            Schema::create($config['roles_table'], function ($table) {
                $table->bigIncrements('id');
                $table->string('name', 50);
                $table->string('slug', 50)->unique();
                $table->timestamps();
            });
        }

        // Permissions table
        if (! Schema::hasTable($config['permissions_table'])) {
            Schema::create($config['permissions_table'], function ($table) {
                $table->bigIncrements('id');
                $table->string('name', 50);
                $table->string('slug', 50)->unique();
                $table->string('http_method')->nullable();
                $table->text('http_path')->nullable();
                $table->integer('order')->default(0);
                $table->bigInteger('parent_id')->default(0);
                $table->timestamps();
            });
        }

        // Menu table
        if (! Schema::hasTable($config['menu_table'])) {
            Schema::create($config['menu_table'], function ($table) {
                $table->bigIncrements('id');
                $table->bigInteger('parent_id')->default(0);
                $table->integer('order')->default(0);
                $table->string('title', 50);
                $table->string('icon', 50)->nullable();
                $table->string('uri', 50)->nullable();
                $table->timestamps();
            });
        }

        // Pivot tables
        if (! Schema::hasTable($config['role_users_table'])) {
            Schema::create($config['role_users_table'], function ($table) {
                $table->bigInteger('role_id');
                $table->bigInteger('user_id');
                $table->unique(['role_id', 'user_id']);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable($config['role_permissions_table'])) {
            Schema::create($config['role_permissions_table'], function ($table) {
                $table->bigInteger('role_id');
                $table->bigInteger('permission_id');
                $table->unique(['role_id', 'permission_id']);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable($config['role_menu_table'])) {
            Schema::create($config['role_menu_table'], function ($table) {
                $table->bigInteger('role_id');
                $table->bigInteger('menu_id');
                $table->unique(['role_id', 'menu_id']);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable($config['permission_menu_table'])) {
            Schema::create($config['permission_menu_table'], function ($table) {
                $table->bigInteger('permission_id');
                $table->bigInteger('menu_id');
                $table->unique(['permission_id', 'menu_id']);
                $table->timestamps();
            });
        }
    }

    protected function seedAdminUser(string $username = 'admin', string $password = 'admin'): Administrator
    {
        return Administrator::create([
            'username' => $username,
            'password' => Hash::make($password),
            'name'     => 'Administrator',
        ]);
    }

    protected function loginAsAdmin(?Administrator $user = null): Administrator
    {
        $user = $user ?: $this->seedAdminUser();

        $this->actingAs($user, 'admin');

        return $user;
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // App key required for encryption/sessions
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));

        // Enable auth for feature tests
        $app['config']->set('admin.auth.enable', true);
        $app['config']->set('admin.auth.controller', \Appsolutely\AIO\Http\Controllers\AuthController::class);

        // Register admin auth guard and provider
        $app['config']->set('auth.guards.admin', [
            'driver'   => 'session',
            'provider' => 'admin',
        ]);

        $app['config']->set('auth.providers.admin', [
            'driver' => 'eloquent',
            'model'  => Administrator::class,
        ]);
    }
}
