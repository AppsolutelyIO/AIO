<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\BlockScope;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Repositories\PageBlockGroupRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Appsolutely\AIO\Tests\TestCase;

final class PageBlockGroupRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PageBlockGroupRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PageBlockGroupRepository::class);
    }

    private function createGroup(array $attrs = []): int
    {
        return DB::table('page_block_groups')->insertGetId(array_merge([
            'title'      => 'Group ' . uniqid(),
            'sort'       => 0,
            'status'     => Status::ACTIVE->value,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ], $attrs));
    }

    private function createBlock(int $groupId, array $attrs = []): int
    {
        return DB::table('page_blocks')->insertGetId(array_merge([
            'block_group_id' => $groupId,
            'title'          => 'Block ' . uniqid(),
            'reference'      => 'ref-' . uniqid(),
            'class'          => 'Appsolutely\\AIO\\Block\\' . uniqid(),
            'scope'          => BlockScope::Page->value,
            'sort'           => 0,
            'status'         => Status::ACTIVE->value,
            'created_at'     => now()->toDateTimeString(),
            'updated_at'     => now()->toDateTimeString(),
        ], $attrs));
    }

    // --- getCategorisedBlocks ---

    public function test_get_categorised_blocks_returns_groups_with_active_blocks(): void
    {
        $groupId = $this->createGroup();
        $this->createBlock($groupId);
        $this->createBlock($groupId);

        $result = $this->repository->getCategorisedBlocks();

        $this->assertCount(1, $result);
        $this->assertCount(2, $result->first()->blocks);
    }

    public function test_get_categorised_blocks_excludes_groups_with_no_active_blocks(): void
    {
        $groupId = $this->createGroup();
        $this->createBlock($groupId, ['status' => Status::INACTIVE->value]);

        $result = $this->repository->getCategorisedBlocks();

        $this->assertCount(0, $result);
    }

    public function test_get_categorised_blocks_excludes_inactive_groups(): void
    {
        $groupId = $this->createGroup(['status' => Status::INACTIVE->value]);
        $this->createBlock($groupId);

        $result = $this->repository->getCategorisedBlocks();

        $this->assertCount(0, $result);
    }

    public function test_get_categorised_blocks_maps_block_properties(): void
    {
        $groupId = $this->createGroup();
        $this->createBlock($groupId, ['title' => 'My Block', 'reference' => 'my-ref', 'template' => '<div>test</div>']);

        $result = $this->repository->getCategorisedBlocks();
        $block  = $result->first()->blocks->first();

        $this->assertEquals('My Block', $block->label);
        $this->assertEquals('my-ref', $block->type);
        $this->assertEquals('<div>test</div>', $block->content);
        $this->assertEquals('section', $block->tagName);
    }

    public function test_get_categorised_blocks_returns_empty_when_none(): void
    {
        $result = $this->repository->getCategorisedBlocks();

        $this->assertCount(0, $result);
    }
}
