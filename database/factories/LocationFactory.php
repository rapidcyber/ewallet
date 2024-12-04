<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $minLatitude = 14.38;
        $maxLatitude = 14.83;
        $minLongitude = 120.91;
        $maxLongitude = 121.13;

        return [
            'address' => fake()->address,
            'latitude' => fake()->latitude($minLatitude, $maxLatitude),
            'longitude' => fake()->longitude($minLongitude, $maxLongitude),
        ];
    }
}
