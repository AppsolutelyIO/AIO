<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Models\PageBlockSetting;
use Appsolutely\AIO\Services\PageBlockService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class PageBlockServiceTest extends TestCase
{
    use RefreshDatabase;

    private PageBlockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PageBlockService::class);
    }

    // --- getCategorisedBlocks ---

    public function test_get_categorised_blocks_returns_collection(): void
    {
        $result = $this->service->getCategorisedBlocks();

        $this->assertInstanceOf(Collection::class, $result);
    }

    // --- getPublishedBlockSettings ---

    public function test_get_published_block_settings_returns_collection(): void
    {
        $result = $this->service->getPublishedBlockSettings(999);

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_get_published_block_settings_returns_empty_for_nonexistent_page(): void
    {
        $result = $this->service->getPublishedBlockSettings(99999);

        $this->assertCount(0, $result);
    }

    // --- updateBlockSettingPublishStatus ---

    public function test_update_block_setting_publish_status_returns_zero_for_nonexistent_setting(): void
    {
        $result = $this->service->updateBlockSettingPublishStatus(99999);

        $this->assertEquals(0, $result);
    }

    // --- renderBlockSafely ---

    public function test_render_block_safely_returns_error_html_for_invalid_block(): void
    {
        $page        = Page::factory()->create();
        $generalPage = new GeneralPage($page);

        $block = new PageBlockSetting(['reference' => 'some-ref']);

        $result = $this->service->renderBlockSafely($block, $generalPage);

        $this->assertStringContainsString('Invalid block structure', $result);
    }
}
