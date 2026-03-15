<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\PageStructureService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class PageStructureServiceTest extends TestCase
{
    use RefreshDatabase;

    private PageStructureService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PageStructureService::class);
    }

    // --- getPageStructure ---

    public function test_get_page_structure_returns_array(): void
    {
        $result = $this->service->getPageStructure();

        $this->assertIsArray($result);
    }

    public function test_get_page_structure_has_required_keys(): void
    {
        $result = $this->service->getPageStructure();

        $this->assertArrayHasKey('pages', $result);
        $this->assertArrayHasKey('assets', $result);
        $this->assertArrayHasKey('styles', $result);
        $this->assertArrayHasKey('symbols', $result);
        $this->assertArrayHasKey('dataSources', $result);
    }

    public function test_get_page_structure_has_one_page_of_type_main(): void
    {
        $result = $this->service->getPageStructure();

        $this->assertCount(1, $result['pages']);
        $this->assertEquals('main', $result['pages'][0]['type']);
    }

    public function test_get_page_structure_pages_have_frames(): void
    {
        $result = $this->service->getPageStructure();

        $this->assertArrayHasKey('frames', $result['pages'][0]);
        $this->assertNotEmpty($result['pages'][0]['frames']);
    }

    public function test_get_page_structure_has_wrapper_component(): void
    {
        $result = $this->service->getPageStructure();
        $frame  = $result['pages'][0]['frames'][0];

        $this->assertEquals('wrapper', $frame['component']['type']);
    }

    public function test_get_page_structure_generates_unique_ids_each_call(): void
    {
        $result1 = $this->service->getPageStructure();
        $result2 = $this->service->getPageStructure();

        $this->assertNotEquals($result1['pages'][0]['id'], $result2['pages'][0]['id']);
    }

    // --- attachGlobalBlocks ---

    public function test_attach_global_blocks_returns_array(): void
    {
        $result = $this->service->attachGlobalBlocks();

        $this->assertIsArray($result);
    }

    public function test_attach_global_blocks_returns_empty_when_no_global_blocks(): void
    {
        $result = $this->service->attachGlobalBlocks();

        $this->assertEmpty($result);
    }

    // --- generateDefaultPageSetting ---

    public function test_generate_default_page_setting_returns_structure_with_components_key(): void
    {
        $result = $this->service->generateDefaultPageSetting();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('pages', $result);
    }
}
