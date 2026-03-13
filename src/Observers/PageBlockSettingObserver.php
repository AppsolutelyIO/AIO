<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Observers;

use Appsolutely\AIO\Models\PageBlockSetting;
use Appsolutely\AIO\Services\PageSlugAliasService;

/**
 * Clears page_slug_aliases cache when block settings change.
 *
 * Block add/remove/update may affect Dynamic Form redirect_url aliases.
 */
final class PageBlockSettingObserver
{
    public function __construct(
        private readonly PageSlugAliasService $pageSlugAliasService
    ) {}

    public function saved(PageBlockSetting $setting): void
    {
        $this->pageSlugAliasService->clearCache();
    }

    public function deleted(PageBlockSetting $setting): void
    {
        $this->pageSlugAliasService->clearCache();
    }
}
