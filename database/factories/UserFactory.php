<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 year', 'now');

        $phone_prefix = "639";
        return [
            'username' => fake()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone_iso' => 'PH',
            'phone_number' => $phone_prefix . fake()->unique()->numerify('#########'),
            'phone_verified_at' => now(),
            'remember_token' => Str::random(10),
            // 'pin' => fake()->numerify('####'),
            'pin' => '0420',
            'app_id' => fake()->unique()->uuid(),
            'apply_for_realholmes' => fake()->randomElement(['merchant', 'owner', null]),
            'created_at' => $date,
            'updated_at' => $date
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
