<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::query()->updateOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
        );

        foreach (config('settings.permissions', []) as $group => $permissions) {
            foreach ($permissions as $permission) {
                $perm = Permission::query()->updateOrCreate(
                    ['name' => $permission, 'guard_name' => 'web'],
                    [
                        'display_name' => Str::replace('_', ' ', Str::title($permission)),
                        'permission_group' => $group,
                    ],
                );

                $role->givePermissionTo($perm);
            }
        }
    }
}
