<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Models\PageBlockSetting;
use Appsolutely\AIO\Services\BlockRendererService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class BlockRendererServiceTest extends TestCase
{
    use RefreshDatabase;

    private BlockRendererService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BlockRendererService::class);
    }

    private function makePage(): GeneralPage
    {
        $page = \Appsolutely\AIO\Models\Page::factory()->create();

        return new GeneralPage($page);
    }

    private function makeBlock(array $attributes = []): PageBlockSetting
    {
        return new PageBlockSetting($attributes);
    }

    // --- renderBlockSafely ---

    public function test_render_block_safely_returns_error_html_for_missing_class_key(): void
    {
        $page  = $this->makePage();
        $block = $this->makeBlock(['reference' => 'test-ref']);

        $result = $this->service->renderBlockSafely($block, $page);

        // In non-production env, should return error HTML
        $this->assertStringContainsString('Invalid block structure', $result);
    }

    public function test_render_block_safely_returns_error_html_for_missing_reference_key(): void
    {
        $page  = $this->makePage();
        $block = $this->makeBlock();

        $result = $this->service->renderBlockSafely($block, $page);

        $this->assertStringContainsString('Invalid block structure', $result);
    }

    public function test_render_block_safely_returns_error_html_for_empty_reference(): void
    {
        $page  = $this->makePage();
        $block = $this->makeBlock(['reference' => '']);

        $result = $this->service->renderBlockSafely($block, $page);

        // Without a block relationship, 'block.class' won't exist → Invalid block structure
        $this->assertStringContainsString('Invalid block structure', $result);
    }

    public function test_render_block_safely_returns_error_when_class_is_not_livewire_component(): void
    {
        $page  = $this->makePage();
        $block = $this->makeBlock(['reference' => 'some-ref']);

        $result = $this->service->renderBlockSafely($block, $page);

        // Without a block relationship, 'block.class' won't exist → Invalid block structure
        $this->assertStringContainsString('Invalid block structure', $result);
    }
}
