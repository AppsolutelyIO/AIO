<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Repositories\PageRepository;
use Appsolutely\AIO\Services\Contracts\PageServiceInterface;
use Appsolutely\AIO\Services\GeneralPageService;
use Appsolutely\AIO\Services\NestedUrlResolverService;
use Appsolutely\AIO\Services\PageSlugAliasService;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Appsolutely\AIO\Tests\TestCase;

final class GeneralPageServiceTest extends TestCase
{
    use RefreshDatabase;

    private GeneralPageService $service;

    private PageServiceInterface $pageService;

    private NestedUrlResolverService $nestedUrlResolver;

    private PageSlugAliasService $pageSlugAliasService;

    private CacheRepository $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageService          = Mockery::mock(PageServiceInterface::class);
        $this->nestedUrlResolver    = app(NestedUrlResolverService::class);
        $this->pageSlugAliasService = app(PageSlugAliasService::class);
        $this->cache                = new CacheRepository(new ArrayStore());

        $this->service = new GeneralPageService(
            app(PageRepository::class),
            $this->pageService,
            $this->cache,
            $this->nestedUrlResolver,
            $this->pageSlugAliasService
        );
    }

    // --- getBlockMappings ---

    public function test_get_block_mappings_returns_empty_by_default(): void
    {
        config(['appsolutely.blocks' => []]);

        $result = $this->service->getBlockMappings();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_block_mappings_returns_configured_mappings(): void
    {
        config(['appsolutely.blocks' => ['Appsolutely\AIO\Blocks\HeroBlock' => ['Appsolutely\AIO\Repositories\PageRepository']]]);

        $result = $this->service->getBlockMappings();

        $this->assertArrayHasKey('Appsolutely\AIO\Blocks\HeroBlock', $result);
    }

    // --- hasBlockMapping ---

    public function test_has_block_mapping_returns_false_for_unmapped_block(): void
    {
        config(['appsolutely.blocks' => []]);

        $result = $this->service->hasBlockMapping('Appsolutely\AIO\Blocks\NonExistentBlock');

        $this->assertFalse($result);
    }

    public function test_has_block_mapping_returns_true_for_mapped_block(): void
    {
        config(['appsolutely.blocks' => ['Appsolutely\AIO\Blocks\HeroBlock' => ['Appsolutely\AIO\Repositories\PageRepository']]]);

        $result = $this->service->hasBlockMapping('Appsolutely\AIO\Blocks\HeroBlock');

        $this->assertTrue($result);
    }

    public function test_has_block_mapping_returns_false_when_repositories_are_empty(): void
    {
        config(['appsolutely.blocks' => ['Appsolutely\AIO\Blocks\EmptyBlock' => []]]);

        $result = $this->service->hasBlockMapping('Appsolutely\AIO\Blocks\EmptyBlock');

        $this->assertFalse($result);
    }

    // --- addBlockMapping ---

    public function test_add_block_mapping_stores_new_mapping(): void
    {
        config(['appsolutely.blocks' => []]);

        $this->service->addBlockMapping('Appsolutely\AIO\Blocks\NewBlock', ['Appsolutely\AIO\Repositories\PageRepository']);

        $result = $this->service->getBlockMappings();

        $this->assertArrayHasKey('Appsolutely\AIO\Blocks\NewBlock', $result);
    }

    public function test_add_block_mapping_preserves_existing_mappings(): void
    {
        config(['appsolutely.blocks' => ['Appsolutely\AIO\Blocks\ExistingBlock' => ['Appsolutely\AIO\Repositories\PageRepository']]]);

        $this->service->addBlockMapping('Appsolutely\AIO\Blocks\NewBlock', ['Appsolutely\AIO\Repositories\ArticleRepository']);

        $result = $this->service->getBlockMappings();

        $this->assertArrayHasKey('Appsolutely\AIO\Blocks\ExistingBlock', $result);
        $this->assertArrayHasKey('Appsolutely\AIO\Blocks\NewBlock', $result);
    }

    // --- getCacheStats ---

    public function test_get_cache_stats_returns_expected_keys(): void
    {
        $stats = $this->service->getCacheStats();

        $this->assertArrayHasKey('cache_prefix', $stats);
        $this->assertArrayHasKey('cache_ttl', $stats);
        $this->assertArrayHasKey('cache_driver', $stats);
    }

    public function test_get_cache_stats_ttl_is_3600(): void
    {
        $stats = $this->service->getCacheStats();

        $this->assertEquals(3600, $stats['cache_ttl']);
    }

    public function test_get_cache_stats_prefix_contains_page_resolution(): void
    {
        $stats = $this->service->getCacheStats();

        $this->assertStringContainsString('page_resolution', $stats['cache_prefix']);
    }

    // --- resolvePage ---

    public function test_resolve_page_returns_general_page_when_found(): void
    {
        $page = Page::factory()->create(['slug' => 'about-us']);

        $this->pageService
            ->shouldReceive('findPublishedPage')
            ->with('about-us')
            ->andReturn($page);

        $result = $this->service->resolvePage('about-us');

        $this->assertInstanceOf(GeneralPage::class, $result);
    }

    public function test_resolve_page_returns_null_when_not_found(): void
    {
        $this->pageService
            ->shouldReceive('findPublishedPage')
            ->with('non-existent')
            ->andReturn(null);

        $result = $this->service->resolvePage('non-existent');

        $this->assertNull($result);
    }

    // --- clearPageCache ---

    public function test_clear_page_cache_removes_cached_page(): void
    {
        $slug     = 'about-us';
        $cacheKey = 'page_resolution:' . md5(normalize_slug($slug));

        $this->cache->put($cacheKey, 'cached_value', 3600);
        $this->assertTrue($this->cache->has($cacheKey));

        $this->service->clearPageCache($slug);

        $this->assertFalse($this->cache->has($cacheKey));
    }

    // --- resolveNestedUrl ---

    public function test_resolve_nested_url_returns_null_for_non_existent_path(): void
    {
        $result = $this->service->resolveNestedUrl('some/nested/non-existent-path');

        $this->assertNull($result);
    }
}
