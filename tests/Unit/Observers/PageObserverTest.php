<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Observers;

use Appsolutely\AIO\Events\PageCreated;
use Appsolutely\AIO\Events\PageDeleted;
use Appsolutely\AIO\Events\PageUpdated;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Observers\PageObserver;
use Appsolutely\AIO\Services\Contracts\PageServiceInterface;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;
use Appsolutely\AIO\Tests\TestCase;

final class PageObserverTest extends TestCase
{
    private PageServiceInterface|MockInterface $pageService;

    private PageObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageService = Mockery::mock(PageServiceInterface::class);
        $this->observer    = new PageObserver($this->pageService);
    }

    // --- creating ---

    public function test_creating_sets_default_setting_when_empty(): void
    {
        $defaultSetting = ['layout' => 'default', 'sidebar' => false];
        $this->pageService->shouldReceive('generateDefaultPageSetting')
            ->once()
            ->andReturn($defaultSetting);

        $page          = new Page();
        $page->setting = null;

        $this->observer->creating($page);

        $this->assertSame($defaultSetting, $page->setting);
    }

    public function test_creating_preserves_existing_setting(): void
    {
        $existingSetting = ['layout' => 'custom', 'sidebar' => true];
        $this->pageService->shouldNotReceive('generateDefaultPageSetting');

        $page          = new Page();
        $page->setting = $existingSetting;

        $this->observer->creating($page);

        $this->assertSame($existingSetting, $page->setting);
    }

    public function test_creating_sets_default_when_setting_is_empty_array(): void
    {
        // empty([]) is true in PHP, so this should trigger default
        $defaultSetting = ['layout' => 'default'];
        $this->pageService->shouldReceive('generateDefaultPageSetting')
            ->once()
            ->andReturn($defaultSetting);

        $page          = new Page();
        $page->setting = [];

        $this->observer->creating($page);

        $this->assertSame($defaultSetting, $page->setting);
    }

    // --- created ---

    public function test_created_dispatches_page_created_event(): void
    {
        Event::fake([PageCreated::class]);

        $page = new Page(['title' => 'Test Page']);
        $this->observer->created($page);

        Event::assertDispatched(PageCreated::class, function (PageCreated $event) use ($page) {
            return $event->page === $page;
        });
    }

    // --- updated ---

    public function test_updated_dispatches_page_updated_event(): void
    {
        Event::fake([PageUpdated::class]);

        $page = new Page(['title' => 'Updated Page']);
        $this->observer->updated($page);

        Event::assertDispatched(PageUpdated::class, function (PageUpdated $event) use ($page) {
            return $event->page === $page;
        });
    }

    // --- deleted ---

    public function test_deleted_dispatches_page_deleted_event(): void
    {
        Event::fake([PageDeleted::class]);

        $page = new Page(['title' => 'Deleted Page']);
        $this->observer->deleted($page);

        Event::assertDispatched(PageDeleted::class, function (PageDeleted $event) use ($page) {
            return $event->page === $page;
        });
    }

    // --- Event dispatch count ---

    public function test_each_lifecycle_event_dispatches_exactly_once(): void
    {
        Event::fake([PageCreated::class, PageUpdated::class, PageDeleted::class]);

        $page = new Page(['title' => 'Test']);

        $this->observer->created($page);
        $this->observer->updated($page);
        $this->observer->deleted($page);

        Event::assertDispatchedTimes(PageCreated::class, 1);
        Event::assertDispatchedTimes(PageUpdated::class, 1);
        Event::assertDispatchedTimes(PageDeleted::class, 1);
    }
}
