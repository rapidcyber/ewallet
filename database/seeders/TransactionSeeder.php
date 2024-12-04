<?php

namespace Database\Seeders;

use App\Models\Balance;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $transaction_providers = TransactionProvider::inRandomOrder()->get();
        $transaction_channels = TransactionChannel::inRandomOrder()->get();
        $success = TransactionStatus::where('slug', 'successful')->first()->id;

        $cash_in_date = fake()->dateTimeBetween('-1 year', '-2 months');
        foreach ($users as $user) {
            $cash_in_provider = $transaction_providers->random();
            $cash_in_channel = $transaction_channels->random();
            $cash_in_type = TransactionType::where('slug', 'cash_in')->first();

            $cash_in = Transaction::factory()->create([
                'sender_id' => $cash_in_provider->id,
                'sender_type' => TransactionProvider::class,
                'recipient_id' => $user->id,
                'recipient_type' => User::class,
                'ref_no' => $cash_in_provider->code . $cash_in_channel->code . $cash_in_type->code . $cash_in_date->format('ymdHis'),
                'transaction_provider_id' => $cash_in_provider->id,
                'transaction_channel_id' => $cash_in_channel->id,
                'transaction_type_id' => $cash_in_type->id,
                'transaction_status_id' => $success,
                'amount' => 20000,
                'created_at' => $cash_in_date,
                'updated_at' => $cash_in_date
            ]);

            $balance = Balance::create([
                'entity_id' => $user->id,
                'entity_type' => User::class,
                'transaction_id' => $cash_in->id,
                'amount' => 20000,
                'created_at' => $cash_in_date,
                'updated_at' => $cash_in_date
            ]);

            $date = Carbon::parse(fake()->dateTimeBetween($cash_in_date, '-30 days'));

            for($count = 1; $count <= 30; $count++) {
                // OUTGOING TRANSFERS
                $recipient_type = fake()->randomElement(['user', 'merchant']);
                $recipient = $recipient_type == 'user' ? User::inRandomOrder()->first() : Merchant::inRandomOrder()->first();

                $outgoing_provider = $transaction_providers->random();
                $outgoing_channel = $transaction_channels->random();
                $outgoing_type = TransactionType::where('slug', 'transfer')->first();

                $outgoing_date = $date->copy()->addDays($count);

                $old_balance = $user->latest_balance()->first();
                
                if ($old_balance->amount < 20) {
                    continue;
                }

                do {
                    $amount = fake()->numberBetween(20, 500);                    
                } while ($old_balance->amount < $amount);

                $outgoing = Transaction::factory()->create([
                    'sender_id' => $user->id,
                    'sender_type' => User::class,
                    'recipient_id' => $recipient->id,
                    'recipient_type' => $recipient_type == 'user' ? User::class : Merchant::class,
                    'ref_no' => $outgoing_provider->code . $outgoing_channel->code . $outgoing_type->code . $outgoing_date->format('ymdHis'),
                    'amount' => $amount,
                    'transaction_provider_id' => $outgoing_provider->id,
                    'transaction_channel_id' => $outgoing_channel->id,
                    'transaction_type_id' => $outgoing_type->id,
                    'transaction_status_id' => $success,
                    'created_at' => $outgoing_date,
                    'updated_at' => $outgoing_date
                ]);

                $user->balances()->create([
                    'amount' => $old_balance->amount - $outgoing->amount,
                    'transaction_id' => $outgoing->id,
                    'created_at' => $outgoing_date,
                    'updated_at' => $outgoing_date
                ]);

                $recipient_balance = $recipient->latest_balance()->first();

                $recipient->balances()->create([
                    'amount' => ($recipient_balance->amount ?? 0) + $outgoing->amount,
                    'transaction_id' => $outgoing->id,
                    'created_at' => $outgoing_date,
                    'updated_at' => $outgoing_date
                ]);


                $sender_type = fake()->randomElement(['user', 'merchant']);
                $sender = $sender_type == 'user' ? User::inRandomOrder()->first() : Merchant::inRandomOrder()->first();

                $incoming_provider = $transaction_providers->random();
                $incoming_channel = $transaction_channels->random();
                $incoming_type = TransactionType::where('slug', 'transfer')->first();

                $incoming_date = $date->copy()->addDays($count)->addHours(6);

                $recipient_balance = $recipient->latest_balance()->first();
                if ($recipient_balance->amount < 20) {
                    continue;
                }
                do {
                    $amount = fake()->numberBetween(20, 500);                    
                } while ($recipient_balance->amount < $amount);

                $incoming = Transaction::factory()->create([
                    'sender_id' => $sender->id,
                    'sender_type' => $sender_type == 'user' ? User::class : Merchant::class,
                    'recipient_id' => $user->id,
                    'recipient_type' => User::class,  
                    'ref_no' => $incoming_provider->code . $incoming_channel->code . $incoming_type->code . $incoming_date->format('ymdHis'),
                    'amount' => $amount,
                    'transaction_provider_id' => $incoming_provider->id,
                    'transaction_channel_id' => $incoming_channel->id,
                    'transaction_type_id' => $incoming_type->id,
                    'transaction_status_id' => $success,
                    'created_at' => $incoming_date,
                    'updated_at' => $incoming_date
                ]);

                $old_balance = $user->latest_balance()->first();

                $user->balances()->create([
                    'amount' => ($old_balance->amount ?? 0) + $incoming->amount,
                    'transaction_id' => $incoming->id,
                    'created_at' => $incoming_date,
                    'updated_at' => $incoming_date
                ]);

                $recipient->balances()->create([
                    'amount' => ($recipient_balance->amount ?? 0) - $incoming->amount,
                    'transaction_id' => $incoming->id,
                    'created_at' => $incoming_date,
                    'updated_at' => $incoming_date
                ]);
            }
        }


        $merchants = Merchant::all();
        $transaction_providers = TransactionProvider::inRandomOrder()->get();
        $transaction_channels = TransactionChannel::inRandomOrder()->get();
        $success = TransactionStatus::where('slug', 'successful')->first()->id;

        $cash_in_date = fake()->dateTimeBetween('-1 years', '-2 months');
        foreach ($merchants as $merchant) {
            $cash_in_provider = $transaction_providers->random();
            $cash_in_channel = $transaction_channels->random();
            $cash_in_type = TransactionType::where('slug', 'cash_in')->first();

            $cash_in = Transaction::factory()->create([
                'sender_id' => $cash_in_provider->id,
                'sender_type' => TransactionProvider::class,
                'recipient_id' => $merchant->id,
                'recipient_type' => Merchant::class,
                'ref_no' => $cash_in_provider->code . $cash_in_channel->code . $cash_in_type->code . $cash_in_date->format('ymdHis'),
                'transaction_provider_id' => $cash_in_provider->id,
                'transaction_channel_id' => $cash_in_channel->id,
                'transaction_type_id' => $cash_in_type->id,
                'transaction_status_id' => $success,
                'amount' => 50000,
                'created_at' => $cash_in_date,
                'updated_at' => $cash_in_date
            ]);

            $balance = Balance::create([
                'entity_id' => $merchant->id,
                'entity_type' => Merchant::class,
                'transaction_id' => $cash_in->id,
                'amount' => 50000,
                'created_at' => $cash_in_date,
                'updated_at' => $cash_in_date
            ]);
            
            $date = Carbon::parse(fake()->dateTimeBetween($cash_in_date, 'now'));

            for($count = 0; $count < 30; $count++) {
                // OUTGOING TRANSFERS
                $recipient_type = fake()->randomElement(['user', 'merchant']);
                $recipient = $recipient_type == 'user' ? User::inRandomOrder()->first() : Merchant::inRandomOrder()->first();

                $outgoing_provider = $transaction_providers->random();
                $outgoing_channel = $transaction_channels->random();
                $outgoing_type = TransactionType::where('slug', 'transfer')->first();

                $outgoing_date = $date->copy()->addDays($count);

                $recipient_balance = $recipient->latest_balance()->first();

                if ($recipient_balance->amount < 20) {
                    continue;
                }
                do {
                    $amount = fake()->numberBetween(20, 500);                    
                } while ($recipient_balance->amount < $amount);

                $outgoing = Transaction::factory()->create([
                    'sender_id' => $merchant->id,
                    'sender_type' => Merchant::class,
                    'recipient_id' => $recipient->id,
                    'recipient_type' => $recipient_type == 'user' ? User::class : Merchant::class,  
                    'ref_no' => $outgoing_provider->code . $outgoing_channel->code . $outgoing_type->code . $outgoing_date->format('ymdHis'),
                    'amount' => $amount,
                    'transaction_provider_id' => $outgoing_provider->id,
                    'transaction_channel_id' => $outgoing_channel->id,
                    'transaction_type_id' => $outgoing_type->id,
                    'transaction_status_id' => $success,
                    'created_at' => $outgoing_date,
                    'updated_at' => $outgoing_date
                ]);

                $old_balance = $merchant->balances()->latest()->first();

                $new_balance = $merchant->balances()->create([
                    'amount' => ($old_balance->amount ?? 0) + $outgoing->amount,
                    'transaction_id' => $outgoing->id,
                    'created_at' => $outgoing_date,
                    'updated_at' => $outgoing_date
                ]);

                $recipient->balances()->create([
                    'amount' => ($recipient_balance->amount ?? 0) - $outgoing->amount,
                    'transaction_id' => $outgoing->id,
                    'created_at' => $outgoing_date,
                    'updated_at' => $outgoing_date
                ]);

                $sender_type = fake()->randomElement(['user', 'merchant']);
                $sender = $sender_type == 'user' ? User::inRandomOrder()->first() : Merchant::inRandomOrder()->first();

                $incoming_provider = $transaction_providers->random();
                $incoming_channel = $transaction_channels->random();
                $incoming_type = TransactionType::where('slug', 'transfer')->first();

                $incoming_date = $date->copy()->addDays($count)->addHours(6);

                $sender_balance = $sender->latest_balance()->first();

                if ($sender_balance->amount < 20) {
                    continue;
                }
                do {
                    $amount = fake()->numberBetween(20, 500);                    
                } while ($sender_balance->amount < $amount);

                $incoming = Transaction::factory()->create([
                    'sender_id' => $sender->id,
                    'sender_type' => $sender_type == 'user' ? User::class : Merchant::class,
                    'recipient_id' => $merchant->id,
                    'recipient_type' => Merchant::class,  
                    'ref_no' => $incoming_provider->code . $incoming_channel->code . $incoming_type->code . $incoming_date->format('ymdHis'),
                    'amount' => $amount,
                    'transaction_provider_id' => $incoming_provider->id,
                    'transaction_channel_id' => $incoming_channel->id,
                    'transaction_type_id' => $incoming_type->id,
                    'transaction_status_id' => $success,
                    'created_at' => $incoming_date,
                    'updated_at' => $incoming_date
                ]);

                $old_balance = $merchant->balances()->latest()->first();

                $merchant->balances()->create([
                    'amount' => ($old_balance->amount ?? 0) + $incoming->amount,
                    'transaction_id' => $incoming->id,
                    'created_at' => $incoming_date,
                    'updated_at' => $incoming_date
                ]);

                $sender->balances()->create([
                    'amount' => ($sender_balance->amount ?? 0) - $incoming->amount,
                    'transaction_id' => $incoming->id,
                    'created_at' => $incoming_date,
                    'updated_at' => $incoming_date
                ]);
            }


        }
    }
}
