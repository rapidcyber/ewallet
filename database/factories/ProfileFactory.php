<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = \App\Models\Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phone_prefix = "6328";
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->lastName(),
            'surname' => fake()->lastName(),
            'suffix' => fake()->randomElement([null, fake()->suffix()]),
            'mother_maiden_name' => fake()->name('female'),
            'nationality' => 'Filipino',
            'sex' => fake()->randomElement(['male', 'female']),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-40 years', '-18 years'),
            'landline_iso' => 'PH',
            'landline_number' => $phone_prefix . fake()->unique()->numerify('#######'),
            'status' => fake()->boolean(70) ? 'verified' : fake()->randomElement(['pending', 'rejected', 'deactivated']),
        ];
    }
}
