<?php

namespace Database\Seeders\Static;

use App\Models\BalanceLimit;
use App\Models\Role;
use Illuminate\Database\Seeder;



class BalanceLimitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $unverified_user = Role::where('slug', str('User')->slug())->first();
        BalanceLimit::firstOrCreate([
            'role_id' => $unverified_user->id,
        ], [
            'amount' => 50000
        ]);

        $verified_user = Role::where('slug', str('Verified User')->slug('_'))->first();
        BalanceLimit::firstOrCreate([
            'role_id' => $verified_user->id,
        ], [
            'amount' => 100000
        ]);

        $merchant = Role::where('slug', str('Merchant')->slug())->first();
        BalanceLimit::firstOrCreate([
            'role_id' => $merchant->id,
        ], [
            'amount' => 500000
        ]);
    }
}
