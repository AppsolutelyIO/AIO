<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Appsolutely\AIO\Services\ThemeService;
use Appsolutely\AIO\Tests\TestCase;
use Qirolab\Theme\Theme;

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

    public function test_get_theme_view_path_prioritizes_site_over_package(): void
    {
        // 'tabler' exists in both site and package — site should win
        // Create a temporary site tabler theme directory
        $sitePath = base_path('themes/tabler/views');
        @mkdir($sitePath, 0755, true);

        try {
            $result = $this->service->getThemeViewPath('tabler');
            $this->assertSame($sitePath, $result);
        } finally {
            @rmdir($sitePath);
            @rmdir(base_path('themes/tabler'));
        }
    }

    // --- ensureSetup ---

    public function test_ensure_setup_does_nothing_when_no_theme_configured(): void
    {
        config(['theme.active' => null]);

        // Should not throw
        $this->service->ensureSetup();

        $this->assertNull(config('theme.active'));
    }

    public function test_ensure_setup_sets_up_theme_from_config(): void
    {
        config(['theme.active' => 'tabler', 'theme.parent' => null]);

        $this->service->ensureSetup();

        $this->assertSame('tabler', Theme::active());
    }

    public function test_get_theme_view_path_falls_back_to_package_when_no_site_theme(): void
    {
        // 'tabler' only exists in the package, not in the site themes directory
        $result = $this->service->getThemeViewPath('tabler');

        $this->assertStringContainsString('/themes/tabler/views', $result);
    }
}
