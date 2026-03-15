<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\BlockScope;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Models\PageBlockSetting;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

final class PageBlockSettingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PageBlockSettingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PageBlockSettingRepository::class);
    }

    private function createBlock(array $attrs = []): int
    {
        $groupId = DB::table('page_block_groups')->insertGetId([
            'title'      => 'Group',
            'status'     => Status::ACTIVE->value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        return DB::table('page_blocks')->insertGetId(array_merge([
            'block_group_id' => $groupId,
            'title'          => 'Block ' . uniqid(),
            'class'          => 'Appsolutely\\AIO\\Block\\' . uniqid(),
            'scope'          => BlockScope::Page->value,
            'sort'           => 0,
            'status'         => Status::ACTIVE->value,
            'created_at'     => now()->toDateTimeString(),
            'updated_at'     => now()->toDateTimeString(),
        ], $attrs));
    }

    private function createSetting(int $pageId, int $blockId, array $attrs = []): PageBlockSetting
    {
        $valueId = DB::table('page_block_values')->insertGetId([
            'block_id'   => $blockId,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $id = DB::table('page_block_settings')->insertGetId(array_merge([
            'page_id'        => $pageId,
            'block_id'       => $blockId,
            'block_value_id' => $valueId,
            'sort'           => 0,
            'status'         => Status::ACTIVE->value,
            'published_at'   => now()->subMinute()->toDateTimeString(),
            'expired_at'     => null,
            'created_at'     => now()->toDateTimeString(),
            'updated_at'     => now()->toDateTimeString(),
        ], $attrs));

        return PageBlockSetting::find($id);
    }

    // --- findByBlockId ---

    public function test_find_by_block_id_returns_active_setting(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $this->createSetting($page->id, $blockId);

        $result = $this->repository->findByBlockId($blockId);

        $this->assertInstanceOf(PageBlockSetting::class, $result);
        $this->assertEquals($blockId, $result->block_id);
    }

    public function test_find_by_block_id_returns_null_for_inactive(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $this->createSetting($page->id, $blockId, ['status' => Status::INACTIVE->value]);

        $result = $this->repository->findByBlockId($blockId);

        $this->assertNull($result);
    }

    // --- resetSetting ---

    public function test_reset_setting_sets_status_to_inactive(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $setting = $this->createSetting($page->id, $blockId, ['status' => Status::ACTIVE->value]);

        $this->repository->resetSetting($page->id);

        $this->assertEquals(Status::INACTIVE->value, $setting->fresh()->status->value);
    }

    public function test_reset_setting_sets_sort_to_zero(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $setting = $this->createSetting($page->id, $blockId, ['sort' => 5]);

        $this->repository->resetSetting($page->id);

        $this->assertEquals(0, $setting->fresh()->sort);
    }

    public function test_reset_setting_only_affects_given_page(): void
    {
        $page1   = Page::factory()->create();
        $page2   = Page::factory()->create();
        $blockId = $this->createBlock();
        $this->createSetting($page1->id, $blockId, ['sort' => 3]);
        $setting2 = $this->createSetting($page2->id, $blockId, ['sort' => 3]);

        $this->repository->resetSetting($page1->id);

        $this->assertEquals(3, $setting2->fresh()->sort);
    }

    public function test_reset_setting_with_theme_only_affects_matching_theme(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $june    = $this->createSetting($page->id, $blockId, ['theme' => 'june', 'sort' => 1]);
        $tabler  = $this->createSetting($page->id, $blockId, ['theme' => 'tabler', 'sort' => 2, 'reference' => 'ref-2']);

        $this->repository->resetSetting($page->id, 'june');

        $this->assertEquals(Status::INACTIVE->value, $june->fresh()->status->value);
        $this->assertEquals(Status::ACTIVE->value, $tabler->fresh()->status->value);
        $this->assertEquals(2, $tabler->fresh()->sort);
    }

    public function test_reset_setting_without_theme_affects_all_themes(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $june    = $this->createSetting($page->id, $blockId, ['theme' => 'june', 'sort' => 1]);
        $tabler  = $this->createSetting($page->id, $blockId, ['theme' => 'tabler', 'sort' => 2, 'reference' => 'ref-2']);

        $this->repository->resetSetting($page->id);

        $this->assertEquals(Status::INACTIVE->value, $june->fresh()->status->value);
        $this->assertEquals(Status::INACTIVE->value, $tabler->fresh()->status->value);
    }

    // --- findBy with theme ---

    public function test_find_by_with_theme_returns_matching_setting(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $this->createSetting($page->id, $blockId, ['theme' => 'june', 'reference' => 'block-ref']);
        $tabler = $this->createSetting($page->id, $blockId, ['theme' => 'tabler', 'reference' => 'block-ref']);

        $result = $this->repository->findBy($page->id, $blockId, 'block-ref', 'tabler');

        $this->assertNotNull($result);
        $this->assertEquals($tabler->id, $result->id);
    }

    public function test_find_by_with_theme_returns_null_when_no_match(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $this->createSetting($page->id, $blockId, ['theme' => 'june', 'reference' => 'block-ref']);

        $result = $this->repository->findBy($page->id, $blockId, 'block-ref', 'tabler');

        $this->assertNull($result);
    }

    // --- getActivePublishedSettings ---

    public function test_get_active_published_settings_returns_active_and_published(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $this->createSetting($page->id, $blockId, ['published_at' => now()->subHour()->toDateTimeString()]);

        $result = $this->repository->getActivePublishedSettings($page->id);

        $this->assertCount(1, $result);
    }

    public function test_get_active_published_settings_excludes_inactive(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $this->createSetting($page->id, $blockId, ['status' => Status::INACTIVE->value]);

        $result = $this->repository->getActivePublishedSettings($page->id);

        $this->assertCount(0, $result);
    }

    public function test_get_active_published_settings_excludes_future_published(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $this->createSetting($page->id, $blockId, ['published_at' => now()->addHour()->toDateTimeString()]);

        $result = $this->repository->getActivePublishedSettings($page->id);

        $this->assertCount(0, $result);
    }

    public function test_get_active_published_settings_only_returns_for_given_page(): void
    {
        $page1   = Page::factory()->create();
        $page2   = Page::factory()->create();
        $blockId = $this->createBlock();
        $this->createSetting($page1->id, $blockId);
        $this->createSetting($page2->id, $blockId);

        $result = $this->repository->getActivePublishedSettings($page1->id);

        $this->assertCount(1, $result);
        $this->assertEquals($page1->id, $result->first()->page_id);
    }

    // --- updatePublishStatus ---

    public function test_update_publish_status_sets_published_at(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $setting = $this->createSetting($page->id, $blockId);
        $newDate = '2026-01-01 00:00:00';

        $this->repository->updatePublishStatus($setting->id, $newDate, null);

        $this->assertEquals($newDate, $setting->fresh()->published_at->toDateTimeString());
    }

    public function test_update_publish_status_sets_expired_at(): void
    {
        $page    = Page::factory()->create();
        $blockId = $this->createBlock();
        $setting = $this->createSetting($page->id, $blockId);
        $expiry  = '2026-12-31 23:59:59';

        $this->repository->updatePublishStatus($setting->id, null, $expiry);

        $this->assertEquals($expiry, $setting->fresh()->expired_at->toDateTimeString());
    }
}
