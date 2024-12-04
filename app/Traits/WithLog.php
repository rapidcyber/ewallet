<?php

namespace App\Traits;
use App\Models\AdminLog;
use App\Models\InvoiceLog;

trait WithLog
{
    //

    /**
     * Create a log for invoice payment.
     * 
     * @param mixed $transaction
     * @param mixed $invoice
     * @param mixed $message
     * @return \App\Models\InvoiceLog
     */
    public function invoice_payment_log($invoice, $transaction, ?string $message): InvoiceLog
    {
        if (empty($message)) {
            $message = "Payment of " . $invoice->currency . " " . number_format($transaction->amount, 2);
        }

        $log = new InvoiceLog;
        $log->fill([
            'invoice_id' => $invoice->id,
            'transaction_id' => $transaction->id,
            'message' => $message,
            'amount' => $transaction->amount,
        ]);

        return $log;
    }

    public function admin_action_log($title, $message = null)
    {
        $log = new AdminLog;
        $log->user_id = auth()->id();
        $log->title = $title;
        $log->description = $message ?? null;
        $log->save();
    }
}
