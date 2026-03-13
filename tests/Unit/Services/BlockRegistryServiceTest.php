<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\BlockRegistryService;
use Appsolutely\AIO\Services\Contracts\ManifestServiceInterface;
use Illuminate\Support\Facades\Cache;
use Appsolutely\AIO\Tests\TestCase;

final class BlockRegistryServiceTest extends TestCase
{
    // --- getRegistry ---

    public function test_get_registry_returns_empty_array_when_manifest_has_no_templates(): void
    {
        $mock = $this->mock(ManifestServiceInterface::class);
        $mock->shouldReceive('loadManifest')->once()->andReturn(['templates' => []]);

        $service = app(BlockRegistryService::class);
        $result  = $service->getRegistry('test-theme');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_registry_returns_empty_array_when_manifest_has_no_template_key(): void
    {
        $mock = $this->mock(ManifestServiceInterface::class);
        $mock->shouldReceive('loadManifest')->once()->andReturn([]);

        $service = app(BlockRegistryService::class);
        $result  = $service->getRegistry('test-theme');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_registry_caches_result(): void
    {
        $mock = $this->mock(ManifestServiceInterface::class);
        // loadManifest should only be called once even when getRegistry is called twice
        $mock->shouldReceive('loadManifest')->once()->andReturn(['templates' => []]);

        $service = app(BlockRegistryService::class);
        $first   = $service->getRegistry('cached-theme');
        $second  = $service->getRegistry('cached-theme');

        // loadManifest called only once proves caching works; results should be identical
        $this->assertSame($first, $second);
    }

    // --- clearCache ---

    public function test_clear_cache_removes_specific_theme_cache_key(): void
    {
        Cache::put('block_registry:my-theme', ['some' => 'data'], 3600);

        $service = app(BlockRegistryService::class);
        $service->clearCache('my-theme');

        $this->assertNull(Cache::get('block_registry:my-theme'));
    }

    public function test_clear_cache_does_not_affect_other_theme_cache(): void
    {
        Cache::put('block_registry:theme-a', ['some' => 'data'], 3600);
        Cache::put('block_registry:theme-b', ['other' => 'data'], 3600);

        $service = app(BlockRegistryService::class);
        $service->clearCache('theme-a');

        $this->assertNull(Cache::get('block_registry:theme-a'));
        $this->assertNotNull(Cache::get('block_registry:theme-b'));
    }

    // --- renderBlockPreview ---

    public function test_render_block_preview_returns_placeholder_when_config_is_null(): void
    {
        $mock = $this->mock(ManifestServiceInterface::class);
        $mock->shouldReceive('getTemplateConfig')->once()->andReturn(null);

        $service = app(BlockRegistryService::class);
        $result  = $service->renderBlockPreview('unknown-block-type');

        $this->assertStringContainsString('unknown-block-type', $result);
        $this->assertStringContainsString('<section', $result);
    }

    public function test_render_block_preview_returns_string(): void
    {
        $mock = $this->mock(ManifestServiceInterface::class);
        $mock->shouldReceive('getTemplateConfig')->once()->andReturn(null);

        $service = app(BlockRegistryService::class);
        $result  = $service->renderBlockPreview('hero');

        $this->assertIsString($result);
    }
}
