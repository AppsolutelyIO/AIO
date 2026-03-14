<?php

namespace Appsolutely\AIO\Database\Seeders;

use Appsolutely\AIO\Models\Administrator;
use Appsolutely\AIO\Models\Permission;
use Appsolutely\AIO\Models\Role;
use Illuminate\Database\Seeder;

class AdminTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds admin user, roles, permissions, and the full menu.
     * Override the menu profile by passing --class with a specific menu seeder.
     */
    public function run(): void
    {
        $this->seedAdminUser();
        $this->seedRoles();
        $this->seedPermissions();

        $this->call(AdminMenuFullSeeder::class);
    }

    protected function seedAdminUser(): void
    {
        Administrator::truncate();
        Administrator::create([
            'username' => 'admin',
            'password' => bcrypt('admin'),
            'name'     => 'Administrator',
        ]);
    }

    protected function seedRoles(): void
    {
        Role::truncate();
        Role::create([
            'name' => 'Administrator',
            'slug' => Role::ADMINISTRATOR,
        ]);

        Administrator::first()->roles()->save(Role::first());
    }

    protected function seedPermissions(): void
    {
        Permission::truncate();
        Permission::insert([
            [
                'id'          => 1,
                'name'        => 'Auth management',
                'slug'        => 'auth-management',
                'http_method' => '',
                'http_path'   => '',
                'parent_id'   => 0,
                'order'       => 1,
            ],
            [
                'id'          => 2,
                'name'        => 'Users',
                'slug'        => 'users',
                'http_method' => '',
                'http_path'   => '/auth/users*',
                'parent_id'   => 1,
                'order'       => 2,
            ],
            [
                'id'          => 3,
                'name'        => 'Roles',
                'slug'        => 'roles',
                'http_method' => '',
                'http_path'   => '/auth/roles*',
                'parent_id'   => 1,
                'order'       => 3,
            ],
            [
                'id'          => 4,
                'name'        => 'Permissions',
                'slug'        => 'permissions',
                'http_method' => '',
                'http_path'   => '/auth/permissions*',
                'parent_id'   => 1,
                'order'       => 4,
            ],
            [
                'id'          => 5,
                'name'        => 'Menu',
                'slug'        => 'menu',
                'http_method' => '',
                'http_path'   => '/auth/menu*',
                'parent_id'   => 1,
                'order'       => 5,
            ],
            [
                'id'          => 6,
                'name'        => 'Extension',
                'slug'        => 'extension',
                'http_method' => '',
                'http_path'   => '/auth/extensions*',
                'parent_id'   => 1,
                'order'       => 6,
            ],
        ]);
    }
}
