<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShippingOption>
 */
class ShippingOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Shipping Option ' . $this->faker->word();
        return [
            'name' => $name,
            'slug' => str($name)->slug('_'),
            'description' => $this->faker->sentences(3, true),
            'shipping_days' => $this->faker->numberBetween(1, 7),
            'price' => $this->faker->numberBetween(60, 200),
        ];
    }
}
