<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Livewire\GeneralBlock;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Services\Contracts\ManifestServiceInterface;
use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Appsolutely\AIO\Services\PageBlockSettingService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class PageBlockSettingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PageBlockSettingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(ThemeServiceInterface::class, function ($mock) {
            $mock->shouldReceive('resolveThemeName')->andReturn('default');
        });

        $this->mock(ManifestServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getTemplateConfig')->andReturn(null);
        });

        $this->service = app(PageBlockSettingService::class);
    }

    // --- syncSettings ---

    public function test_sync_settings_returns_empty_array_when_data_is_empty(): void
    {
        $result = $this->service->syncSettings([], 1);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // --- getBlockValueId ---

    public function test_get_block_value_id_creates_new_block_value_when_none_exists(): void
    {
        $groupId = DB::table('page_block_groups')->insertGetId([
            'title'      => 'Test Group',
            'sort'       => 0,
            'status'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $blockId = DB::table('page_blocks')->insertGetId([
            'block_group_id' => $groupId,
            'title'          => 'Test Block',
            'class'          => 'Appsolutely\\AIO\\Livewire\\TestBlock',
            'reference'      => 'test-block',
            'sort'           => 0,
            'status'         => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $blockValueId = $this->service->getBlockValueId($blockId, null, 'test-view');

        $this->assertIsInt($blockValueId);
        $this->assertGreaterThan(0, $blockValueId);
    }

    public function test_get_block_value_id_reuses_existing_block_value_for_same_theme(): void
    {
        $groupId = DB::table('page_block_groups')->insertGetId([
            'title'      => 'Test Group',
            'sort'       => 0,
            'status'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $blockId = DB::table('page_blocks')->insertGetId([
            'block_group_id' => $groupId,
            'title'          => 'Shared Block',
            'class'          => 'Appsolutely\\AIO\\Livewire\\SharedBlock',
            'reference'      => 'shared-block',
            'sort'           => 0,
            'status'         => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $firstId  = $this->service->getBlockValueId($blockId, 'light', 'hero-view');
        $secondId = $this->service->getBlockValueId($blockId, 'light', 'hero-view');

        $this->assertEquals($firstId, $secondId);
    }

    public function test_get_block_value_id_creates_new_value_for_general_block(): void
    {
        $groupId = DB::table('page_block_groups')->insertGetId([
            'title'      => 'General Group',
            'sort'       => 0,
            'status'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $blockId = DB::table('page_blocks')->insertGetId([
            'block_group_id' => $groupId,
            'title'          => 'General Block',
            'class'          => GeneralBlock::class,
            'reference'      => 'general-block',
            'sort'           => 0,
            'status'         => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $firstId  = $this->service->getBlockValueId($blockId, null, 'some-view');
        $secondId = $this->service->getBlockValueId($blockId, null, 'some-view');

        // GeneralBlock always creates a new block value per call
        $this->assertNotEquals($firstId, $secondId);
    }

    // --- resolveBlockId (via syncSettings) ---

    public function test_sync_settings_resolves_block_by_reference(): void
    {
        Cache::flush();

        $page = Page::factory()->create();

        $groupId = DB::table('page_block_groups')->insertGetId([
            'title'      => 'Test Group',
            'sort'       => 0,
            'status'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $blockId = DB::table('page_blocks')->insertGetId([
            'block_group_id' => $groupId,
            'title'          => 'Header',
            'class'          => 'Appsolutely\\AIO\\Livewire\\Header',
            'reference'      => 'header',
            'sort'           => 0,
            'status'         => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $result = $this->service->syncSettings([
            ['type' => 'header', 'reference' => 'ref-header-1'],
        ], $page->id);

        $this->assertCount(1, $result);
        $this->assertEquals($blockId, $result[0]->block_id);
    }

    public function test_sync_settings_falls_back_to_general_block_with_legacy_class_name(): void
    {
        Cache::flush();

        $page = Page::factory()->create();

        $groupId = DB::table('page_block_groups')->insertGetId([
            'title'      => 'General Group',
            'sort'       => 0,
            'status'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Store with legacy App\Livewire namespace (as found in production DB)
        $blockId = DB::table('page_blocks')->insertGetId([
            'block_group_id' => $groupId,
            'title'          => 'General Block',
            'class'          => 'App\\Livewire\\GeneralBlock',
            'reference'      => 'general-block',
            'sort'           => 0,
            'status'         => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // Use a reference that doesn't match any block, triggering the GeneralBlock fallback
        $result = $this->service->syncSettings([
            ['type' => 'nonexistent-block-type', 'reference' => 'ref-custom-1'],
        ], $page->id);

        // classNameVariants resolves App\Livewire\GeneralBlock from Appsolutely\AIO\Livewire\GeneralBlock
        $this->assertCount(1, $result);
        $this->assertEquals($blockId, $result[0]->block_id);
    }

    public function test_sync_settings_falls_back_to_general_block_with_aio_class_name(): void
    {
        Cache::flush();

        $page = Page::factory()->create();

        $groupId = DB::table('page_block_groups')->insertGetId([
            'title'      => 'General Group',
            'sort'       => 0,
            'status'     => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Store with current AIO namespace
        $blockId = DB::table('page_blocks')->insertGetId([
            'block_group_id' => $groupId,
            'title'          => 'General Block',
            'class'          => GeneralBlock::class,
            'reference'      => 'general-block',
            'sort'           => 0,
            'status'         => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $result = $this->service->syncSettings([
            ['type' => 'unknown-block', 'reference' => 'ref-custom-2'],
        ], $page->id);

        $this->assertCount(1, $result);
        $this->assertEquals($blockId, $result[0]->block_id);
    }
}
