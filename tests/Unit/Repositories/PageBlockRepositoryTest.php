<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\BlockScope;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Models\PageBlock;
use Appsolutely\AIO\Repositories\PageBlockRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

final class PageBlockRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PageBlockRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PageBlockRepository::class);
    }

    private function createGroup(): int
    {
        return DB::table('page_block_groups')->insertGetId([
            'title'      => 'Test Group',
            'status'     => Status::ACTIVE->value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    private function createBlock(array $attrs = []): PageBlock
    {
        $groupId = $attrs['block_group_id'] ?? $this->createGroup();

        $id = DB::table('page_blocks')->insertGetId(array_merge([
            'block_group_id' => $groupId,
            'title'          => 'Test Block ' . uniqid(),
            'class'          => 'Appsolutely\\AIO\\Blocks\\TestBlock' . uniqid(),
            'scope'          => BlockScope::Page->value,
            'sort'           => 0,
            'status'         => Status::ACTIVE->value,
            'created_at'     => now()->toDateTimeString(),
            'updated_at'     => now()->toDateTimeString(),
        ], $attrs));

        return PageBlock::find($id);
    }

    private function createSetting(int $blockId, int $pageId, array $attrs = []): void
    {
        $valueId = DB::table('page_block_values')->insertGetId([
            'block_id'   => $blockId,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        DB::table('page_block_settings')->insert(array_merge([
            'page_id'        => $pageId,
            'block_id'       => $blockId,
            'block_value_id' => $valueId,
            'sort'           => 0,
            'status'         => Status::ACTIVE->value,
            'published_at'   => now()->subMinute()->toDateTimeString(),
            'created_at'     => now()->toDateTimeString(),
            'updated_at'     => now()->toDateTimeString(),
        ], $attrs));
    }

    // --- getBlocksByScope ---

    public function test_get_blocks_by_scope_returns_active_blocks_of_scope(): void
    {
        $this->createBlock(['scope' => BlockScope::Global->value]);
        $this->createBlock(['scope' => BlockScope::Global->value]);
        $this->createBlock(['scope' => BlockScope::Page->value]);

        $result = $this->repository->getBlocksByScope(BlockScope::Global);

        $this->assertCount(2, $result);
        $result->each(fn ($b) => $this->assertEquals(BlockScope::Global->value, $b->scope));
    }

    public function test_get_blocks_by_scope_excludes_inactive(): void
    {
        $this->createBlock(['scope' => BlockScope::Page->value, 'status' => Status::ACTIVE->value]);
        $this->createBlock(['scope' => BlockScope::Page->value, 'status' => Status::INACTIVE->value]);

        $result = $this->repository->getBlocksByScope(BlockScope::Page);

        $this->assertCount(1, $result);
    }

    public function test_get_blocks_by_scope_returns_empty_when_none(): void
    {
        $result = $this->repository->getBlocksByScope(BlockScope::Global);

        $this->assertCount(0, $result);
    }

    // --- getGlobalBlocks ---

    public function test_get_global_blocks_returns_global_scope_blocks_with_active_settings(): void
    {
        $block  = $this->createBlock(['scope' => BlockScope::Global->value]);
        $page   = Page::factory()->create();
        $this->createSetting($block->id, $page->id);

        $result = $this->repository->getGlobalBlocks();

        $this->assertCount(1, $result);
        $this->assertEquals(BlockScope::Global->value, $result->first()->scope);
    }

    public function test_get_global_blocks_excludes_page_scope(): void
    {
        $block = $this->createBlock(['scope' => BlockScope::Page->value]);
        $page  = Page::factory()->create();
        $this->createSetting($block->id, $page->id);

        $result = $this->repository->getGlobalBlocks();

        $this->assertCount(0, $result);
    }
}
