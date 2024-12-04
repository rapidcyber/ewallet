<?php

namespace Database\Seeders;

use App\Models\ShippingOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShippingOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            [
                'name' => 'Lalamove',
                'slug' => 'lalamove',
                'description' => 'Lalamove Delivery Service',
                'shipping_days' => 0,
                'price' => 0,
            ],
        ];

        foreach ($options as $option) {
            ShippingOption::firstOrCreate($option);
        }
    }
}
