<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders;

use Dcat\Admin\Models\Menu;
use Illuminate\Database\Seeder;

class OrderMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ordersMenu = Menu::where('parent_id', 0)->where('title', 'Orders')->first();
        if (! $ordersMenu) {
            Menu::create([
                'parent_id'  => 0,
                'order'      => 30,
                'title'      => 'Orders',
                'icon'       => 'fa-shopping-cart',
                'uri'        => 'orders/entry',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
