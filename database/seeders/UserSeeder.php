<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        (new User)->updateOrCreate(
            [
                'email' => 'info@radioafricagroup.co.ke',
            ],
            [
                'username' => 'admin',
                'name' => 'Default Administrator',
                'password' => bcrypt('1234567'),
                'status' => true,
                'type' => 'owner',
            ]
        );
    }
}
