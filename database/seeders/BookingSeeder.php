<?php

namespace Database\Seeders;

use App\Models\Balance;
use App\Models\Booking;
use App\Models\BookingAnswer;
use App\Models\BookingStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Location;
use App\Models\Merchant;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\WithNumberGeneration;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    use WithNumberGeneration;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = Service::with('merchant')->get();

        foreach($services as $service) {
            $merchant = $service->merchant;
            $invoice_prefix = $merchant->invoice_prefix;
            // $entity = User::active()->inRandomOrder()->limit(15)->get();
            $entity = Merchant::inRandomOrder()->limit(15)->get();

            foreach($entity as $entity) {
                $date = fake()->dateTimeBetween('-1 month', '-1 week');

                $maxAttempts = 10;
                $attempts = 0;
                $time = null;

                do {
                    $attempts++;
                    
                    // Decode service_days JSON and get a random day and corresponding time slot
                    $serviceDaysArray = $service->service_days;
                    $randomDay = fake()->randomElement(array_keys($serviceDaysArray)); // Get a random day (e.g., 'Monday')
                    $time = array_slice($serviceDaysArray[$randomDay], fake()->numberBetween(0, count($serviceDaysArray[$randomDay]) - 1), fake()->numberBetween(1, count($serviceDaysArray[$randomDay]))); // Get one or more random time slots for the day
                
                    // Generate a random date within the current month that matches the randomDay
                    $currentMonth = Carbon::now()->month;
                    $randomDate = Carbon::now()
                                    ->startOfMonth()
                                    ->modify("next $randomDay") // Find the first occurrence of the day in the month
                                    ->addWeeks(fake()->numberBetween(0, 3)); // Add a random number of weeks (0-3) to get a date within the month
                
                    // Check if the generated date is still within the current month
                    if ($randomDate->month !== $currentMonth) {
                        $randomDate = $randomDate->subWeek(); // If it exceeds the month, adjust back by one week
                    }
                
                    // Break if maximum attempts reached (to prevent infinite loop)
                    if ($attempts >= $maxAttempts) {
                        throw new \Exception('Unable to find an available slot after multiple attempts');
                    }
                
                }  while (Booking::where('service_id', $service->id)
                    ->where('service_date', $randomDate->format('Y-m-d'))
                    ->where('slots', $time)
                    ->exists());

                if ($time == null) {
                    continue;
                }

                if ($attempts >= $maxAttempts) {
                    continue;
                }

                if ($randomDate > now()) {
                    $booking_status = BookingStatus::whereIn('slug', ['inquiry', 'booked', 'quoted'])->inRandomOrder()->first();
                } elseif ($randomDate < now()) {
                    $booking_status = BookingStatus::whereNot('slug', 'booked')->inRandomOrder()->first();
                } else {
                    $booking_status = BookingStatus::where('slug', 'in_progress')->first();
                }

                if ($booking_status->slug == 'inquiry') {
                    $invoice = null;
                } else {
                    $type = $booking_status->slug == 'quoted' ? 'quotation' : 'payable';

                    $invoice = Invoice::factory()->create([
                        'sender_id' => $service->merchant_id,
                        'sender_type' => Merchant::class,
                        'recipient_id' => $entity->id,
                        'recipient_type' => get_class($entity),
                        'invoice_no' => $invoice_prefix . '-' . fake()->unique()->numerify('#########'),
                        'due_date' => Carbon::parse($date)->addWeek(),
                        'type' => $type,
                        'status' => 'unpaid',
                        'created_at' => $date,
                        'updated_at' => $date
                    ]);
    
                    $invoice_item = InvoiceItem::factory()->create([
                        'invoice_id' => $invoice->id,
                        'name' => $service->name,
                        'created_at' => $date,
                        'updated_at' => $date
                    ]);
                }


                $booking = Booking::factory()->create([
                    'entity_id' => $entity->id,
                    'entity_type' => get_class($entity),
                    'service_id' => $service->id,
                    'service_date' => $randomDate,
                    'slots' => $time,
                    'invoice_id' => $invoice ? $invoice->id : null,
                    'booking_status_id' => $booking_status->id,
                    'created_at' => $date,
                    'updated_at' => $date
                ]);

                $location = Location::factory()->create([
                    'entity_id' => $booking->id,
                    'entity_type' => Booking::class
                ]);

                $service = $service->load(['form_questions.choices']);

                foreach ($service->form_questions as $question) {
                    $question_type = $question->type;

                    $answer = fake()->text(50);
                    $choices = [];
                    if ($question_type !== 'paragraph') {
                        $answer = fake()->randomElement($question->choices?->pluck('value')->toArray());
                        $choices = $question->choices?->pluck('value')->toArray();
                    }

                    $booking_answer = BookingAnswer::create([
                        'booking_id' => $booking->id,
                        'question' => $question->question,
                        'answer' => [
                            'choices' => $choices,
                            'selected' => $answer
                        ]
                    ]);
                }

                if ($booking_status->slug == 'fulfilled') {
                    $transaction_provider = TransactionProvider::inRandomOrder()->first();
                    $transaction_channel = TransactionChannel::inRandomOrder()->first();
                    $transaction_type = TransactionType::inRandomOrder()->first();

                    $get_invoice = $invoice->withSum('items as items_sum', 'price')->first();
                    
                    $transaction = Transaction::create([
                        'sender_id' => $entity->id,
                        'sender_type' => get_class($entity),
                        'recipient_id' => $service->merchant_id,
                        'recipient_type' => Merchant::class,
                        'txn_no' => $this->generate_transaction_number(),
                        'ref_no' => $invoice->invoice_no,
                        'currency' => 'PHP',
                        'amount' => $get_invoice->items_sum,
                        'service_fee' => 0,
                        'transaction_provider_id' => $transaction_provider->id,
                        'transaction_channel_id' => $transaction_channel->id,
                        'transaction_type_id' => $transaction_type->id,
                        'rate' => 1,
                        'transaction_status_id' => TransactionStatus::where('slug', 'successful')->first()->id,
                        'created_at' => $date,
                        'updated_at' => $date
                    ]);

                    $invoice->status = 'paid';
                    $invoice->save();

                    $sender_balance = $entity->latest_balance()->first();

                    $sender_new_balance = new Balance;
                    $sender_new_balance->entity_id = $entity->id;
                    $sender_new_balance->entity_type = get_class($entity);
                    $sender_new_balance->amount = ($sender_balance->amount ?? 0) - $get_invoice->items_sum;
                    $sender_new_balance->transaction_id = $transaction->id;
                    $sender_new_balance->created_at = $date;
                    $sender_new_balance->updated_at = $date;
                    $sender_new_balance->save();

                    $merchant_balance = $service->merchant()->first()->latest_balance()->first();

                    $merchant_new_balance = new Balance;
                    $merchant_new_balance->entity_id = $service->merchant_id;
                    $merchant_new_balance->entity_type = Merchant::class;
                    $merchant_new_balance->amount = ($merchant_balance->amount ?? 0) + $get_invoice->items_sum;
                    $merchant_new_balance->transaction_id = $transaction->id;
                    $merchant_new_balance->created_at = $date;
                    $merchant_new_balance->updated_at = $date;
                    $merchant_new_balance->save();
                }
            }
        }
    }
}
