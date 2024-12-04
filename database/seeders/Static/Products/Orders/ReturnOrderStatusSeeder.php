<?php

namespace Database\Seeders\Static\Products\Orders;

use App\Models\ReturnOrderStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReturnOrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Return Initiated' => [],
            'Return In Progress' => [
                'Pending Return'
            ],
            'Rejected' => [
                'Pending Resolution',
            ],
            'Resolved' => [
                'Refunded Only',
                'Returned Only',
                'Returned and Refunded',
                'Return Cancelled'
            ],
            'Dispute In Progress' => [
                'Pending Response',
                'Pending Resolution'
            ],
        ];

        foreach ($statuses as $parent => $status) {
            $parent_status = ReturnOrderStatus::firstOrCreate([
                'parent' => null,
                'name' => $parent
            ], [
                'slug' => str($parent)->slug('_')
            ]);

            foreach ($status as $sub_status) {
                ReturnOrderStatus::firstOrCreate([
                    'parent' => $parent_status->id,
                    'name' => $sub_status
                ], [
                    'slug' => str($sub_status)->slug('_')
                ]);
            }
        }
    }
}
