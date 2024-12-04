<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ShippingStatus;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Traits\WithNumberGeneration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProductOrderSeeder extends Seeder
{
    use WithNumberGeneration;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::whereHas('merchant', function ($query) {
            $query->where('merchants.id', 1);
            $query->whereHas('shipping_options');
            $query->whereHas('warehouses');
        })->with('merchant')->get();

        foreach ($products as $product) {
            $product->approval_status = 'approved';
            $product->is_active = true;
            $product->save();
        }
        
        $type = TransactionType::where('code', 'OR')->first();
        $provider = TransactionProvider::where('code', 'RPY')->first();
        $channel = TransactionChannel::where('code', 'RPY')->first();
        $successful_status = TransactionStatus::where('slug', 'successful')->first();

        foreach ($products as $product) {

            if ($product->stock_count == 0) {
                continue;
            }

            $quantity = fake()->numberBetween(1, 3);

            $product_orders = ProductOrder::factory(10)->create([
                'product_id' => $product->id,
                'amount' => $product->price,
                'quantity' => $quantity,
                'warehouse_id' => $product->merchant->warehouses()->inRandomOrder()->first()->id,
                'shipping_option_id' => $product->merchant->shipping_options()->inRandomOrder()->first()->id,
            ])->each(function ($product_order) use ($type, $provider, $channel, $successful_status, $product) {
                $product_order->transaction()->create([
                    'sender_id' => $product_order->product->merchant->id,
                    'sender_type' => get_class($product_order->product->merchant),
                    'recipient_id' => $product_order->buyer_id,
                    'recipient_type' => $product_order->buyer_type,
                    'txn_no' => $this->generate_transaction_number(),
                    'transaction_provider_id' => $provider->id,
                    'transaction_channel_id' => $channel->id,
                    'transaction_type_id' => $type->id,
                    'transaction_status_id' => $successful_status->id,
                    'currency' => 'PHP',
                    'service_fee' => $product_order->shipping_fee,
                    'amount' => $product_order->amount * $product_order->quantity,
                ]);
            });

            foreach ($product_orders as $product_order) {
                Location::factory()->create([
                    'entity_id' => $product_order->id,
                    'entity_type' => ProductOrder::class,
                ]);
            }

            $product->stock_count--;

            $product->save();
        }
    }
}
