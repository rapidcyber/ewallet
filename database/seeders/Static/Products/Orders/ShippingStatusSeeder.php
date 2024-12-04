<?php

namespace Database\Seeders\Static\Products\Orders;

use App\Models\ShippingStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShippingStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shipping_statuses = [
            'Unpaid' => [],
            'To Ship' => [
                'Pending',
                'Packed',
                'Ready to Ship',
            ],
            'Shipping' => [],
            'Completed' => [],
            'Cancellation' => [],
            'Failed Delivery' => []
        ];

        foreach ($shipping_statuses as $shipping_status => $sub_shipping_status) {
            $parent = ShippingStatus::firstOrCreate([
                'name' => $shipping_status,
            ],[
                'slug' => str($shipping_status)->slug('_')
            ]);

            foreach ($sub_shipping_status as $status) {
                ShippingStatus::firstOrCreate([
                    'name' => $status,
                ], [
                    'parent' => $parent->id,
                    'slug' => str($status)->slug('_')
                ]);
            }
        }
    }
}
