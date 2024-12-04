<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductDetail>
 */
class ProductDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'weight' => fake()->numberBetween(1, 10),
            'length' => fake()->numberBetween(1, 10),
            'width' => fake()->numberBetween(1, 10),
            'height' => fake()->numberBetween(1, 10),
        ];
    }
}
