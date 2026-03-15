<?php

namespace Appsolutely\AIO\Database\Seeders\Admin;

use Appsolutely\AIO\Models\Menu;
use Illuminate\Database\Seeder;

/**
 * Full admin menu — all features enabled.
 *
 * CMS items flattened at top level, ordered by usage frequency.
 * Orders and Products grouped (multiple sub-items each).
 * System administration grouped at the bottom.
 */
class AdminMenuFullSeeder extends Seeder
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
            'title'     => 'Categories',
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

        // ── Orders ───────────────────────────────────────────
        $ordersId = ++$id;
        Menu::create([
            'id'        => $ordersId,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Orders',
            'icon'      => 'feather icon-shopping-cart',
            'uri'       => '',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $ordersId,
            'order'     => ++$order,
            'title'     => 'All Orders',
            'icon'      => '',
            'uri'       => 'orders/entry',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $ordersId,
            'order'     => ++$order,
            'title'     => 'Shipments',
            'icon'      => '',
            'uri'       => 'orders/shipments',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $ordersId,
            'order'     => ++$order,
            'title'     => 'Refunds',
            'icon'      => '',
            'uri'       => 'orders/refunds',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $ordersId,
            'order'     => ++$order,
            'title'     => 'Coupons',
            'icon'      => '',
            'uri'       => 'coupons/entry',
        ]);

        // ── Products ─────────────────────────────────────────
        $productsId = ++$id;
        Menu::create([
            'id'        => $productsId,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Products',
            'icon'      => 'feather icon-shopping-bag',
            'uri'       => '',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $productsId,
            'order'     => ++$order,
            'title'     => 'All Products',
            'icon'      => '',
            'uri'       => 'products/entry',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $productsId,
            'order'     => ++$order,
            'title'     => 'Categories',
            'icon'      => '',
            'uri'       => 'products/categories',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $productsId,
            'order'     => ++$order,
            'title'     => 'Attributes',
            'icon'      => '',
            'uri'       => 'products/attributes',
        ]);

        Menu::create([
            'id'        => ++$id,
            'parent_id' => $productsId,
            'order'     => ++$order,
            'title'     => 'Reviews',
            'icon'      => '',
            'uri'       => 'products/reviews',
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

        // ── Releases ─────────────────────────────────────────
        Menu::create([
            'id'        => ++$id,
            'parent_id' => 0,
            'order'     => ++$order,
            'title'     => 'Releases',
            'icon'      => 'feather icon-package',
            'uri'       => 'releases',
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
