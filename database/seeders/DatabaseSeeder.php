<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);

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
