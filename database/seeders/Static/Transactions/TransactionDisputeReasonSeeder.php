<?php

namespace Database\Seeders\Static\Transactions;

use App\Models\TransactionDisputeReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionDisputeReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            'Unauthorized Transaction',
            'Duplicate Charging',
            'Incorrect Amount',
            'Paid by Other Means',
            'Refund/Credit Not Processed',
            'Cancelled Recurring Transactions',
            'Defective Goods',
            'Goods/Services Not Received',
            'Declined but Charged/Debited'
        ];

        foreach ($reasons as $reason) {
            TransactionDisputeReason::firstOrCreate([
                'name' => $reason
            ], [
                'slug' => str($reason)->slug('_')
            ]);
        }
    }
}
