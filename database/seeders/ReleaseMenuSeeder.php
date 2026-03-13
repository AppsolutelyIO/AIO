<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders;

use Dcat\Admin\Models\Menu;
use Illuminate\Database\Seeder;

class ReleaseMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $releasesMenu = Menu::where('parent_id', 0)->where('title', 'Releases')->first();
        if (! $releasesMenu) {
            Menu::create([
                'parent_id'  => 0,
                'order'      => 100,
                'title'      => 'Releases',
                'icon'       => 'fa-rocket',
                'uri'        => 'releases',
                'created_at' => now(),
                'updated_at' => now(),
            ])->id;
        }
    }
}
