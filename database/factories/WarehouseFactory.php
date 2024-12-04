<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Warehouse ' . $this->faker->word(),
            'phone_number' => '639' . $this->faker->unique()->numerify('#########'),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
