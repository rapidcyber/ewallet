<?php

namespace Database\Seeders\Static\Products\Orders;

use App\Models\PaymentOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payment_options = [
            'COD',
            'RePay'

        ];

        foreach ($payment_options as $payment_option) {
            PaymentOption::firstOrCreate([
                'name' => $payment_option,
                'slug' => str($payment_option)->slug('_')
            ]);
        }
    }
}
