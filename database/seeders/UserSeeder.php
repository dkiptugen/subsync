<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'info@radioafricagroup.co.ke'],
            [
                'username' => 'admin',
                'name' => 'Default Administrator',
                'password' => bcrypt('1234567'),
                'status' => 1,
                'type' => 'owner',
            ],
        );

        $role = Role::query()
            ->where('name', 'Super Admin')
            ->where('guard_name', 'web')
            ->first();

        if ($role !== null) {
            $user->syncRoles([$role]);
        }
    }
}
