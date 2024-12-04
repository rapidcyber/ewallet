<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Location;
use App\Models\Merchant;
use App\Models\MerchantCategory;
use App\Models\MerchantDetail;
use App\Models\Profile;
use App\Models\Role;
use App\Models\SalaryType;
use App\Models\User;
use App\Models\Warehouse;
use App\Traits\WithNumberGeneration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SystemAdminSeeder extends Seeder
{

    use WithNumberGeneration;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sys_admin = User::where('email', 'admin@repay.ph')->first();
        if (empty($sys_admin) == true) {
            $sys_admin = User::create([
                'app_id' => Str::orderedUuid(),
                'username' => 'Admin',
                'email' => 'admin@repay.ph',
                'password' => Hash::make('R3p@yx24'),
                'phone_iso' => 'PH',
                'phone_number' => '639163072918',
                'pin' => Hash::make('0374'),
                'apply_for_realholmes' => null,
            ]);

            $profile = Profile::create([
                'user_id' => $sys_admin->id,
                'first_name' => 'Repay',
                'surname' => 'Admin',
            ]);

            $profile->status = 'verified';
            $profile->save();

            $sys_admin->roles()->syncWithoutDetaching(Role::where('slug', str('Administrator')->slug())->first()->id);
            $sys_admin->roles()->syncWithoutDetaching(Role::where('slug', str('Verified User')->slug('_'))->first()->id);
        }

        if (Merchant::where('name', 'Repay Digital Banking Solutions Inc.')->exists() == false) {
            $sys_merchant = Merchant::create([
                'app_id' => Str::orderedUuid(),
                'user_id' => $sys_admin->id,
                'account_number' => $this->generate_merchant_account_number($sys_admin),
                'name' => 'Repay Digital Banking Solutions Inc.',
                'merchant_category_id' => MerchantCategory::where('name', 'Legal and Financial')->first()->id,
                'email' => 'solutions@repay.ph',
                'phone_iso' => 'PH',
                'phone_number' => '639209058875',
                'invoice_prefix' => 'REPAY',
                'status' => 'verified',
            ]);
            $sys_merchant->roles()->syncWithoutDetaching(Role::where('slug', str('Merchant')->slug('_'))->first()->id);

            Employee::create([
                'merchant_id' => $sys_merchant->id,
                'user_id' => $sys_admin->id,
                'employee_role_id' => EmployeeRole::firstWhere('slug', 'owner')->id,
                'occupation' => 'CEO',
                'salary' => 100000,
                'salary_type_id' => SalaryType::firstWhere('slug', 'per_cutoff')->id,
            ]);

            MerchantDetail::factory()->create([
                'merchant_id' => $sys_merchant->id,
            ]);

            $warehouse = Warehouse::create([
                'merchant_id' => $sys_merchant->id,
                'name' => 'Main Office',
                'phone_number' => '639209058875',
                'email' => 'solutions@repay.ph',
            ]);
            $warehouse->save();

            $location = Location::create([
                'entity_type' => get_class($warehouse),
                'entity_id' => $warehouse->id,
                'address' => 'Unit 804, CTP Alpha Tower Investment Drv., Madrigal Business Park, Brgy. Ayala Alabang, Muntinlupa City National Capital Region (NCR), 1781',
                'latitude' => 14.4256304,
                'longitude' => 121.0228616,
            ]);
            $location->save();
        }
    }
}
