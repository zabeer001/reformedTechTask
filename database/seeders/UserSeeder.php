<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'colin@gmail.com'],
            [
                'name' => 'Binzabir Tareq',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        User::factory()
            ->count(30)
            ->create();
    }
}
