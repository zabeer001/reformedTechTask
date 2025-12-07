<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = Str::upper(Str::random(6));

        return [
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . $number,
            'ordered_at' => $this->faker->dateTimeBetween('-2 months'),
            'total_amount' => $this->faker->randomFloat(2, 50, 1200),
            'customer_name' => $this->faker->name(),
            'status' => $this->faker->randomElement(OrderStatus::values()),
        ];
    }
}
