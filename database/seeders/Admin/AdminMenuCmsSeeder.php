<?php

namespace Appsolutely\AIO\Database\Seeders\Admin;

use Appsolutely\AIO\Models\Menu;
use Illuminate\Database\Seeder;

/**
 * CMS-only admin menu — content management features.
 *
 * Flat layout ordered by usage frequency. Articles and Pages first,
 * system administration grouped at the bottom.
 */
class AdminMenuCmsSeeder extends Seeder
{
    public function run(): void
    {
        Menu::truncate();

        $id    = 0;
        $order = 0;

        // ── Dashboard ────────────────────────────────────────
        Menu::create([
            'id'        => ++$id,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Dashboard',
            'icon'      => 'feather icon-bar-chart-2',
            'uri'       => '/',
        ]);

        // ── Articles ─────────────────────────────────────────
        Menu::create([
            'id'        => ++$id,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Articles',
            'icon'      => 'feather icon-file-text',
            'uri'       => 'articles/entry',
        ]);

        // ── Pages ────────────────────────────────────────────
        Menu::create([
            'id'        => ++$id,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Pages',
            'icon'      => 'feather icon-layout',
            'uri'       => 'pages/entry',
        ]);

        // ── Categories ───────────────────────────────────────
        Menu::create([
            'id'        => ++$id,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Article Categories',
            'icon'      => 'feather icon-folder',
            'uri'       => 'articles/categories',
        ]);

        // ── Menus ────────────────────────────────────────────
        Menu::create([
            'id'        => ++$id,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Menus',
            'icon'      => 'feather icon-menu',
            'uri'       => 'menus/entry',
        ]);

        // ── Forms ────────────────────────────────────────────
        Menu::create([
            'id'        => ++$id,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Forms',
            'icon'      => 'feather icon-clipboard',
            'uri'       => 'forms',
        ]);

        // ── Notifications ────────────────────────────────────
        Menu::create([
            'id'        => ++$id,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Notifications',
            'icon'      => 'feather icon-bell',
            'uri'       => 'notifications',
        ]);

        // ── Media ────────────────────────────────────────────
        Menu::create([
            'id'        => ++$id,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Media',
            'icon'      => 'feather icon-image',
            'uri'       => 'file-manager',
        ]);

        // ── Site Settings ────────────────────────────────────
        Menu::create([
            'id'        => ++$id,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Site Settings',
            'icon'      => 'feather icon-sliders',
            'uri'       => 'site-settings',
        ]);

        // ── System ───────────────────────────────────────────
        $systemId = ++$id;
        Menu::create([
            'id'        => $systemId,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'System',
            'icon'      => 'feather icon-settings',
            'uri'       => '',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $systemId,
            'order'     => ++$order,
            'title'     => 'Admin Users',
            'icon'      => '',
            'uri'       => 'auth/users',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $systemId,
            'order'     => ++$order,
            'title'     => 'Roles',
            'icon'      => '',
            'uri'       => 'auth/roles',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $systemId,
            'order'     => ++$order,
            'title'     => 'Permissions',
            'icon'      => '',
            'uri'       => 'auth/permissions',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $systemId,
            'order'     => ++$order,
            'title'     => 'Menu',
            'icon'      => '',
            'uri'       => 'auth/menu',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $systemId,
            'order'     => ++$order,
            'title'     => 'Extensions',
            'icon'      => '',
            'uri'       => 'auth/extensions',
        ]);

        (new Menu())->flushCache();
    }
}
