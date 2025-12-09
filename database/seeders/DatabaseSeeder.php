<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use App\Services\Images\ImageService;
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

        /** @var ImageService $imageService */
        $imageService = app(ImageService::class);

        $products = Product::factory()->count(15)->create();

        $products->each(function (Product $product) use ($imageService): void {
            $product->forceFill([
                'image_path' => $imageService->storePlaceholder('products'),
            ])->save();

            $stocks = Stock::factory()
                ->count(3)
                ->create([
                    'product_id' => $product->id,
                ]);

            $stocks->each(function (Stock $stock) use ($imageService): void {
                $stock->forceFill([
                    'image_path' => $imageService->storePlaceholder('stocks'),
                ])->save();
            });
        });
    }
}
