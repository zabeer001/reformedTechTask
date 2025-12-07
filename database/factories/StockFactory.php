<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchasePrice = $this->faker->randomFloat(2, 5, 50);
        $salePrice = $purchasePrice + $this->faker->randomFloat(2, 5, 40);

        return [
            'product_id' => Product::factory(),
            'sku' => Str::upper(Str::random(10)),
            'sale_price' => $salePrice,
            'purchase_price' => $purchasePrice,
            'quantity' => $this->faker->numberBetween(5, 80),
            'last_update_at' => now(),
        ];
    }
}
