<?php

namespace Database\Factories;

use App\Models\ReturnOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnOrderDispute>
 */
class ReturnOrderDisputeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-6 months', 'now');
        return [
            'return_order_id' => ReturnOrder::inRandomOrder()->first()->id,
            'comment' => fake()->text(50),
            'created_at' => $date,
            'updated_at' => $date
        ];
    }
}
