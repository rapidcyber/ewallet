<?php

namespace Database\Factories;

use App\Models\ProductOrder;
use App\Models\ReturnOrderStatus;
use App\Models\ReturnReason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnOrder>
 */
class ReturnOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $created_at = fake()->dateTimeBetween('-1 year', '-1 month');
        $updated_at = fake()->dateTimeBetween($created_at, 'now');
        return [
            'product_order_id' => ProductOrder::inRandomOrder()->first()->id,
            'return_reason_id' => ReturnReason::inRandomOrder()->first()->id,
            'comment' => fake()->text(100),
            'return_order_status_id' => ReturnOrderStatus::whereNotIn('name', ['Return In Progress', 'Rejected', 'Resolved', 'Dispute In Progress'])->inRandomOrder()->first()->id,
            'created_at' => $created_at,
            'updated_at' => $updated_at
        ];
    }
}
