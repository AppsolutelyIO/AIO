<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders;

use Carbon\Carbon;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Models\Permission;
use Dcat\Admin\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminCoreSeeder extends Seeder
{
    public function run(): void
    {
        $now        = Carbon::now();
        $timestamps = [
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // admin_users
        $admin = Administrator::firstOrCreate(
            ['id' => 1],
            array_merge(
                $timestamps,
                [
                    'username'       => 'admin',
                    'password'       => Hash::make('password'),
                    'name'           => 'Administrator',
                    'avatar'         => null,
                    'remember_token' => null,
                ]
            )
        );

        // admin_roles
        $role = Role::firstOrCreate(
            ['id' => 1],
            array_merge($timestamps, [
                'name' => 'Administrator',
                'slug' => 'administrator',
            ])
        );

        // admin_permissions
        $permissions = [
            [
                'id'          => 1,
                'name'        => 'Auth management',
                'slug'        => 'auth-management',
                'http_method' => '',
                'http_path'   => '',
                'order'       => 1,
                'parent_id'   => 0,
            ],
            [
                'id'          => 2,
                'name'        => 'Users',
                'slug'        => 'users',
                'http_method' => '',
                'http_path'   => '/auth/users*',
                'order'       => 2,
                'parent_id'   => 1,
            ],
            [
                'id'          => 3,
                'name'        => 'Roles',
                'slug'        => 'roles',
                'http_method' => '',
                'http_path'   => '/auth/roles*',
                'order'       => 3,
                'parent_id'   => 1,
            ],
            [
                'id'          => 4,
                'name'        => 'Permissions',
                'slug'        => 'permissions',
                'http_method' => '',
                'http_path'   => '/auth/permissions*',
                'order'       => 4,
                'parent_id'   => 1,
            ],
            [
                'id'          => 5,
                'name'        => 'Menu',
                'slug'        => 'menu',
                'http_method' => '',
                'http_path'   => '/auth/menu*',
                'order'       => 5,
                'parent_id'   => 1,
            ],
            [
                'id'          => 6,
                'name'        => 'Extension',
                'slug'        => 'extension',
                'http_method' => '',
                'http_path'   => '/auth/extensions*',
                'order'       => 6,
                'parent_id'   => 1,
            ],
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['id' => $perm['id']],
                array_merge($timestamps, $perm)
            );
        }

        // admin_role_users (pivot)
        if ($admin && $role) {
            $admin->roles()->syncWithoutDetaching([$role->id]);
        }

    }
}
