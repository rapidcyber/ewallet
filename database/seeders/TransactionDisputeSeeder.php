<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\TransactionDispute;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionDisputeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('id', 1)->get();

        foreach ($users as $user) {
            $transactions = $user->outgoing_transactions()
                ->where('created_at', '<', now())
                ->inRandomOrder()
                ->limit(fake()->numberBetween(5,5))
                ->get();

            foreach ($transactions as $transaction) {
                if ($transaction->disputes()->count() > 0) {
                    continue;
                }

                $date = fake()->dateTimeBetween($transaction->created_at, 'now');

                $dispute = TransactionDispute::factory()->create([
                    'transaction_id' => $transaction->id,
                    'created_at' => $date,
                    'updated_at' => $date
                ]);

                $dispute->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('dispute_images');
            }
        }

        $merchants = Merchant::all();

        foreach ($merchants as $merchant) {
            $transactions = $merchant->outgoing_transactions()
                ->where('created_at', '<', now())
                ->inRandomOrder()
                ->limit(fake()->numberBetween(1, 2))
                ->get();

            foreach ($transactions as $transaction) {
                if ($transaction->disputes()->count() > 0) {
                    continue;
                }

                $date = fake()->dateTimeBetween($transaction->created_at, 'now');

                $dispute = TransactionDispute::factory()->create([
                    'transaction_id' => $transaction->id,
                    'created_at' => $date,
                    'updated_at' => $date
                ]);

                $dispute->addMediaFromUrl('https://i.imgur.com/S9dsMBX.jpeg')->toMediaCollection('dispute_images');
            }
        }
    }
}
