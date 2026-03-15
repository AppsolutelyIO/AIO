<?php

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Admin;
use Appsolutely\AIO\Application;
use Appsolutely\AIO\Color;

class AdminTest extends TestCase
{
    // --- Version ---

    public function test_version_constant()
    {
        $this->assertNotEmpty(Admin::VERSION);
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+/', Admin::VERSION);
    }

    public function test_long_version()
    {
        $long = Admin::longVersion();

        $this->assertStringContainsString(Admin::VERSION, $long);
        $this->assertStringContainsString('Appsolutely AIO', $long);
    }

    // --- Color ---

    public function test_color_returns_color_instance()
    {
        $color = Admin::color();

        $this->assertInstanceOf(Color::class, $color);
    }

    // --- Title ---

    public function test_title_returns_config_default()
    {
        $title = Admin::title();

        $this->assertSame(config('admin.title'), $title);
    }

    public function test_title_can_be_set()
    {
        Admin::title('Custom Title');

        $this->assertSame('Custom Title', Admin::title());
    }

    // --- Application ---

    public function test_app_returns_application()
    {
        $app = Admin::app();

        $this->assertInstanceOf(Application::class, $app);
    }

    public function test_app_default_name()
    {
        $name = Admin::app()->getName();

        $this->assertSame('admin', $name);
    }

    public function test_route_prefix()
    {
        $prefix = Admin::app()->getRoutePrefix();

        $this->assertSame('aio.admin.', $prefix);
    }

    public function test_api_route_prefix()
    {
        $prefix = Admin::app()->getApiRoutePrefix();

        $this->assertSame('aio.admin.api.', $prefix);
    }

    // --- No dcat references ---

    public function test_route_prefix_uses_aio_not_dcat()
    {
        $prefix = Admin::app()->getRoutePrefix();

        $this->assertStringNotContainsString('dcat', $prefix);
        $this->assertStringStartsWith('aio.', $prefix);
    }

    public function test_api_prefix_uses_aio_not_dcat()
    {
        $prefix = Admin::app()->getApiRoutePrefix();

        $this->assertStringNotContainsString('dcat', $prefix);
        $this->assertStringContainsString('api', $prefix);
    }

    // --- Context ---

    public function test_context_accessible()
    {
        $context = Admin::context();

        $this->assertNotNull($context);
    }

    // --- Section constants ---

    public function test_section_constants_defined()
    {
        $sections = Admin::SECTION;

        $this->assertArrayHasKey('HEAD', $sections);
        $this->assertArrayHasKey('BODY_INNER_BEFORE', $sections);
        $this->assertArrayHasKey('BODY_INNER_AFTER', $sections);
        $this->assertArrayHasKey('APP_INNER_BEFORE', $sections);
        $this->assertArrayHasKey('APP_INNER_AFTER', $sections);
        $this->assertArrayHasKey('NAVBAR_USER_PANEL', $sections);
        $this->assertArrayHasKey('LEFT_SIDEBAR_MENU', $sections);
    }

    public function test_section_values_use_admin_prefix()
    {
        foreach (Admin::SECTION as $key => $value) {
            $this->assertStringStartsWith('ADMIN_', $value, "Section {$key} should start with ADMIN_");
        }
    }

    // --- Config ---

    public function test_admin_config_loaded()
    {
        $this->assertSame('Appsolutely AIO', config('admin.name'));
    }

    public function test_database_tables_config()
    {
        $this->assertSame('admin_users', config('admin.database.users_table'));
        $this->assertSame('admin_roles', config('admin.database.roles_table'));
        $this->assertSame('admin_permissions', config('admin.database.permissions_table'));
        $this->assertSame('admin_menu', config('admin.database.menu_table'));
    }
}
