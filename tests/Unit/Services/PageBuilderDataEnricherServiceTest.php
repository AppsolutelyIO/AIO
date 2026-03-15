<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Constants\BasicConstant;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Services\PageBuilderDataEnricherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
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

    /**
     * Build a GrapesJS project data structure with components at the correct nested path.
     *
     * @param  array<int, array<string, mixed>>  $components
     * @return array<string, mixed>
     */
    private function buildProjectData(array $components): array
    {
        $setting = [];
        Arr::set($setting, BasicConstant::PAGE_GRAPESJS_KEY, $components);

        return $setting;
    }

    /**
     * Get components from enriched result at the correct nested path.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getComponents(array $result): array
    {
        return Arr::get($result, BasicConstant::PAGE_GRAPESJS_KEY, []);
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
        $setting = [];
        Arr::set($setting, BasicConstant::PAGE_GRAPESJS_KEY, 'not-an-array');

        $result = $this->service->enrich($page, $setting);

        $this->assertEquals($setting, $result);
    }

    public function test_enrich_processes_empty_components_array(): void
    {
        $page    = Page::factory()->create();
        $setting = $this->buildProjectData([]);

        $result = $this->service->enrich($page, $setting);

        $components = $this->getComponents($result);
        $this->assertIsArray($components);
        $this->assertEmpty($components);
    }

    public function test_enrich_filters_out_component_without_reference(): void
    {
        $page      = Page::factory()->create();
        $component = ['type' => 'section', 'attributes' => []];
        $setting   = $this->buildProjectData([$component]);

        $result = $this->service->enrich($page, $setting);

        $components = $this->getComponents($result);
        $this->assertCount(0, $components);
    }

    public function test_enrich_filters_out_component_when_block_setting_not_found(): void
    {
        $page      = Page::factory()->create();
        $component = ['reference' => 'nonexistent-ref', 'block_id' => 999];
        $setting   = $this->buildProjectData([$component]);

        $result = $this->service->enrich($page, $setting);

        $components = $this->getComponents($result);
        $this->assertCount(0, $components);
    }

    public function test_enrich_returns_array(): void
    {
        $page    = Page::factory()->create();
        $setting = [];

        $result = $this->service->enrich($page, $setting);

        $this->assertIsArray($result);
    }
}
