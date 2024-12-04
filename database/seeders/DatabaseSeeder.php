<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // RUN IN ALL ENVIRONMENTS
        $this->call(StaticSeeder::class);

        // RUN TO GENERATE SUPER USER
        $this->call(SystemAdminSeeder::class);

        // RUN IN LOCAL ENVIRONMENT FOR TESTING
        if (app()->environment('local')) {
            $this->call([
                TestSeeder::class,

                UserSeeder::class,

                MerchantSeeder::class,

                EmployeeSeeder::class,

                InquirySeeder::class,

                TransactionSeeder::class,

                // BillerSeeder::class,
                // BillSeeder::class,

                ProductSeeder::class,
                ProductOrderSeeder::class,
                ProductOrderLogSeeder::class,

                ServiceSeeder::class,
                BookingSeeder::class,
                
                EntityReviewSeeder::class,

                TransactionDisputeSeeder::class,
                
                ReturnOrderSeeder::class,
                ReturnOrderLogSeeder::class,
            ]);
        }
    }
}
