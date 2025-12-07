<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Binzabir Tareq',
            'email' => 'binzabirtareq@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $products = Product::factory()
            ->count(15)
            ->create();

        $products->each(function (Product $product): void {
            Stock::factory()
                ->count(3)
                ->create([
                    'product_id' => $product->id,
                ]);
        });
    }
}
