<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders;

use Dcat\Admin\Models\Menu;
use Illuminate\Database\Seeder;

class AdminMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filesMenu = Menu::where('parent_id', 0)->where('title', 'Files')->first();
        if (! $filesMenu) {
            Menu::create([
                'parent_id'  => 0,
                'order'      => 180,
                'title'      => 'Files',
                'icon'       => 'fa-file',
                'uri'        => 'file-manager',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $configMenu = Menu::where('parent_id', 0)->where('title', 'Site Settings')->first();
        if (! $configMenu) {
            Menu::create([
                'parent_id'  => 0,
                'order'      => 190,
                'title'      => 'Site Settings',
                'icon'       => 'fa-cogs',
                'uri'        => 'site-settings',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $adminMenu = Menu::where('parent_id', 0)->where('title', 'Admin')->first();
        if (! $adminMenu) {
            $adminMenuId = Menu::create([
                'parent_id'  => 0,
                'order'      => 200,
                'title'      => 'Admin',
                'icon'       => 'feather icon-settings',
                'uri'        => '',
                'created_at' => now(),
                'updated_at' => now(),
            ])->id;
        } else {
            $adminMenuId = $adminMenu->id;
        }

        $usersMenu = Menu::where('parent_id', $adminMenuId)->where('title', 'Users')->first();
        if (! $usersMenu) {
            Menu::create([
                'parent_id'  => $adminMenuId,
                'order'      => 1,
                'title'      => 'Users',
                'icon'       => 'feather icon-users',
                'uri'        => 'auth/users',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $rolesMenu = Menu::where('parent_id', $adminMenuId)->where('title', 'Roles')->first();
        if (! $rolesMenu) {
            Menu::create([
                'parent_id'  => $adminMenuId,
                'order'      => 2,
                'title'      => 'Roles',
                'icon'       => '',
                'uri'        => 'auth/roles',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $permissionMenu = Menu::where('parent_id', $adminMenuId)->where('title', 'Permission')->first();
        if (! $permissionMenu) {
            Menu::create([
                'parent_id'  => $adminMenuId,
                'order'      => 3,
                'title'      => 'Permission',
                'icon'       => '',
                'uri'        => 'auth/permissions',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $menu = Menu::where('parent_id', $adminMenuId)->where('title', 'Menu')->first();
        if (! $menu) {
            Menu::create([
                'parent_id'  => $adminMenuId,
                'order'      => 4,
                'title'      => 'Menu',
                'icon'       => '',
                'uri'        => 'auth/menu',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $extensionsMenu = Menu::where('parent_id', $adminMenuId)->where('title', 'Extensions')->first();
        if (! $extensionsMenu) {
            Menu::create([
                'parent_id'  => $adminMenuId,
                'order'      => 5,
                'title'      => 'Extensions',
                'icon'       => '',
                'uri'        => 'auth/extensions',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
