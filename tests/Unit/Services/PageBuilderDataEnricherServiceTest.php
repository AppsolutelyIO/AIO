<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Constants\BasicConstant;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Services\PageBuilderDataEnricherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class PageBuilderDataEnricherServiceTest extends TestCase
{
    use RefreshDatabase;

    private PageBuilderDataEnricherService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PageBuilderDataEnricherService::class);
    }

    // --- enrich ---

    public function test_enrich_returns_setting_unchanged_when_no_grapesjs_key(): void
    {
        $page    = Page::factory()->create();
        $setting = ['other_key' => 'some_value'];

        $result = $this->service->enrich($page, $setting);

        $this->assertEquals($setting, $result);
    }

    public function test_enrich_returns_setting_unchanged_when_components_is_not_array(): void
    {
        $page    = Page::factory()->create();
        $setting = [BasicConstant::PAGE_GRAPESJS_KEY => 'not-an-array'];

        $result = $this->service->enrich($page, $setting);

        $this->assertEquals($setting, $result);
    }

    public function test_enrich_processes_empty_components_array(): void
    {
        $page    = Page::factory()->create();
        $setting = [BasicConstant::PAGE_GRAPESJS_KEY => []];

        $result = $this->service->enrich($page, $setting);

        $this->assertIsArray($result[BasicConstant::PAGE_GRAPESJS_KEY]);
        $this->assertEmpty($result[BasicConstant::PAGE_GRAPESJS_KEY]);
    }

    public function test_enrich_skips_component_without_reference(): void
    {
        $page      = Page::factory()->create();
        $component = ['type' => 'section', 'attributes' => []];
        $setting   = [BasicConstant::PAGE_GRAPESJS_KEY => [$component]];

        $result = $this->service->enrich($page, $setting);

        $components = $result[BasicConstant::PAGE_GRAPESJS_KEY];
        $this->assertCount(1, $components);
        // Component returned unchanged (no reference → no enrichment)
        $this->assertArrayNotHasKey('content', $components[0]);
    }

    public function test_enrich_skips_component_when_block_setting_not_found(): void
    {
        $page      = Page::factory()->create();
        $component = ['reference' => 'nonexistent-ref', 'block_id' => 999];
        $setting   = [BasicConstant::PAGE_GRAPESJS_KEY => [$component]];

        $result = $this->service->enrich($page, $setting);

        $components = $result[BasicConstant::PAGE_GRAPESJS_KEY];
        $this->assertCount(1, $components);
        // No block setting found → content not injected
        $this->assertArrayNotHasKey('content', $components[0]);
    }

    public function test_enrich_returns_array(): void
    {
        $page    = Page::factory()->create();
        $setting = [];

        $result = $this->service->enrich($page, $setting);

        $this->assertIsArray($result);
    }
}
