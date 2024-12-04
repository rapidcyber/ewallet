<?php

namespace Database\Seeders\Static\Transactions;

use App\Models\TransactionStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Pending',
            'Successful',
            'Failed',
            'Refunded'
        ];

        foreach ($statuses as $status) {
            TransactionStatus::firstOrCreate([
                'name' => $status
            ], [
                'slug' => str($status)->slug('_'),
            ]);
        }
    }
}
