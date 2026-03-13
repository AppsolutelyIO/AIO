<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Observers;

use Appsolutely\AIO\Models\PageBlockValue;
use Appsolutely\AIO\Services\PageSlugAliasService;

/**
 * Clears page_slug_aliases cache when block values (display_options) change.
 *
 * Dynamic Form redirect/redirect_url in display_options affect the alias map.
 */
final class PageBlockValueObserver
{
    public function __construct(
        private readonly PageSlugAliasService $pageSlugAliasService
    ) {}

    public function saved(PageBlockValue $blockValue): void
    {
        $this->pageSlugAliasService->clearCache();
    }

    public function deleted(PageBlockValue $blockValue): void
    {
        $this->pageSlugAliasService->clearCache();
    }
}
