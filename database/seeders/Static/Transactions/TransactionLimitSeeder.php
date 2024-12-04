<?php

namespace Database\Seeders\Static\Transactions;

use App\Models\Role;
use App\Models\TransactionLimit;
use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionLimitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unverified_user = Role::where('slug', str('User')->slug())->first();
        $verified_user = Role::where('slug', str('Verified User')->slug('_'))->first();
        $merchant = Role::where('slug', str('Merchant')->slug())->first();

        $transaction_types = TransactionType::all();
        
        foreach($transaction_types as $transaction_type) {
            if (in_array($transaction_type->slug, ['cash_in', 'cash_out', 'transfer'])) {
                $unverified_limit = TransactionLimit::firstOrCreate([
                    'transaction_type_id' => $transaction_type->id,
                    'role_id' => $unverified_user->id,
                    'scope' => 'monthly'
                ], [
                    'amount' => 5000,
                ]);
        
                $verified_limit = TransactionLimit::firstOrCreate([
                    'transaction_type_id' => $transaction_type->id,
                    'role_id' => $verified_user->id,
                    'scope' => 'daily'
                ], [
                    'amount' => 50000,
                ]);
        
                $merchant_limit = TransactionLimit::firstOrCreate([
                    'transaction_type_id' => $transaction_type->id,
                    'role_id' => $merchant->id,
                    'scope' => 'daily'
                ], [ 
                    'amount' => 500000,
                ]);

                continue;
            }

            
        }
    }
}
