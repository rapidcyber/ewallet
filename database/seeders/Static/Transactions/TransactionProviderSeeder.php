<?php

namespace Database\Seeders\Static\Transactions;

use App\Models\TransactionProvider;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'name' => 'Repay',
                'code' => 'RPY'
            ],
            [
                'name' => 'Unionbank',
                'code' => 'UBN'
            ],
            [
                'name' => 'AllBank',
                'code' => 'ALB'
            ],
            [
                'name' => 'ECPay',
                'code' => 'ECP'
            ],
        ];

        foreach ($providers as $provider) {
            TransactionProvider::firstOrCreate([
                'name' => $provider['name']
            ], [
                'slug' => str($provider['name'])->slug('_'),
                'code' => $provider['code']
            ]);
        }
    }
}
