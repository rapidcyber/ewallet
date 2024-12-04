<?php

namespace Database\Seeders\Static\Products\Orders;

use App\Models\OrderCancelReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderCancelReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons_per_entity = [
            'buyer' => [
                'Change / Combine Order',
                'Delivery Time Too Long',
                'Duplicate Order',
                'Sourcing Payment Issue',
                'Change of Mind',
                'Decided for Alternative Product',
                'Fees / Shipping Costs',
            ],
            'seller' => [
                'Out of Stock',
                'Incorrect Pricing',
                'Sourcing Delay'
            ],
            'admin' => [

            ]
        ];

        foreach ($reasons_per_entity as $entity => $reasons) {
            foreach ($reasons as $reason) {
                OrderCancelReason::firstOrCreate([
                    'name' => $reason,
                    'slug' => str($reason)->slug('_'),
                    'entity' => $entity
                ]);
            }
        }
    }
}
