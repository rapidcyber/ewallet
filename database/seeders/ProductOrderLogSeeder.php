<?php

namespace Database\Seeders;

use App\Models\ProductOrder;
use App\Models\ProductOrderLog;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductOrderLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product_orders = ProductOrder::with('shipping_status')->get();

        foreach ($product_orders as $product_order) {
            for ($count = 1; $count <= $product_order->shipping_status_id; $count++) {
                $log = new ProductOrderLog;
                $log->product_order_id = $product_order->id;
                $log->shipping_status_id = $product_order->shipping_status_id;
                $log->title = fake()->sentence();
                $log->description = fake()->sentence();
                $log->created_at = Carbon::parse($product_order->created_at)->addDays($count);
                $log->updated_at = Carbon::parse($product_order->created_at)->addDays($count);
                $log->save();
            }
        }
    }
}
