<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\ProductOrder;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderDispute;
use App\Models\ReturnOrderDisputeResponse;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReturnOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product_orders = ProductOrder::whereHas('shipping_status', function ($q) {
            $q->where('name', 'Completed');
        })
            ->whereDoesntHave('return_orders')
            ->inRandomOrder()
            ->limit(20)
            ->get();

        foreach ($product_orders as $product_order) {
            $dont_skip = fake()->boolean(60);

            if (!$dont_skip || $product_order->return_orders()->count() > 0) {
                continue;
            }

            $created_at = fake()->dateTimeBetween('-1 year', '-1 month');
            $updated_at = fake()->dateTimeBetween($created_at, 'now');

            $return_order = ReturnOrder::factory()->create([
                'product_order_id' => $product_order->id,
                'created_at' => $created_at,
                'updated_at' => $updated_at
            ]);

            $return_order->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('return_order_images');

            $return_order->load('status.parent_status');

            if ($return_order->status->parent_status?->name == 'Dispute In Progress') {
                $dispute = ReturnOrderDispute::factory()->create([
                    'return_order_id' => $return_order->id,
                    'created_at' => $updated_at,
                    'updated_at' => $updated_at
                ]);

                $dispute->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('dispute_images');

                if ($return_order->status->name == 'Pending Resolution') {
                    $dispute_response = ReturnOrderDisputeResponse::factory()->create([
                        'return_order_dispute_id' => $dispute->id,
                        'created_at' => fake()->dateTimeBetween($updated_at, 'now'),
                        'updated_at' => fake()->dateTimeBetween($updated_at, 'now')
                    ]);

                    $dispute_response->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('dispute_response_images');
                }
            }

            if ($return_order->status->parent_status?->name == 'Resolved' && fake()->boolean(70)) {
                $dispute = ReturnOrderDispute::factory()->create([
                    'return_order_id' => $return_order->id,
                    'created_at' => $updated_at,
                    'updated_at' => $updated_at
                ]);

                $dispute->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('dispute_images');

                $dispute_response = ReturnOrderDisputeResponse::factory()->create([
                    'return_order_dispute_id' => $dispute->id,
                    'created_at' => fake()->dateTimeBetween($updated_at, 'now'),
                    'updated_at' => fake()->dateTimeBetween($updated_at, 'now')
                ]);

                $dispute_response->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('dispute_response_images');
            }
        }

        // $product_orders = ProductOrder::whereHasMorph('buyer', [Merchant::class], function ($q) {
        //     $q->where('id', 1);
        // })->get();

        // foreach ($product_orders as $product_order) {
        //     $return_order = ReturnOrder::factory()->create(['product_order_id' => $product_order->id]);
        // }
    }
}
