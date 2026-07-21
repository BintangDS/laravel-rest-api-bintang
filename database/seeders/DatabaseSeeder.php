<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin account
        User::create([
            'name' => 'Admin System',
            'email' => 'admin@mail.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Create regular User account
        User::create([
            'name' => 'Budi Customer',
            'email' => 'budi@mail.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
    }
}