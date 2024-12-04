<?php

namespace Database\Seeders\Static\Products\Orders;

use App\Models\ReturnRejectionReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReturnRejectionReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            'Non-Compliance with Return Policy',
            'Damaged or Used Product',
            'Fraudulent Claims',
            'Product Tampering',
            'Lack of Proof',
            'Item is customized or personalized',
        ];

        foreach ($reasons as $reason) {
            ReturnRejectionReason::firstOrCreate([
                'name' => $reason,
                'slug' => str($reason)->slug('_')
            ]);
        }
    }
}
