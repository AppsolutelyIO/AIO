<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Integration;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Services\PageService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class PageWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private PageService $pageService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pageService = app(PageService::class);
    }

    public function test_complete_page_lifecycle(): void
    {
        // Create page
        $page = Page::factory()->create([
            'slug'         => 'test-page',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        // Find published page
        $found = $this->pageService->findPublishedPage('test-page');
        $this->assertNotNull($found);
        $this->assertEquals($page->id, $found->id);

        // Update settings
        $updated = $this->pageService->saveSetting(
            $page->reference,
            ['title' => 'Updated Title']
        );
        $this->assertEquals('Updated Title', $updated->setting['title']);

        // Reset settings
        $reset = $this->pageService->resetSetting($page->reference);
        $this->assertEmpty($reset->setting);
    }

    public function test_page_not_found_returns_null(): void
    {
        $result = $this->pageService->findPublishedPage('non-existent-page');

        $this->assertNull($result);
    }
}
