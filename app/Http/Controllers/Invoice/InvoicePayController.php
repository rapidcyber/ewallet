<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\InvoicePayRequest;
use App\Models\InvoiceLog;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Traits\WithBalance;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithNotification;
use Exception;
use Illuminate\Support\Facades\DB;

class InvoicePayController extends Controller
{

    use WithHttpResponses, WithEntity, WithBalance, WithNotification;

    /**
     * Handle the incoming request.
     */
    public function __invoke(InvoicePayRequest $request)
    {
        $validated = $request->validated();
        $invoice_no = $validated['invoice_no'];
        $amount = $validated['amount'];
        $message = $validated['message'] ?? null;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error('Invalid merchant account number', 499);
        }

        $invoice = $entity->incoming_invoices()
            ->where(['invoice_no' => $invoice_no, 'type' => 'payable'])
            ->with(['items', 'inclusions'])
            ->withSum('logs as total_paid', 'amount')
            ->first();

        if (empty($invoice)) {
            return $this->error('Invalid invoice number', 499);
        }

        if ($invoice->status == 'paid') {
            return $this->error('Already paid', 499);
        }

        $item_sum = 0;
        foreach ($invoice->items as $item) {
            $item_sum += ($item->price * $item->quantity);
        }

        $inclusion_sum = 0;
        foreach ($invoice->inclusions as $inclusion) {
            if ($inclusion->deduct) {
                $inclusion_sum -= $inclusion->amount;
            } else {
                $inclusion_sum += $inclusion->amount;
            }
        }

        $grand_total = $item_sum + $inclusion_sum;
        $total_payable = $grand_total - $invoice->total_paid;
        // dd($grand_total, $invoice->total_paid, $total_payable);

        /// check balance of the payor (invoice recipient)
        $is_sufficient = $this->is_sufficient($invoice->recipient, $amount);
        if ($is_sufficient == false) {
            return $this->error(config('constants.messages.insufficient_bal'), 499);
        }

        /// Deny partial payment if minimum_partial === 0
        if ($invoice->minimum_partial == 0 and $grand_total > $amount) {
            return $this->error("Partial payment is not allowed", 499);
        }

        /// Deny partial payment if minimum_partial is greater than provided amount and total payable is greater than minimum_partial
        if ($invoice->minimum_partial > $amount and $invoice->minimum_partial < $total_payable) {
            return $this->error("Minimum payable amount is $invoice->currency $invoice->minimum_partial", 499);
        }

        /// Set amount to total_payable if total_payable is smaller than amount provided
        $amount = $total_payable < $amount ? $total_payable : $amount;

        /// Set invoice status according to amount paid
        $invoice->status = ($total_payable - $amount) <= 0 ? 'paid' : 'partial';

        /// create transaction
        $transaction_provider = TransactionProvider::where('slug', 'repay')->first();
        $transaction_channel = TransactionChannel::where('slug', 'repay')->first();
        $transaction_type = TransactionType::where('slug', 'invoice_payment')->first();

        $transaction = new Transaction();
        $transaction->sender_id = $invoice->recipient_id;
        $transaction->sender_type = $invoice->recipient_type;
        $transaction->recipient_id = $invoice->sender_id;
        $transaction->recipient_type = $invoice->sender_type;
        $transaction->txn_no = $this->generate_transaction_number();
        $transaction->ref_no = $invoice->invoice_no;
        $transaction->transaction_provider_id = $transaction_provider->id;
        $transaction->transaction_channel_id = $transaction_channel->id;
        $transaction->transaction_type_id = $transaction_type->id;
        $transaction->currency = 'PHP';
        $transaction->service_fee = 0;
        $transaction->amount = $invoice->minimum_partial ? $amount : $grand_total;
        $transaction->transaction_status_id = TransactionStatus::where('slug', 'successful')->first()->id;

        $log_message = "Paid an amount of " . $invoice->currency . " " . number_format($transaction->amount, 2);
        if (empty($message) == false) {
            $log_message .= " with message $message.";
        }

        $log = new InvoiceLog;
        $log->fill([
            'invoice_id' => $invoice->id,
            'message' => "[RECIPIENT] $log_message",
            'amount' => $transaction->amount,
        ]);

        try {
            DB::transaction(function () use ($transaction, $invoice, $log) {
                $transaction->save();
                $invoice->save();

                /// create payment history
                $log->transaction_id = $transaction->id;
                $log->save();

                /// update payor (recipient of invoice) balance (-)
                $this->credit($invoice->recipient, $transaction);

                /// update payee (sender of invoice) balance (+)
                $this->debit($invoice->sender, $transaction);

                /// create notification for invoice sender
                $this->alert(
                    $invoice->sender,
                    'transaction',
                    $invoice->invoice_no,
                    "Received invoice payment.\n\nInvoice no: " . $invoice->invoice_no . "."
                );
            });

            $transaction->load(['type']);
            $transaction->inbound = false;
            return $this->success([
                'transaction' => $transaction,
                'log' => $log,
                'status' => $invoice->status,
            ]);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }
}
