<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Listeners;

use Appsolutely\AIO\Events\PageCreated;
use Appsolutely\AIO\Events\PageDeleted;
use Appsolutely\AIO\Events\PageUpdated;
use Appsolutely\AIO\Listeners\ClearPageSlugAliasCache;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Services\PageSlugAliasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Appsolutely\AIO\Tests\TestCase;

final class ClearPageSlugAliasCacheTest extends TestCase
{
    use RefreshDatabase;

    // --- handle ---

    public function test_handle_page_created_clears_cache(): void
    {
        $service = app(PageSlugAliasService::class);
        $service->addAlias('old-slug', 'new-slug');
        $this->assertNotEmpty($service->getAliases());

        $listener = app(ClearPageSlugAliasCache::class);
        $listener->handle(new PageCreated(Page::factory()->make()));

        $this->assertEmpty($service->getAliases());
    }

    public function test_handle_page_updated_clears_cache(): void
    {
        $service = app(PageSlugAliasService::class);
        $service->addAlias('slug-a', 'slug-b');
        $this->assertNotEmpty($service->getAliases());

        $listener = app(ClearPageSlugAliasCache::class);
        $listener->handle(new PageUpdated(Page::factory()->make()));

        $this->assertEmpty($service->getAliases());
    }

    public function test_handle_page_deleted_clears_cache(): void
    {
        $service = app(PageSlugAliasService::class);
        $service->addAlias('delete-slug', 'target-slug');
        $this->assertNotEmpty($service->getAliases());

        $listener = app(ClearPageSlugAliasCache::class);
        $listener->handle(new PageDeleted(Page::factory()->make()));

        $this->assertEmpty($service->getAliases());
    }

    public function test_handle_clears_alias_cache_effect(): void
    {
        $service = app(PageSlugAliasService::class);

        // Add an alias so cache has content
        $service->addAlias('old-slug', 'new-slug');
        $aliases = $service->getAliases();
        $this->assertNotEmpty($aliases);

        // Handle event to clear cache
        $listener = app(ClearPageSlugAliasCache::class);
        $listener->handle(new PageCreated(Page::factory()->make()));

        // After clearing cache, getAliases rebuilds from empty DB → should be empty
        $this->assertEmpty($service->getAliases());
    }
}
