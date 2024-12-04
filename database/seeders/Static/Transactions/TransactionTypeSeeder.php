<?php

namespace Database\Seeders\Static\Transactions;

use App\Models\TransactionType;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Cash-in',
                'code' => 'CI'
            ],
            [
                'name' => 'Cash-out',
                'code' => 'CO'
            ],
            [
                'name' => 'Transfer',
                'code' => 'TR'
            ],
            [
                'name' => 'Bill Payment',
                'code' => 'BP'
            ],
            [
                'name' => 'Invoice Payment',
                'code' => 'IV'
            ],
            [
                'name' => 'Order Payment',
                'code' => 'OR'
            ],
            [
                'name' => 'Payroll Salary',
                'code' => 'PS'
            ],
            [
                'name' => 'Payroll Gov',
                'code' => 'PG'
            ],
            [
                'name' => 'RH Sub',
                'code' => 'RH'
            ],
            [
                'name' => 'Disbursement',
                'code' => 'DS'
            ],
            [
                'name' => 'Refund',
                'code' => 'RF'
            ],
        ];


        foreach ($types as $type) {
            TransactionType::firstOrCreate([
                'name' => $type['name'],
                'slug' => str($type['name'])->slug('_'),
                'code' => $type['code'],
            ]);
        }
    }
}
