<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookings = Booking::with('invoice')->has('invoice')->get();

        foreach($bookings as $booking) {
            $invoice = $booking->invoice;
            $invoice->status = 'paid';
            $invoice->save();
            
            if ($invoice->status == 'paid') {
                $invoice_payment = TransactionType::where('code', 'IV')->first()->id;
                $transaction_factory = Transaction::factory()->new();
                $transaction = Transaction::firstOrCreate([
                    'ref_no' => $invoice->invoice_no,
                ], [
                    'sender_id' => $booking->entity_id,
                    'sender_type' => $booking->entity_type,
                    'recipient_id' => $invoice->merchant_id,
                    'recipient_type' => Merchant::class,
                    'transaction_type_id' => $invoice_payment,
                    'txn_no' => fake()->unique()->numerify('############'),
                    'currency' => $invoice->currency,
                    'amount' => fake()->numberBetween(20, 3000),
                    'service_fee' => 0,
                    'transaction_provider_id' => TransactionProvider::inRandomOrder()->first()->id,
                    'transaction_channel_id' => TransactionChannel::inRandomOrder()->first()->id,
                    'transaction_status_id' => TransactionStatus::where('slug', 'successful')->first()->id,
                ]);
            }
        }
    }
}
