<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders;

use Dcat\Admin\Models\Menu;
use Illuminate\Database\Seeder;

class ProductMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productMainMenu = Menu::where('parent_id', 0)->where('title', 'Products')->first();
        if (! $productMainMenu) {
            $productMainMenuId = Menu::create([
                'parent_id'  => 0,
                'order'      => 30,
                'title'      => 'Products',
                'icon'       => 'fa-product-hunt',
                'uri'        => null,
                'created_at' => now(),
                'updated_at' => now(),
            ])->id;
        } else {
            $productMainMenuId = $productMainMenu->id;
        }

        $productsMenu = Menu::where('parent_id', $productMainMenuId)->where('title', 'Products')->first();
        if (! $productsMenu) {
            Menu::create([
                'parent_id'  => $productMainMenuId,
                'order'      => 1,
                'title'      => 'Products',
                'icon'       => 'fa-list-ol',
                'uri'        => 'products/entry',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $productSkusMenu = Menu::where('parent_id', $productMainMenuId)->where('title', 'Product Skus')->first();
        if (! $productSkusMenu) {
            Menu::create([
                'parent_id'  => $productMainMenuId,
                'order'      => 2,
                'title'      => 'Product Skus',
                'icon'       => 'fa-list-ol',
                'uri'        => 'products/skus',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $productCategoriesMenu = Menu::where('parent_id', $productMainMenuId)->where('title', 'Product Categories')->first();
        if (! $productCategoriesMenu) {
            Menu::create([
                'parent_id'  => $productMainMenuId,
                'order'      => 3,
                'title'      => 'Product Categories',
                'icon'       => 'fa-list-ol',
                'uri'        => 'products/categories',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $attributesMenu = Menu::where('parent_id', $productMainMenuId)->where('title', 'Product Attributes')->first();
        if (! $attributesMenu) {
            Menu::create([
                'parent_id'  => $productMainMenuId,
                'order'      => 4,
                'title'      => 'Product Attributes',
                'icon'       => 'fa-list-ol',
                'uri'        => 'products/attributes',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
