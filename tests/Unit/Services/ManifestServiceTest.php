<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\ManifestService;
use Appsolutely\AIO\Tests\TestCase;

final class ManifestServiceTest extends TestCase
{
    private ManifestService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ManifestService::class);
    }

    // --- loadManifest ---

    public function test_load_manifest_returns_empty_array_when_file_not_found(): void
    {
        $result = $this->service->loadManifest('nonexistent-theme-xyz');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // --- getTemplateConfig ---

    public function test_get_template_config_returns_null_when_manifest_empty(): void
    {
        $result = $this->service->getTemplateConfig('some-block', 'nonexistent-theme-xyz');

        $this->assertNull($result);
    }

    // --- getDisplayOptions ---

    public function test_get_display_options_returns_empty_array_when_no_manifest(): void
    {
        $result = $this->service->getDisplayOptions('some-block', 'nonexistent-theme-xyz');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // --- clearCache ---

    public function test_clear_cache_removes_cached_manifest(): void
    {
        // Put something in the manifest cache
        \Illuminate\Support\Facades\Cache::put('manifest_test-theme', ['templates' => ['hero' => []]], 3600);
        $this->assertNotNull(\Illuminate\Support\Facades\Cache::get('manifest_test-theme'));

        $this->service->clearCache('test-theme');

        $this->assertNull(\Illuminate\Support\Facades\Cache::get('manifest_test-theme'));
    }
}
