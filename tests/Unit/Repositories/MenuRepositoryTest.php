<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\CmsMenu as Menu;
use Appsolutely\AIO\Repositories\MenuRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\Collection as NestedCollection;
use Appsolutely\AIO\Tests\TestCase;

final class MenuRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MenuRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(MenuRepository::class);
    }

    /**
     * Insert a menu record directly to bypass NodeTrait auto-management.
     */
    private function insertMenu(array $attrs = []): Menu
    {
        static $counter = 0;
        $counter++;
        $lft = $counter * 2 - 1;
        $rgt = $counter * 2;

        $id = DB::table('menus')->insertGetId(array_merge([
            'left'         => $lft,
            'right'        => $rgt,
            'parent_id'    => null,
            'title'        => 'Menu Item ' . $counter,
            'reference'    => 'ref-' . $counter . '-' . uniqid(),
            'type'         => 'link',
            'target'       => '_self',
            'is_external'  => false,
            'published_at' => now()->subMinute()->toDateTimeString(),
            'expired_at'   => null,
            'status'       => Status::ACTIVE->value,
            'created_at'   => now()->toDateTimeString(),
            'updated_at'   => now()->toDateTimeString(),
        ], $attrs));

        return Menu::find($id);
    }

    // --- getActiveMenus ---

    public function test_get_active_menus_returns_children_of_given_parent(): void
    {
        $parent = $this->insertMenu(['parent_id' => null]);
        $this->insertMenu(['parent_id' => $parent->id]);
        $this->insertMenu(['parent_id' => $parent->id]);
        $this->insertMenu(['parent_id' => null]); // different parent

        $result = $this->repository->getActiveMenus($parent->id, null);

        $this->assertCount(2, $result);
        $result->each(fn ($m) => $this->assertEquals($parent->id, $m->parent_id));
    }

    public function test_get_active_menus_excludes_inactive(): void
    {
        $parent = $this->insertMenu(['parent_id' => null]);
        $this->insertMenu(['parent_id' => $parent->id, 'status' => Status::ACTIVE->value]);
        $this->insertMenu(['parent_id' => $parent->id, 'status' => Status::INACTIVE->value]);

        $result = $this->repository->getActiveMenus($parent->id, null);

        $this->assertCount(1, $result);
    }

    public function test_get_active_menus_excludes_future_published(): void
    {
        $parent = $this->insertMenu(['parent_id' => null]);
        $this->insertMenu(['parent_id' => $parent->id, 'published_at' => now()->addHour()->toDateTimeString()]);
        $this->insertMenu(['parent_id' => $parent->id, 'published_at' => now()->subHour()->toDateTimeString()]);

        $result = $this->repository->getActiveMenus($parent->id, null);

        $this->assertCount(1, $result);
    }

    public function test_get_active_menus_excludes_expired(): void
    {
        $parent = $this->insertMenu(['parent_id' => null]);
        $this->insertMenu(['parent_id' => $parent->id, 'expired_at' => now()->subMinute()->toDateTimeString()]);
        $this->insertMenu(['parent_id' => $parent->id, 'expired_at' => null]);

        $result = $this->repository->getActiveMenus($parent->id, null);

        $this->assertCount(1, $result);
    }

    public function test_get_active_menus_returns_empty_when_none(): void
    {
        $result = $this->repository->getActiveMenus(999, null);

        $this->assertCount(0, $result);
    }

    // --- getActiveMenuTree ---

    public function test_get_active_menu_tree_returns_nested_collection(): void
    {
        $parent = $this->insertMenu(['parent_id' => null]);
        $this->insertMenu(['parent_id' => $parent->id]);

        $result = $this->repository->getActiveMenuTree($parent->id, null);

        $this->assertInstanceOf(NestedCollection::class, $result);
    }

    // --- findByReference ---

    public function test_find_by_reference_returns_menu(): void
    {
        $this->insertMenu(['reference' => 'main-nav']);

        $result = $this->repository->findByReference('main-nav');

        $this->assertInstanceOf(Menu::class, $result);
        $this->assertEquals('main-nav', $result->reference);
    }

    public function test_find_by_reference_returns_null_when_not_found(): void
    {
        $result = $this->repository->findByReference('nonexistent-reference');

        $this->assertNull($result);
    }

    public function test_find_by_reference_loads_children_relation(): void
    {
        $parent = $this->insertMenu(['reference' => 'nav-with-children']);
        $this->insertMenu(['parent_id' => $parent->id]);

        $result = $this->repository->findByReference('nav-with-children');

        $this->assertTrue($result->relationLoaded('children'));
    }
}
