<?php

namespace Database\Factories;

use App\Models\TransactionDisputeReason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionDispute>
 */
class TransactionDisputeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reason_id' => TransactionDisputeReason::inRandomOrder()->first()->id,
            'email' => fake()->safeEmail(),
            'comment' => fake()->text(100),
            'status' => fake()->randomElement(['pending', 'partially-paid', 'fully-paid', 'denied']),
        ];
    }
}
