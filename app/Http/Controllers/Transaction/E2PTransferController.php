<?php

namespace App\Http\Controllers\Transaction;

use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\E2PTransferRequest;
use App\Models\User;
use App\Traits\WithBalance;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithNotification;
use App\Traits\WithTransactionLimit;
use Exception;
use Illuminate\Support\Facades\DB;

class E2PTransferController extends Controller
{
    use WithHttpResponses, WithEntity, WithBalance, WithTransactionLimit, WithNotification;

    /**
     * Handle the incoming request for E2P fund transfer.
     * 
     * E2P - Entity Account (User/Merchant) to Personal Account (User)
     */
    public function __invoke(E2PTransferRequest $request)
    {
        $validated = $request->validated();

        $amount = $validated['amount'];
        $phone_number = $validated['phone_number'];

        $sender = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($sender)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        /// User can't transfer funds to the same account.
        /// That's ducking fumb.
        if (get_class($sender) == User::class and $sender->phone_number == $phone_number) {
            return $this->error(config('constants.messages.err_same_entity_transfer'), 499);
        }

        /// Check if entity's balance is sufficient to proceed with the transaction.
        $is_sufficient = $this->is_sufficient($sender, $amount);
        if ($is_sufficient == false) {
            return $this->error(config('constants.messages.insufficient_bal'), 499);
        }

        $recipient = User::where('phone_number', $phone_number)->first();
        $provider = TransactionProvider::where('slug', 'repay')->first();
        $channel = TransactionChannel::where('slug', 'repay')->first();
        $type = TransactionType::where('slug', 'transfer')->first();

        /// Check if the entity reach it's transaction limit for the current transaction type.
        $limited = $this->check_outbound_limit($sender, $type, $amount);
        if ($limited) {
            return $this->error(config('constants.messages.txn_limit_reach'), 499);
        }

        $transaction = new Transaction;
        $transaction->fill([
            'sender_id' => $sender->id,
            'sender_type' => get_class($sender),
            'recipient_id' => $recipient->id,
            'recipient_type' => get_class($recipient),
            'txn_no' => $this->generate_transaction_number(),
            'ref_no' => $this->generate_transaction_reference_number($provider, $channel, $type),
            'transaction_provider_id' => $provider->id,
            'transaction_channel_id' => $channel->id,
            'transaction_type_id' => $type->id,
            'transaction_status_id' => TransactionStatus::where('slug', 'successful')->first()->id,
            'service_fee' => 0,
            'currency' => 'PHP',
            'amount' => $amount,
        ]);

        try {
            DB::transaction(function () use ($sender, $recipient, $transaction) {
                $sender_name = get_class($sender) == User::class ? "+$sender->phone_number" : $sender->name;

                $transaction->save();
                $this->credit($sender, $transaction);
                $this->debit($recipient, $transaction);
                $this->alert(
                    $recipient,
                    'transaction',
                    $transaction->txn_no,
                    "Received " . $transaction->currency . " " . number_format($transaction->amount, 2)
                    . " from " . $sender_name . ".\n\nTransaction No: " . $transaction->txn_no . '.',
                );
            });

            $transaction->inbound = false;
            $transaction->type;
            return $this->success($transaction);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }
}
