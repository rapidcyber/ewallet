<?php

namespace Database\Seeders\Static;

use App\Models\SystemService;
use Illuminate\Database\Seeder;

class SystemServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $system_services = [
            [
                'name' => "System",
                'slug' => "system",
                'availability' => 'active',
            ],
            [
                'name' => 'Bills Management',
                'slug' => 'bills_management',
                'availability' => 'soon',
            ],
            [
                'name' => 'QR Generation',
                'slug' => 'qr_generation',
                'availability' => 'active',
            ],
            [
                'name' => 'Cash Disbursement',
                'slug' => 'cash_disbursement',
                'availability' => 'active',
            ],
            [
                'name' => 'Telco Load',
                'slug' => 'telco_load',
                'availability' => 'soon',
            ],
            [
                'name' => 'Virtual Card',
                'slug' => 'virtual_card',
                'availability' => 'soon',
            ],
            [
                'name' => 'Bank Account Linking',
                'slug' => 'bank_account_linking',
                'availability' => 'soon',
            ],
        ];

        foreach ($system_services as $service) {
            $exists = SystemService::where('slug', $service['slug'])->exists();

            if ($exists) {
                continue;
            }

            $s = new SystemService;
            $s->fill($service);
            $s->save();
        }
    }
}
