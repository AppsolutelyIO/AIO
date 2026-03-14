<?php

namespace Appsolutely\AIO\Models;

use Illuminate\Database\Seeder;

/**
 * CMS-only admin menu — content management features.
 *
 * Includes: Dashboard, CMS (Articles, Pages, Menus, Forms, Notifications),
 * Media, Site Settings, and System Administration.
 */
class AdminMenuCmsSeeder extends Seeder
{
    public function run(): void
    {
        Menu::truncate();

        $order = 0;

        // ── Dashboard ────────────────────────────────────────
        Menu::create([
            'id'        => 1,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Dashboard',
            'icon'      => 'feather icon-bar-chart-2',
            'uri'       => '/',
        ]);

        // ── Content ──────────────────────────────────────────
        Menu::create([
            'id'        => 2,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Content',
            'icon'      => 'feather icon-edit',
            'uri'       => '',
        ]);

        Menu::create([
            'id'        => 3,
            'parent_id' => 2,
            'order'     => ++$order,
            'title'     => 'Articles',
            'icon'      => '',
            'uri'       => 'articles/entry',
        ]);

        Menu::create([
            'id'        => 4,
            'parent_id' => 2,
            'order'     => ++$order,
            'title'     => 'Categories',
            'icon'      => '',
            'uri'       => 'articles/categories',
        ]);

        Menu::create([
            'id'        => 5,
            'parent_id' => 2,
            'order'     => ++$order,
            'title'     => 'Pages',
            'icon'      => '',
            'uri'       => 'pages/entry',
        ]);

        Menu::create([
            'id'        => 6,
            'parent_id' => 2,
            'order'     => ++$order,
            'title'     => 'Page Blocks',
            'icon'      => '',
            'uri'       => 'pages/blocks',
        ]);

        Menu::create([
            'id'        => 7,
            'parent_id' => 2,
            'order'     => ++$order,
            'title'     => 'Menus',
            'icon'      => '',
            'uri'       => 'menus/entry',
        ]);

        // ── Forms ────────────────────────────────────────────
        Menu::create([
            'id'        => 8,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Forms',
            'icon'      => 'feather icon-clipboard',
            'uri'       => 'forms',
        ]);

        // ── Notifications ────────────────────────────────────
        Menu::create([
            'id'        => 9,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Notifications',
            'icon'      => 'feather icon-bell',
            'uri'       => 'notifications',
        ]);

        // ── Media ────────────────────────────────────────────
        Menu::create([
            'id'        => 10,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Media',
            'icon'      => 'feather icon-image',
            'uri'       => 'file-manager',
        ]);

        // ── Site Settings ────────────────────────────────────
        Menu::create([
            'id'        => 11,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Site Settings',
            'icon'      => 'feather icon-sliders',
            'uri'       => 'site-settings',
        ]);

        // ── System ───────────────────────────────────────────
        Menu::create([
            'id'        => 12,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'System',
            'icon'      => 'feather icon-settings',
            'uri'       => '',
        ]);

        Menu::create([
            'id'        => 13,
            'parent_id' => 12,
            'order'     => ++$order,
            'title'     => 'Admin Users',
            'icon'      => '',
            'uri'       => 'auth/users',
        ]);

        Menu::create([
            'id'        => 14,
            'parent_id' => 12,
            'order'     => ++$order,
            'title'     => 'Roles',
            'icon'      => '',
            'uri'       => 'auth/roles',
        ]);

        Menu::create([
            'id'        => 15,
            'parent_id' => 12,
            'order'     => ++$order,
            'title'     => 'Permissions',
            'icon'      => '',
            'uri'       => 'auth/permissions',
        ]);

        Menu::create([
            'id'        => 16,
            'parent_id' => 12,
            'order'     => ++$order,
            'title'     => 'Menu',
            'icon'      => '',
            'uri'       => 'auth/menu',
        ]);

        Menu::create([
            'id'        => 17,
            'parent_id' => 12,
            'order'     => ++$order,
            'title'     => 'Extensions',
            'icon'      => '',
            'uri'       => 'auth/extensions',
        ]);

        (new Menu())->flushCache();
    }
}
