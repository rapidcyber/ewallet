<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Location;
use App\Models\Merchant;
use App\Models\MerchantDetail;
use App\Models\Role;
use App\Models\ShippingOption;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseAvailability;
use App\Traits\WithNumberGeneration;
use Illuminate\Database\Seeder;

class MerchantSeeder extends Seeder
{
    use WithNumberGeneration;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::active()->where('id', '!=', 1)->limit(2)->get();

        foreach ($users as $user) {
            if ($user->owned_merchants()->count() >= 5) {
                continue;
            }

            $merchant = Merchant::factory()->create([
                'user_id' => $user->id,
                'account_number' => $this->generate_merchant_account_number($user),
            ]);

            $merchant->status = 'verified';
            $merchant->save();
            $merchant->roles()->syncWithoutDetaching(Role::where('slug', str('Merchant')->slug('_'))->first()->id);

            $merchant->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('merchant_logo');
            $merchant->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('merchant_banner');

            $merchant->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('dti_sec');
            $merchant->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('bir_cor');

            MerchantDetail::factory()->create([
                'merchant_id' => $merchant->id,
            ]);

            Employee::factory()->create([
                'merchant_id' => $merchant->id,
                'user_id' => $user->id,
                'employee_role_id' => EmployeeRole::where('slug', str('Owner')->slug())->first()->id,
                'occupation' => 'Owner',
                'salary' => 0,
                'salary_type_id' => 1,
            ]);

            $shipping_options = ShippingOption::inRandomOrder()->limit(rand(1, 3))->get();

            $merchant->shipping_options()->sync($shipping_options);

            $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            $warehouses = Warehouse::factory(rand(1, 3))->create([
                'merchant_id' => $merchant->id,
            ]);

            foreach ($warehouses as $warehouse) {
                Location::factory()->create([
                    'entity_id' => $warehouse->id,
                    'entity_type' => Warehouse::class,
                ]);

                foreach ($daysOfWeek as $day) {
                    $proceed = fake()->boolean(60);
                    if (!$proceed) {
                        continue;
                    }

                    WarehouseAvailability::factory()->create([
                        'warehouse_id' => $warehouse->id,
                        'day_name' => $day,
                    ]);
                }
            }
        }
    }
}
