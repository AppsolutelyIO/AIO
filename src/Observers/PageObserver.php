<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Observers;

use Appsolutely\AIO\Events\PageCreated;
use Appsolutely\AIO\Events\PageDeleted;
use Appsolutely\AIO\Events\PageUpdated;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Services\Contracts\PageServiceInterface;

/**
 * Observer for Page model events
 *
 * Dispatches domain events when pages are created, updated, or deleted.
 * Listeners handle side effects like cache clearing.
 */
final class PageObserver
{
    public function __construct(
        private readonly PageServiceInterface $pageService
    ) {}

    /**
     * Handle the Page "creating" event.
     */
    public function creating(Page $page): void
    {
        // Initialize default page setting if not provided
        if (empty($page->setting)) {
            $page->setting = $this->pageService->generateDefaultPageSetting();
        }
    }

    /**
     * Handle the Page "created" event.
     */
    public function created(Page $page): void
    {
        event(new PageCreated($page));
    }

    /**
     * Handle the Page "updated" event.
     */
    public function updated(Page $page): void
    {
        event(new PageUpdated($page));
    }

    /**
     * Handle the Page "deleted" event.
     */
    public function deleted(Page $page): void
    {
        event(new PageDeleted($page));
    }
}
