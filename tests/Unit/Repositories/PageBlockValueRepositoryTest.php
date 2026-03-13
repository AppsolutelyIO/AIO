<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\BlockScope;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\PageBlockValue;
use Appsolutely\AIO\Repositories\PageBlockValueRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Appsolutely\AIO\Tests\TestCase;

final class PageBlockValueRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PageBlockValueRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PageBlockValueRepository::class);
    }

    private function createBlock(): int
    {
        $groupId = DB::table('page_block_groups')->insertGetId([
            'title'      => 'Group',
            'status'     => Status::ACTIVE->value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);

        return DB::table('page_blocks')->insertGetId([
            'block_group_id' => $groupId,
            'title'          => 'Block ' . uniqid(),
            'class'          => 'App\\Block\\' . uniqid(),
            'scope'          => BlockScope::Page->value,
            'status'         => Status::ACTIVE->value,
            'created_at'     => now()->toDateTimeString(),
            'updated_at'     => now()->toDateTimeString(),
        ]);
    }

    private function createValue(int $blockId, array $attrs = []): PageBlockValue
    {
        $id = DB::table('page_block_values')->insertGetId(array_merge([
            'block_id'   => $blockId,
            'view_style' => 'default',
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ], $attrs));

        return PageBlockValue::find($id);
    }

    // --- findByBlockId ---

    public function test_find_by_block_id_returns_value(): void
    {
        $blockId = $this->createBlock();
        $this->createValue($blockId);

        $result = $this->repository->findByBlockId($blockId);

        $this->assertInstanceOf(PageBlockValue::class, $result);
        $this->assertEquals($blockId, $result->block_id);
    }

    public function test_find_by_block_id_returns_null_when_not_found(): void
    {
        $result = $this->repository->findByBlockId(9999);

        $this->assertNull($result);
    }

    // --- findByBlockIdAndTheme ---

    public function test_find_by_block_id_and_theme_returns_matching_theme(): void
    {
        $blockId = $this->createBlock();
        $this->createValue($blockId, ['theme' => 'dark']);
        $this->createValue($blockId, ['theme' => null]);

        $result = $this->repository->findByBlockIdAndTheme($blockId, 'dark');

        $this->assertEquals('dark', $result->theme);
    }

    public function test_find_by_block_id_and_theme_falls_back_to_null_theme(): void
    {
        $blockId = $this->createBlock();
        $this->createValue($blockId, ['theme' => null]);

        $result = $this->repository->findByBlockIdAndTheme($blockId, 'light');

        $this->assertNotNull($result);
        $this->assertNull($result->theme);
    }

    public function test_find_by_block_id_and_theme_returns_null_theme_when_no_theme_specified(): void
    {
        $blockId = $this->createBlock();
        $this->createValue($blockId, ['theme' => null]);

        $result = $this->repository->findByBlockIdAndTheme($blockId, null);

        $this->assertNotNull($result);
    }

    // --- createOrUpdate ---

    public function test_create_or_update_creates_new_value(): void
    {
        $blockId = $this->createBlock();

        $result = $this->repository->createOrUpdate($blockId, ['view' => 'hero', 'view_style' => 'card']);

        $this->assertInstanceOf(PageBlockValue::class, $result);
        $this->assertEquals($blockId, $result->block_id);
        $this->assertEquals('hero', $result->view);
    }

    public function test_create_or_update_updates_existing_value(): void
    {
        $blockId = $this->createBlock();
        $this->createValue($blockId, ['view' => 'old-view', 'view_style' => 'default']);

        $result = $this->repository->createOrUpdate($blockId, ['view' => 'new-view', 'view_style' => 'card']);

        $this->assertEquals('new-view', $result->view);
        $this->assertDatabaseCount('page_block_values', 1);
    }
}
