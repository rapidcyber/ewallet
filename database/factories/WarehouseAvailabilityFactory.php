<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WarehouseAvailability>
 */
class WarehouseAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start_hour = fake()->numberBetween(6, 9);
        $minute = fake()->randomElement(['00', '30']);
        $end_hour = $start_hour + fake()->numberBetween(7, 10);

        return [
            'day_name' => fake()->randomElement(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']),
            'start_time' => $start_hour . ':' . $minute,
            'end_time' => $end_hour . ':' . $minute,
        ];
    }
}
