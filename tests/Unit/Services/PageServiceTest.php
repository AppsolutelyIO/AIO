<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Constants\BasicConstant;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Appsolutely\AIO\Repositories\PageRepository;
use Appsolutely\AIO\Services\Contracts\PageBlockSettingServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageStructureServiceInterface;
use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Appsolutely\AIO\Services\PageService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

final class PageServiceTest extends TestCase
{
    use RefreshDatabase;

    private PageRepository $pageRepository;

    private PageBlockSettingRepository $pageBlockSettingRepository;

    private PageBlockSettingServiceInterface $blockSettingService;

    private PageStructureServiceInterface $structureService;

    private ThemeServiceInterface $themeService;

    private PageService $pageService;

    protected function setUp(): void
    {
        parent::setUp();

        // Use real repositories (integration testing approach for final classes)
        $this->pageRepository             = app(PageRepository::class);
        $this->pageBlockSettingRepository = app(PageBlockSettingRepository::class);
        $this->blockSettingService        = Mockery::mock(PageBlockSettingServiceInterface::class);
        $this->structureService           = Mockery::mock(PageStructureServiceInterface::class);
        $this->themeService               = app(ThemeServiceInterface::class);

        $this->pageService = new PageService(
            $this->pageRepository,
            $this->pageBlockSettingRepository,
            $this->blockSettingService,
            $this->structureService,
            $this->themeService
        );
    }

    public function test_find_published_page_by_slug_returns_page(): void
    {
        $page = Page::factory()->create([
            'slug'         => 'test-page',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $result = $this->pageService->findPublishedPage('test-page');

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals($page->id, $result->id);
        $this->assertEquals('test-page', $result->slug);
    }

    public function test_find_published_page_by_slug_returns_null_when_not_found(): void
    {
        $result = $this->pageService->findPublishedPage('non-existent');

        $this->assertNull($result);
    }

    public function test_find_published_page_by_id_returns_page(): void
    {
        $page = Page::factory()->create([
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $result = $this->pageService->findPublishedPageById($page->id);

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals($page->id, $result->id);
    }

    public function test_find_published_page_by_id_returns_null_when_not_found(): void
    {
        $result = $this->pageService->findPublishedPageById(999);

        $this->assertNull($result);
    }

    public function test_find_by_reference_returns_page(): void
    {
        $page = Page::factory()->create([
            'title' => 'Test Page',
        ]);

        $result = $this->pageService->findByReference($page->reference);

        $this->assertInstanceOf(Model::class, $result);
        $this->assertEquals($page->reference, $result->reference);
    }

    public function test_find_by_reference_throws_exception_when_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->pageService->findByReference('non-existent-reference');
    }

    public function test_reset_setting_resets_page_and_block_settings(): void
    {
        $page = Page::factory()->create([
            'setting' => ['key' => 'value'],
        ]);

        $result = $this->pageService->resetSetting($page->reference);

        $this->assertInstanceOf(Model::class, $result);
        $this->assertEmpty($result->setting);
    }

    public function test_save_setting_saves_page_and_syncs_block_settings(): void
    {
        $page = Page::factory()->create();
        $data = [
            'title'                          => 'Updated Title',
            BasicConstant::PAGE_GRAPESJS_KEY => [
                ['id' => 'block-1', 'type' => 'text'],
            ],
        ];

        $this->blockSettingService
            ->shouldReceive('syncSettings')
            ->once()
            ->with([['id' => 'block-1', 'type' => 'text']], $page->id)
            ->andReturn([]);

        $result = $this->pageService->saveSetting($page->reference, $data);

        $this->assertInstanceOf(Model::class, $result);
        $this->assertEquals('Updated Title', $result->setting['title']);
    }

    public function test_save_setting_handles_empty_block_data(): void
    {
        $page = Page::factory()->create();
        $data = ['title' => 'Updated Title'];

        $this->blockSettingService
            ->shouldReceive('syncSettings')
            ->once()
            ->with([], $page->id)
            ->andReturn([]);

        $result = $this->pageService->saveSetting($page->reference, $data);

        $this->assertInstanceOf(Model::class, $result);
        $this->assertEquals('Updated Title', $result->setting['title']);
    }

    public function test_save_setting_handles_non_array_block_data(): void
    {
        $page = Page::factory()->create();
        $data = [
            'title'                          => 'Updated Title',
            BasicConstant::PAGE_GRAPESJS_KEY => 'not-an-array',
        ];

        $this->blockSettingService
            ->shouldReceive('syncSettings')
            ->once()
            ->with([], $page->id)
            ->andReturn([]);

        $result = $this->pageService->saveSetting($page->reference, $data);

        $this->assertInstanceOf(Model::class, $result);
    }

    public function test_sync_settings_delegates_to_block_setting_service(): void
    {
        $data     = [['id' => 'block-1']];
        $pageId   = 1;
        $expected = ['result' => 'synced'];

        $this->blockSettingService
            ->shouldReceive('syncSettings')
            ->once()
            ->with($data, $pageId)
            ->andReturn($expected);

        $result = $this->pageService->syncSettings($data, $pageId);

        $this->assertEquals($expected, $result);
    }

    public function test_get_block_value_id_delegates_to_block_setting_service(): void
    {
        $blockId  = 5;
        $expected = 10;

        $this->blockSettingService
            ->shouldReceive('getBlockValueId')
            ->once()
            ->with($blockId)
            ->andReturn($expected);

        $result = $this->pageService->getBlockValueId($blockId);

        $this->assertEquals($expected, $result);
    }

    public function test_generate_default_page_setting_delegates_to_structure_service(): void
    {
        $expected = ['pages' => [['frames' => []]]];

        $this->structureService
            ->shouldReceive('generateDefaultPageSetting')
            ->once()
            ->andReturn($expected);

        $result = $this->pageService->generateDefaultPageSetting();

        $this->assertEquals($expected, $result);
    }
}
