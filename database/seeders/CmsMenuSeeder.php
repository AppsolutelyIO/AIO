<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders;

use Dcat\Admin\Models\Menu;
use Illuminate\Database\Seeder;

class CmsMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dashboardMenu = Menu::where('parent_id', 0)->where('title', 'Dashboard')->first();
        if (! $dashboardMenu) {
            Menu::create([
                'parent_id'  => 0,
                'order'      => 0,
                'title'      => 'Dashboard',
                'icon'       => 'feather icon-bar-chart-2',
                'uri'        => '/',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $pagesMenu = Menu::where('parent_id', 0)->where('title', 'Pages')->first();
        if (! $pagesMenu) {
            Menu::create([
                'parent_id'  => 0,
                'order'      => 10,
                'title'      => 'Pages',
                'icon'       => 'fa-file',
                'uri'        => 'pages/entry',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $cmsMenu = Menu::where('parent_id', 0)->where('title', 'CMS')->first();
        if (! $cmsMenu) {
            $cmsMenuId = Menu::create([
                'parent_id'  => 0,
                'order'      => 20,
                'title'      => 'CMS',
                'icon'       => 'fa-file',
                'uri'        => null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->id;
        } else {
            $cmsMenuId = $cmsMenu->id;
        }

        $articlesMenu = Menu::where('parent_id', $cmsMenuId)->where('title', 'Articles')->first();
        if (! $articlesMenu) {
            Menu::create([
                'parent_id'  => $cmsMenuId,
                'order'      => 1,
                'title'      => 'Articles',
                'icon'       => 'fa-file-text',
                'uri'        => 'articles/entry',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $articleCategoriesMenu = Menu::where('parent_id', $cmsMenuId)->where('title', 'Article Categories')->first();
        if (! $articleCategoriesMenu) {
            Menu::create([
                'parent_id'  => $cmsMenuId,
                'order'      => 2,
                'title'      => 'Article Categories',
                'icon'       => 'fa-folder',
                'uri'        => 'articles/categories',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $menusMenu = Menu::where('parent_id', $cmsMenuId)->where('title', 'Menu')->first();
        if (! $menusMenu) {
            Menu::create([
                'parent_id'  => $cmsMenuId,
                'order'      => 3,
                'title'      => 'Menu',
                'icon'       => 'fa-list',
                'uri'        => 'menus/entry',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $formsMenu = Menu::where('parent_id', $cmsMenuId)->where('title', 'Dynamic Forms')->first();
        if (! $formsMenu) {
            Menu::create([
                'parent_id'  => $cmsMenuId,
                'order'      => 4,
                'title'      => 'Dynamic Forms',
                'icon'       => 'fa-wpforms',
                'uri'        => 'forms',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $blocksMenu = Menu::where('parent_id', $cmsMenuId)->where('title', 'Page Blocks')->first();
        if (! $blocksMenu) {
            Menu::create([
                'parent_id'  => $cmsMenuId,
                'order'      => 5,
                'title'      => 'Page Blocks',
                'icon'       => 'fa-list',
                'uri'        => 'pages/blocks',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
