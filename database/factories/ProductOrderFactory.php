<?php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\ShippingOption;
use App\Models\ShippingStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductOrder>
 */
class ProductOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 month', 'now');

        $user = User::active()->inRandomOrder()->first();
        $product = Product::inRandomOrder()->first();
        $count = fake()->numberBetween(1, 3);

        $shipping_status = ShippingStatus::whereDoesntHave('sub_statuses')->inRandomOrder()->first();
        if (in_array($shipping_status->name, ['Completed', 'Cancellation', 'Failed Delivery'])) {
            if ($shipping_status->name == 'Cancellation' || $shipping_status->name == 'Failed Delivery') {
                $reason = fake()->text(50);
            }

            $processed_at = fake()->dateTimeBetween($date, 'now');
        }



        return [
            'product_id' => $product->id,
            'buyer_id' => $user->id,
            'buyer_type' => User::class,
            'quantity' => $count,
            'amount' => $product->price,
            'shipping_fee' => fake()->randomElement([50, 60, 70, 80, 90, 100]),
            'order_number' => fake()->unique()->numerify('####################'),
            'tracking_number' => fake()->unique()->numerify('################'),
            'shipping_status_id' => $shipping_status->id,
            'processed_at' => $processed_at ?? null,
            'termination_reason' => $reason ?? null,
            'payment_option_id' => 1,
            'created_at' => $date,
            'updated_at' => $processed_at ?? $date
        ];
    }
}
