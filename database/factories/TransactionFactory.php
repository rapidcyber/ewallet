<?php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sender = fake()->randomElement(['merchant', 'user']);
        $recipient = fake()->randomElement(['merchant', 'user']);
        $amount = fake()->numberBetween(20, 500);

        return [
            'sender_type' => $sender,
            'sender_id' => $sender == 'merchant' ? Merchant::factory() : User::factory(),
            'recipient_type' => $recipient,
            'recipient_id' => $recipient == 'merchant' ? Merchant::factory() : User::factory(),
            'txn_no' => fake()->unique()->numerify('############'),
            'ref_no' => fake()->unique()->uuid(),
            'currency' => 'PHP',
            'amount' => $amount,
            'service_fee' => 0,
            'transaction_provider_id' => TransactionProvider::inRandomOrder()->first()->id,
            'transaction_channel_id' => TransactionChannel::inRandomOrder()->first()->id,
            'transaction_type_id' => TransactionType::inRandomOrder()->first()->id,
            'transaction_status_id' => TransactionStatus::inRandomOrder()->first()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
