<?php

namespace Database\Seeders\Static\Products\Orders;

use App\Models\ReturnCancelReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReturnCancelReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            'Mutual Agreement between parties',
            'Lack of evidence',
            'Dispute policy violation',
            'Resolved outside the platform',
            'Abusive dispute',
            'Dispute is irrelevant',
            'Violation of the terms and conditions',
            'Uncooperative dispute party/parties'
        ];

        foreach ($reasons as $reason) {
            ReturnCancelReason::firstOrCreate([
                'name' => $reason,
                'slug' => str($reason)->slug('_')
            ]);
        }
    }
}
