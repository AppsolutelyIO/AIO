<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Repositories\PageRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

final class PageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(PageRepository::class);
    }

    public function test_find_page_by_slug_returns_published_page(): void
    {
        $page = Page::factory()->create([
            'slug'         => 'test-page',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
            'expired_at'   => null,
        ]);

        $result = $this->repository->findPageBySlug('test-page', now());

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals($page->id, $result->id);
        $this->assertEquals('test-page', $result->slug);
    }

    public function test_find_page_by_slug_returns_null_for_unpublished_page(): void
    {
        Page::factory()->create([
            'slug'         => 'test-page',
            'status'       => Status::ACTIVE,
            'published_at' => now()->addDay(), // Future date
        ]);

        $result = $this->repository->findPageBySlug('test-page', now());

        $this->assertNull($result);
    }

    public function test_find_page_by_slug_returns_null_for_inactive_page(): void
    {
        Page::factory()->create([
            'slug'         => 'test-page',
            'status'       => Status::INACTIVE, // Inactive
            'published_at' => now()->subDay(),
        ]);

        $result = $this->repository->findPageBySlug('test-page', now());

        $this->assertNull($result);
    }

    public function test_find_page_by_slug_returns_null_for_expired_page(): void
    {
        Page::factory()->create([
            'slug'         => 'test-page',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDays(10),
            'expired_at'   => now()->subDay(), // Expired
        ]);

        $result = $this->repository->findPageBySlug('test-page', now());

        $this->assertNull($result);
    }

    public function test_find_page_by_slug_returns_null_for_non_existent_slug(): void
    {
        $result = $this->repository->findPageBySlug('non-existent-page', now());

        $this->assertNull($result);
    }

    public function test_find_page_by_id_returns_published_page(): void
    {
        $page = Page::factory()->create([
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
            'expired_at'   => null,
        ]);

        $result = $this->repository->findPageById($page->id, now());

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals($page->id, $result->id);
    }

    public function test_find_page_by_id_returns_null_for_unpublished_page(): void
    {
        $page = Page::factory()->create([
            'status'       => Status::ACTIVE,
            'published_at' => now()->addDay(), // Future date
        ]);

        $result = $this->repository->findPageById($page->id, now());

        $this->assertNull($result);
    }

    public function test_find_page_by_id_returns_null_for_inactive_page(): void
    {
        $page = Page::factory()->create([
            'status'       => Status::INACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $result = $this->repository->findPageById($page->id, now());

        $this->assertNull($result);
    }

    public function test_find_by_slug_returns_page_without_datetime_filtering(): void
    {
        $page = Page::factory()->create([
            'slug'         => 'test-page',
            'status'       => Status::INACTIVE, // Even inactive pages should be found
            'published_at' => now()->addDay(), // Even future pages should be found
        ]);

        $result = $this->repository->findBySlug('test-page');

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals($page->id, $result->id);
    }

    public function test_find_by_slug_returns_null_for_non_existent_slug(): void
    {
        $result = $this->repository->findBySlug('non-existent');

        $this->assertNull($result);
    }

    public function test_get_published_pages_for_sitemap_returns_only_published_pages(): void
    {
        $published1 = Page::factory()->create([
            'slug'         => 'page-1',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $published2 = Page::factory()->create([
            'slug'         => 'page-2',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDays(2),
        ]);

        Page::factory()->create([
            'slug'         => 'unpublished',
            'status'       => Status::INACTIVE,
            'published_at' => now()->subDay(),
        ]);

        // Create page without slug - use DB to ensure slug is actually null
        \DB::table('pages')->insert([
            'reference'    => (string) Str::ulid(),
            'title'        => 'No Slug Page',
            'name'         => 'No Slug Page',
            'slug'         => null,
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $result = $this->repository->getPublishedPagesForSitemap(now());

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $published1->id));
        $this->assertTrue($result->contains('id', $published2->id));
    }

    public function test_get_published_pages_for_sitemap_excludes_pages_without_slug(): void
    {
        Page::factory()->create([
            'slug'         => 'valid-page',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        // Create page with empty slug - use DB to ensure slug is actually empty
        \DB::table('pages')->insert([
            'reference'    => (string) Str::ulid(),
            'title'        => 'Empty Slug Page',
            'name'         => 'Empty Slug Page',
            'slug'         => '',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $result = $this->repository->getPublishedPagesForSitemap(now());

        $this->assertCount(1, $result);
        $this->assertEquals('valid-page', $result->first()->slug);
    }

    public function test_get_published_pages_for_sitemap_orders_by_published_at_desc(): void
    {
        $oldest = Page::factory()->create([
            'slug'         => 'oldest',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDays(5),
        ]);

        $newest = Page::factory()->create([
            'slug'         => 'newest',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $middle = Page::factory()->create([
            'slug'         => 'middle',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDays(3),
        ]);

        $result = $this->repository->getPublishedPagesForSitemap(now());

        $this->assertEquals($newest->id, $result->first()->id);
        $this->assertEquals($middle->id, $result->get(1)->id);
        $this->assertEquals($oldest->id, $result->last()->id);
    }

    public function test_update_setting_updates_page_setting(): void
    {
        $page       = Page::factory()->create();
        $newSetting = ['key' => 'value', 'nested' => ['data' => 'test']];

        $result = $this->repository->updateSetting($page->id, $newSetting);

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals($newSetting, $result->setting);
        $this->assertEquals($newSetting, $page->fresh()->setting);
    }

    public function test_get_by_parent_id_returns_pages_with_parent_id(): void
    {
        $parent = Page::factory()->create();

        $child1 = Page::factory()->create(['parent_id' => $parent->id, 'status' => Status::ACTIVE]);
        $child2 = Page::factory()->create(['parent_id' => $parent->id, 'status' => Status::ACTIVE]);
        Page::factory()->create(['parent_id' => null, 'status' => Status::ACTIVE]); // Different parent

        $result = $this->repository->getByParentId($parent->id);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $child1->id));
        $this->assertTrue($result->contains('id', $child2->id));
    }

    public function test_get_by_parent_id_returns_null_parent_pages_when_parent_id_is_null(): void
    {
        $page1 = Page::factory()->create(['parent_id' => null, 'status' => Status::ACTIVE]);
        $page2 = Page::factory()->create(['parent_id' => null, 'status' => Status::ACTIVE]);
        Page::factory()->create(['parent_id' => 999, 'status' => Status::ACTIVE]); // Has parent

        $result = $this->repository->getByParentId(null);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $page1->id));
        $this->assertTrue($result->contains('id', $page2->id));
    }

    public function test_get_by_parent_id_filters_by_published_when_datetime_provided(): void
    {
        $parent = Page::factory()->create();

        $published = Page::factory()->create([
            'parent_id'    => $parent->id,
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        Page::factory()->create([
            'parent_id'    => $parent->id,
            'status'       => Status::ACTIVE,
            'published_at' => now()->addDay(), // Future
        ]);

        $result = $this->repository->getByParentId($parent->id, now());

        $this->assertCount(1, $result);
        $this->assertEquals($published->id, $result->first()->id);
    }

    public function test_get_by_parent_id_does_not_filter_by_published_when_datetime_is_null(): void
    {
        $parent = Page::factory()->create();

        $published = Page::factory()->create([
            'parent_id'    => $parent->id,
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $future = Page::factory()->create([
            'parent_id'    => $parent->id,
            'status'       => Status::ACTIVE,
            'published_at' => now()->addDay(),
        ]);

        $result = $this->repository->getByParentId($parent->id, null);

        $this->assertCount(2, $result);
    }

    public function test_get_published_with_blocks_returns_published_pages(): void
    {
        $published = Page::factory()->create([
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        Page::factory()->create([
            'status'       => Status::INACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $result = $this->repository->getPublishedWithBlocks();

        $this->assertCount(1, $result);
        $this->assertEquals($published->id, $result->first()->id);
    }

    public function test_get_published_with_blocks_uses_current_datetime_when_not_provided(): void
    {
        $published = Page::factory()->create([
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        Page::factory()->create([
            'status'       => Status::ACTIVE,
            'published_at' => now()->addDay(), // Future
        ]);

        $result = $this->repository->getPublishedWithBlocks();

        $this->assertCount(1, $result);
        $this->assertEquals($published->id, $result->first()->id);
    }

    public function test_get_published_with_blocks_orders_by_published_at_desc(): void
    {
        $oldest = Page::factory()->create([
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDays(5),
        ]);

        $newest = Page::factory()->create([
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $result = $this->repository->getPublishedWithBlocks();

        $this->assertEquals($newest->id, $result->first()->id);
        $this->assertEquals($oldest->id, $result->last()->id);
    }
}
