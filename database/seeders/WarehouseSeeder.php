<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Merchant;
use App\Models\Warehouse;
use App\Models\WarehouseAvailability;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Merchant::all() as $merchant) {
            $warehouse = Warehouse::factory()->create([
                'merchant_id' => $merchant->id,
            ]);

            Location::factory()->create([
                'entity_id' => $warehouse->id,
                'entity_type' => Warehouse::class,
            ]);

            $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

            foreach($daysOfWeek as $day) {
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
