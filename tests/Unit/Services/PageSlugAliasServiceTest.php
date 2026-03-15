<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\PageSlugAliasService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository as CacheRepository;

final class PageSlugAliasServiceTest extends TestCase
{
    private PageSlugAliasService $service;

    private CacheRepository $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache   = new CacheRepository(new ArrayStore());
        $this->service = new PageSlugAliasService($this->cache);
    }

    // --- getAliases ---

    public function test_get_aliases_returns_empty_array_by_default(): void
    {
        $result = $this->service->getAliases();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // --- addAlias ---

    public function test_add_alias_stores_mapping(): void
    {
        $this->service->addAlias('old-slug', 'new-slug');

        $aliases = $this->service->getAliases();

        // normalize_slug adds leading slash: 'old-slug' → '/old-slug'
        $this->assertArrayHasKey('/old-slug', $aliases);
        $this->assertEquals('/new-slug', $aliases['/old-slug']);
    }

    public function test_add_alias_ignores_empty_alias(): void
    {
        $this->service->addAlias('', 'canonical');

        $this->assertEmpty($this->service->getAliases());
    }

    public function test_add_alias_ignores_empty_canonical(): void
    {
        $this->service->addAlias('alias', '');

        $this->assertEmpty($this->service->getAliases());
    }

    public function test_add_alias_ignores_root_slash_alias(): void
    {
        $this->service->addAlias('/', 'canonical');

        $this->assertEmpty($this->service->getAliases());
    }

    public function test_add_alias_ignores_when_alias_equals_canonical(): void
    {
        $this->service->addAlias('same-slug', 'same-slug');

        $this->assertEmpty($this->service->getAliases());
    }

    public function test_add_alias_does_not_duplicate_existing_mapping(): void
    {
        $this->service->addAlias('old', 'new');
        $this->service->addAlias('old', 'new'); // add same alias again

        $aliases = $this->service->getAliases();
        $this->assertCount(1, $aliases);
    }

    public function test_add_alias_can_store_multiple_aliases(): void
    {
        $this->service->addAlias('alias-one', 'canonical-one');
        $this->service->addAlias('alias-two', 'canonical-two');

        $aliases = $this->service->getAliases();

        $this->assertCount(2, $aliases);
        $this->assertArrayHasKey('/alias-one', $aliases);
        $this->assertArrayHasKey('/alias-two', $aliases);
    }

    // --- resolveAlias ---

    public function test_resolve_alias_returns_canonical_for_known_alias(): void
    {
        $this->service->addAlias('old-page', 'new-page');

        // resolveAlias expects the normalized slug (with leading slash)
        $result = $this->service->resolveAlias('/old-page');

        $this->assertEquals('/new-page', $result);
    }

    public function test_resolve_alias_returns_null_for_unknown_slug(): void
    {
        $result = $this->service->resolveAlias('non-existent');

        $this->assertNull($result);
    }

    public function test_resolve_alias_returns_null_when_no_aliases(): void
    {
        $result = $this->service->resolveAlias('any-slug');

        $this->assertNull($result);
    }

    // --- clearCache ---

    public function test_clear_cache_removes_all_aliases(): void
    {
        $this->service->addAlias('alias-one', 'canonical-one');
        $this->service->addAlias('alias-two', 'canonical-two');

        $this->service->clearCache();

        $this->assertEmpty($this->service->getAliases());
    }

    public function test_clear_cache_makes_resolve_return_null(): void
    {
        $this->service->addAlias('old-page', 'new-page');
        $this->service->clearCache();

        $result = $this->service->resolveAlias('old-page');

        $this->assertNull($result);
    }
}
