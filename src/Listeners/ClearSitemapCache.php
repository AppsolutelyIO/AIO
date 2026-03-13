<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Listeners;

use Appsolutely\AIO\Events\ArticleCreated;
use Appsolutely\AIO\Events\ArticleDeleted;
use Appsolutely\AIO\Events\ArticleUpdated;
use Appsolutely\AIO\Events\PageCreated;
use Appsolutely\AIO\Events\PageDeleted;
use Appsolutely\AIO\Events\PageUpdated;
use Appsolutely\AIO\Events\ProductCreated;
use Appsolutely\AIO\Events\ProductDeleted;
use Appsolutely\AIO\Events\ProductUpdated;
use Appsolutely\AIO\Services\Contracts\SitemapServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Listener to clear sitemap cache when content changes
 *
 * Handles cache invalidation for sitemap when pages, products, or articles
 * are created, updated, or deleted.
 */
final class ClearSitemapCache implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly SitemapServiceInterface $sitemapService
    ) {}

    /**
     * Handle content change events (pages, products, articles)
     */
    public function handle(PageCreated|PageUpdated|PageDeleted|ProductCreated|ProductUpdated|ProductDeleted|ArticleCreated|ArticleUpdated|ArticleDeleted $event): void
    {
        $this->clearCache();
    }

    /**
     * Clear sitemap cache
     */
    private function clearCache(): void
    {
        $this->sitemapService->clearCache();
    }
}
