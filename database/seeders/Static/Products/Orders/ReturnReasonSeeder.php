<?php

namespace Database\Seeders\Static\Products\Orders;

use App\Models\ReturnReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReturnReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {        
        $reasons = [
            'Item is Expired / Damaged / Defective',
            'Received Wrong Item',
            'Item is Not as Advertised',
            'Item is a Counterfeit',
            'Item does not suit me',
            'Mutual Agreement with Merchant',
            'Missing Accessories / Freebies',
        ];

        foreach ($reasons as $reason) {
            ReturnReason::firstOrCreate([
                'name' => $reason,
                'slug' => str($reason)->slug('_') 
            ]);
        }
    }
}
