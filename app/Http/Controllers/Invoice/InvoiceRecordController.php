<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\InvoiceRecordRequest;
use App\Models\InvoiceLog;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithNotification;
use Exception;
use Illuminate\Support\Facades\DB;

class InvoiceRecordController extends Controller
{

    use WithHttpResponses, WithEntity, WithNotification;

    /**
     * Handle the incoming request.
     */
    public function __invoke(InvoiceRecordRequest $request)
    {
        $validated = $request->validated();
        $invoice_no = $validated['invoice_no'];
        $amount = $validated['amount'];
        $message = $validated['message'] ?? null;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error('Invalid merchant account number', 499);
        }

        $invoice = $entity->outgoing_invoices()
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
        $amount = $total_payable < $amount ? $total_payable : $amount;
        $invoice->status = ($total_payable - $amount) <= 0 ? 'paid' : 'partial';

        $log_message = "Recorded a payment of " . $invoice->currency . " " . number_format($amount, 2);
        if (empty($message) == false) {
            $log_message .= " with message '$message'.";
        }

        $log = new InvoiceLog;
        $log->fill([
            'invoice_id' => $invoice->id,
            'message' => "[ISSUER] $log_message",
            'amount' => $amount,
        ]);

        try {
            DB::transaction(function () use ($invoice, $log) {
                $invoice->save();
                $log->save();

                /// create notification for invoice sender
                $this->alert(
                    $invoice->recipient,
                    'notification',
                    $invoice->invoice_no,
                    "Payment recorded for invoice. Invoice Number: $invoice->invoice_no."
                );
            });

            return $this->success([
                'log' => $log,
                'status' => $invoice->status,
            ]);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }
}
