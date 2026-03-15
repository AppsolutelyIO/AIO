<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Listeners;

use Appsolutely\AIO\Events\PageCreated;
use Appsolutely\AIO\Events\PageDeleted;
use Appsolutely\AIO\Events\PageUpdated;
use Appsolutely\AIO\Listeners\ClearSitemapCache;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Services\Contracts\SitemapServiceInterface;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

final class ClearSitemapCacheTest extends TestCase
{
    use RefreshDatabase;

    private function makeListenerWithSpy(): array
    {
        $sitemapService = Mockery::mock(SitemapServiceInterface::class);
        $sitemapService->allows('clearCache');
        $listener = new ClearSitemapCache($sitemapService);

        return [$listener, $sitemapService];
    }

    // --- handle ---

    public function test_handle_page_created_calls_clear_cache(): void
    {
        [$listener, $service] = $this->makeListenerWithSpy();
        $page                 = Page::factory()->make();

        $listener->handle(new PageCreated($page));

        $service->shouldHaveReceived('clearCache')->once();
        $this->addToAssertionCount(1);
    }

    public function test_handle_page_updated_calls_clear_cache(): void
    {
        [$listener, $service] = $this->makeListenerWithSpy();
        $page                 = Page::factory()->make();

        $listener->handle(new PageUpdated($page));

        $service->shouldHaveReceived('clearCache')->once();
        $this->addToAssertionCount(1);
    }

    public function test_handle_page_deleted_calls_clear_cache(): void
    {
        [$listener, $service] = $this->makeListenerWithSpy();
        $page                 = Page::factory()->make();

        $listener->handle(new PageDeleted($page));

        $service->shouldHaveReceived('clearCache')->once();
        $this->addToAssertionCount(1);
    }
}
