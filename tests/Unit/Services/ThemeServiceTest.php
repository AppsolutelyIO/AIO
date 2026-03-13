<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Appsolutely\AIO\Services\ThemeService;
use Appsolutely\AIO\Tests\TestCase;

final class ThemeServiceTest extends TestCase
{
    private ThemeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ThemeService::class);
    }

    // --- Container resolution ---

    public function test_resolves_from_container_via_interface(): void
    {
        $service = app(ThemeServiceInterface::class);

        $this->assertInstanceOf(ThemeService::class, $service);
    }

    public function test_implements_theme_service_interface(): void
    {
        $this->assertInstanceOf(ThemeServiceInterface::class, $this->service);
    }

    // --- shouldApplyTheme ---

    public function test_should_apply_theme_returns_true_for_non_admin_path(): void
    {
        config(['admin.route.prefix' => 'admin']);

        $this->assertTrue($this->service->shouldApplyTheme('/home'));
        $this->assertTrue($this->service->shouldApplyTheme('/products'));
        $this->assertTrue($this->service->shouldApplyTheme('/about'));
    }

    public function test_should_apply_theme_returns_false_for_admin_path(): void
    {
        config(['admin.route.prefix' => 'admin']);

        $this->assertFalse($this->service->shouldApplyTheme('admin'));
        $this->assertFalse($this->service->shouldApplyTheme('admin/users'));
        $this->assertFalse($this->service->shouldApplyTheme('admin/settings'));
    }

    public function test_should_apply_theme_uses_configured_admin_prefix(): void
    {
        config(['admin.route.prefix' => 'dashboard']);

        $this->assertFalse($this->service->shouldApplyTheme('dashboard/settings'));
        $this->assertTrue($this->service->shouldApplyTheme('admin/settings'));
    }

    public function test_should_apply_theme_with_empty_string_prefix(): void
    {
        config(['admin.route.prefix' => '']);

        // Empty string prefix: str_starts_with is always true, so theme never applies
        $this->assertFalse($this->service->shouldApplyTheme('anything'));
    }

    public function test_should_apply_theme_returns_true_for_root_path(): void
    {
        config(['admin.route.prefix' => 'admin']);

        $this->assertTrue($this->service->shouldApplyTheme(''));
    }

    // --- resolveThemeName ---

    public function test_resolve_theme_name_returns_config_value_when_no_basic_config(): void
    {
        config(['theme.active' => 'default-theme']);

        $result = $this->service->resolveThemeName();

        $this->assertEquals('default-theme', $result);
    }

    public function test_resolve_theme_name_returns_null_when_no_config(): void
    {
        config(['theme.active' => null]);

        $result = $this->service->resolveThemeName();

        $this->assertNull($result);
    }

    // --- getParentTheme ---

    public function test_get_parent_theme_returns_configured_value(): void
    {
        config(['theme.parent' => 'default']);

        $result = $this->service->getParentTheme();

        $this->assertSame('default', $result);
    }

    public function test_get_parent_theme_returns_null_when_not_configured(): void
    {
        config(['theme.parent' => null]);

        $result = $this->service->getParentTheme();

        $this->assertNull($result);
    }

    // --- getThemeViewPath ---

    public function test_get_theme_view_path_returns_string_containing_theme_name(): void
    {
        $result = $this->service->getThemeViewPath('my-theme');

        $this->assertIsString($result);
        $this->assertStringContainsString('my-theme', $result);
    }

    public function test_get_theme_view_path_contains_views_directory(): void
    {
        $result = $this->service->getThemeViewPath('june');

        $this->assertStringContainsString('views', $result);
    }

    public function test_get_theme_view_path_for_different_themes(): void
    {
        $default = $this->service->getThemeViewPath('default');
        $june    = $this->service->getThemeViewPath('june');
        $tabler  = $this->service->getThemeViewPath('tabler');

        $this->assertNotSame($default, $june);
        $this->assertNotSame($june, $tabler);
        $this->assertStringContainsString('default', $default);
        $this->assertStringContainsString('june', $june);
        $this->assertStringContainsString('tabler', $tabler);
    }
}
