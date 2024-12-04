<?php

namespace Database\Seeders\Static\Services;

use App\Models\BookingStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $booking_statuses = [
            'Inquiry',
            'Booked',
            'In Progress',
            'Fulfilled',
            'Cancelled',
            'Declined',
            'Quoted'
        ];

        foreach($booking_statuses as $status) {
            BookingStatus::firstOrCreate([
                'name' => $status
            ], [
                'slug' => str($status)->slug('_'),
            ]);
        }
    }
}
