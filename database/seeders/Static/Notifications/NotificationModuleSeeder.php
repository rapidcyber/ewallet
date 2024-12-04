<?php

namespace Database\Seeders\Static\Notifications;

use App\Models\NotificationModule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'Notification' => false,
            'Transaction' => true,
            'Invoice' => true,
            'Expired' => false,
            'Affiliation' => true,
            'Bill' => true,
            'Order' => true,
            'Booking' => true,
            'Inquiry' => true
        ];

        foreach ($modules as $module => $needs_action) {
            NotificationModule::firstOrCreate([
                'name' => $module,
                'slug' => str($module)->slug('_'),
                'needs_action' => $needs_action
            ]);
        }
    }
}
