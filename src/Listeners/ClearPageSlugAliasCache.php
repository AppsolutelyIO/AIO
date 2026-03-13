<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Listeners;

use Appsolutely\AIO\Events\PageCreated;
use Appsolutely\AIO\Events\PageDeleted;
use Appsolutely\AIO\Events\PageUpdated;
use Appsolutely\AIO\Services\PageSlugAliasService;

/**
 * Clears page_slug_aliases cache when pages change.
 *
 * Ensures alias map stays in sync when pages are created, updated, or deleted.
 */
final class ClearPageSlugAliasCache
{
    public function __construct(
        private readonly PageSlugAliasService $pageSlugAliasService
    ) {}

    public function handle(PageCreated|PageUpdated|PageDeleted $event): void
    {
        $this->pageSlugAliasService->clearCache();
    }
}
