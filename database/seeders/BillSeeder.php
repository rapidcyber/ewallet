<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Biller;
use App\Models\BillProfile;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::inRandomOrder()->get();
        $billers = Biller::all();

        foreach ($users as $user) {
            for ($count = 0; $count < fake()->numberBetween(3, 5); $count++) {
                $biller = $billers->random();
                $date = fake()->dateTimeBetween('-1 month', '-1 week');

                $data = [
                    'entity_id' => $user->id,
                    'entity_type' => User::class,
                    'biller_code' => $biller->code,
                    'biller_name' => $biller->name,
                    'amount' => fake()->numberBetween(100, 10000),
                    'receipt_email' => fake()->randomElement([null, fake()->safeEmail()]),
                    'remind_date' => fake()->randomElement([null, fake()->randomNumber(2)]),
                    'created_at' => $date,
                    'updated_at' => $date,
                    'due_date' => fake()->date('Y-m-d', '1 year'),
                    'payment_date' => fake()->numberBetween(0, 1) == 1 ? now()->format('Y-m-d') : null,
                ];

                BillProfile::firstOrNew([
                    'entity_id' => $data['entity_id'],
                    'entity_type' => $data['entity_type'],
                    'biller_code' => $data['biller_code'],
                    'biller_name' => $data['biller_name'],
                    'type' => 'presentment'
                ], [
                    'amount' => $data['amount'],
                    'receipt_email' => $data['receipt_email'],
                    'remind_date' => $data['remind_date'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                ]);

                Bill::create([
                    ...$data,
                    'ref_no' => 'BL' . $date->format('ymd') . fake()->unique()->numerify('########'),
                ]);

            }

        }

        $merchants = Merchant::all();

        foreach ($merchants as $merchant) {
            for ($count = 0; $count < fake()->numberBetween(3, 5); $count++) {
                $biller = $billers->random();
                $date = fake()->dateTimeBetween('-1 month', '-1 week');

                $data = [
                    'entity_id' => $merchant->id,
                    'entity_type' => Merchant::class,
                    'biller_code' => $biller->code,
                    'biller_name' => $biller->name,
                    'amount' => fake()->numberBetween(100, 10000),
                    'receipt_email' => fake()->randomElement([null, fake()->safeEmail()]),
                    'remind_date' => fake()->randomElement([null, fake()->randomNumber(2)]),
                    'created_at' => $date,
                    'updated_at' => $date,
                    'due_date' => fake()->date('Y-m-d', '1 year'),
                    'payment_date' => fake()->numberBetween(0, 1) == 1 ? now()->format('Y-m-d') : null,
                ];

                BillProfile::firstOrNew([
                    'entity_id' => $data['entity_id'],
                    'entity_type' => $data['entity_type'],
                    'biller_code' => $data['biller_code'],
                    'biller_name' => $data['biller_name'],
                    'type' => 'presentment'
                ], [
                    'amount' => $data['amount'],
                    'receipt_email' => $data['receipt_email'],
                    'remind_date' => $data['remind_date'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                ]);

                Bill::create([
                    ...$data,
                    'ref_no' => 'BL' . $date->format('ymd') . fake()->unique()->numerify('########'),
                ]);
            }
        }
    }
}
