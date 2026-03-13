<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Menu;
use Appsolutely\AIO\Services\Contracts\MenuServiceInterface;
use Appsolutely\AIO\Services\MenuService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Appsolutely\AIO\Tests\TestCase;

final class MenuServiceTest extends TestCase
{
    use RefreshDatabase;

    private MenuService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MenuService::class);
    }

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
            'title'        => 'Menu ' . $counter,
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

    // --- Container resolution ---

    public function test_resolves_from_container_via_interface(): void
    {
        $service = app(MenuServiceInterface::class);

        $this->assertInstanceOf(MenuService::class, $service);
    }

    public function test_implements_menu_service_interface(): void
    {
        $this->assertInstanceOf(MenuServiceInterface::class, $this->service);
    }

    // --- getActiveMenus ---

    public function test_get_active_menus_returns_active_children(): void
    {
        $parent = $this->insertMenu();
        $this->insertMenu(['parent_id' => $parent->id]);
        $this->insertMenu(['parent_id' => $parent->id]);

        $result = $this->service->getActiveMenus($parent->id);

        $this->assertCount(2, $result);
    }

    public function test_get_active_menus_returns_collection(): void
    {
        $parent = $this->insertMenu();

        $result = $this->service->getActiveMenus($parent->id);

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_get_active_menus_excludes_inactive_children(): void
    {
        $parent = $this->insertMenu();
        $this->insertMenu(['parent_id' => $parent->id, 'status' => Status::ACTIVE->value]);
        $this->insertMenu(['parent_id' => $parent->id, 'status' => Status::INACTIVE->value]);

        $result = $this->service->getActiveMenus($parent->id);

        $this->assertCount(1, $result);
    }

    public function test_get_active_menus_returns_empty_when_no_children(): void
    {
        $parent = $this->insertMenu();

        $result = $this->service->getActiveMenus($parent->id);

        $this->assertEmpty($result);
    }

    // --- findByReference ---

    public function test_find_by_reference_returns_menu(): void
    {
        $this->insertMenu(['reference' => 'top-nav']);

        $result = $this->service->findByReference('top-nav');

        $this->assertInstanceOf(Menu::class, $result);
        $this->assertEquals('top-nav', $result->reference);
    }

    public function test_find_by_reference_returns_null_when_not_found(): void
    {
        $result = $this->service->findByReference('does-not-exist');

        $this->assertNull($result);
    }

    public function test_find_by_reference_returns_correct_menu_among_many(): void
    {
        $this->insertMenu(['reference' => 'header-nav']);
        $this->insertMenu(['reference' => 'footer-nav']);
        $this->insertMenu(['reference' => 'sidebar-nav']);

        $result = $this->service->findByReference('footer-nav');

        $this->assertSame('footer-nav', $result->reference);
    }

    // --- getMenusByReference ---

    public function test_get_menus_by_reference_returns_children_of_found_menu(): void
    {
        $parent = $this->insertMenu(['reference' => 'main-menu']);
        $this->insertMenu(['parent_id' => $parent->id]);
        $this->insertMenu(['parent_id' => $parent->id]);

        $result = $this->service->getMenusByReference('main-menu');

        $this->assertCount(2, $result);
    }

    public function test_get_menus_by_reference_returns_empty_when_not_found(): void
    {
        $result = $this->service->getMenusByReference('nonexistent-menu');

        $this->assertEmpty($result);
    }

    public function test_get_menus_by_reference_returns_empty_collection_type(): void
    {
        $result = $this->service->getMenusByReference('nonexistent-menu');

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
    }

    public function test_get_menus_by_reference_returns_empty_when_menu_has_no_children(): void
    {
        $this->insertMenu(['reference' => 'empty-menu']);

        $result = $this->service->getMenusByReference('empty-menu');

        $this->assertEmpty($result);
    }
}
