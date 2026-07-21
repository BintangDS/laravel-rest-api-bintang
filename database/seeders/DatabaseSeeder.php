<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
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

        // Seed default products
        Product::create([
            'name' => 'Mangrove Soap',
            'price' => 2.79,
            'status' => 'active',
            'description' => 'An eco-friendly soap crafted from sustainably grown mangroves on Dompak Island. Each purchase directly funds rehabilitation efforts and supports local coastal communities.',
        ]);

        Product::create([
            'name' => 'Mangrove Soap Premium',
            'price' => 3.48,
            'status' => 'active',
            'description' => 'A premium, all-natural soap infused with organic mangrove extracts. Directly supports sustainable livelihoods for fishermen and protects Dompak Island\'s biodiversity.',
        ]);

        Product::create([
            'name' => 'Mangrove Batik',
            'price' => 15.00,
            'status' => 'active',
            'description' => 'An exquisite, hand-crafted Indonesian batik made using sustainable, natural dyes derived from Dompak Island\'s mangroves. Each piece preserves cultural heritage and supports livelihood empowerment for coastal artisan families.',
        ]);

        Product::create([
            'name' => 'Organic Mangrove Dodol',
            'price' => 2.00,
            'status' => 'active',
            'description' => 'A sweet, traditional Indonesian chewy treat hand-crafted by coastal women artisans (KAWANIBU) from sustainably harvested mangrove fruits in Tanjung Pakis.',
        ]);

        Product::create([
            'name' => 'Mangrove Tree Adoption',
            'price' => 2.00,
            'status' => 'active',
            'description' => 'Adopt a single mangrove tree to be planted and nurtured by local coastal farmers. Includes access to digital growth monitoring.',
        ]);

        Product::create([
            'name' => 'Blue Carbon Restoration Package',
            'price' => 8.39,
            'status' => 'active',
            'description' => 'A comprehensive climate action package including a single mangrove planting, seagrass restoration support, and economic empowerment for local coastal farmers.',
        ]);
    }
}