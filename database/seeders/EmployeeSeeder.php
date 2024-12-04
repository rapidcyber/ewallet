<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Merchant;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $merchants = Merchant::all();
        
        foreach ($merchants as $merchant) {
            $users = User::factory(3)->has(Profile::factory())->create();

            foreach ($users as $user) {
                $user->roles()->attach(Role::where('slug', str('Verified User')->slug('_'))->first()->id);

                $employee = Employee::factory()->create([
                    'user_id' => $user->id,
                    'merchant_id' => $merchant->id
                ]);
            }
        }
    }
}
