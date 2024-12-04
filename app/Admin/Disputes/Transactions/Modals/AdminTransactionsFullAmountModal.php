<?php

namespace App\Admin\Disputes\Transactions\Modals;

use App\Models\TransactionDispute;
use App\Traits\WithBalance;
use App\Traits\WithLog;
use App\Traits\WithNotification;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AdminTransactionsFullAmountModal extends Component
{
    use WithBalance, WithLog, WithNotification;

    public TransactionDispute $dispute;

    #[Locked]
    public $transactionAmount;

    public function mount($dispute_id)
    {
        $this->dispute = TransactionDispute::where('id', $dispute_id)
            ->where('status', 'pending')
            ->with(['transaction.sender', 'transaction.recipient'])
            ->firstOrFail();

        $this->transactionAmount = $this->dispute->transaction->amount + $this->dispute->transaction->service_fee;
    }

    public function pay_full()
    {
        if ($this->dispute->status != 'pending') {
            return;
        }

        DB::beginTransaction();
        try {
            $this->dispute->status = 'fully-paid';
            $this->dispute->save();

            $this->credit($this->dispute->transaction->recipient, $this->dispute->transaction);
            $this->refund($this->dispute->transaction->sender, $this->dispute->transaction);

            $this->admin_action_log('Fully paid transaction dispute', 'Transaction Dispute ID: ' . $this->dispute->id);

            $this->alert(
                $this->dispute->transaction->sender,
                'notification',
                $this->dispute->id,
                "Your dispute for transaction number {$this->dispute->transaction->txn_no} has been resolved. A full refund has been made to your account.",

            );

            DB::commit();
            return $this->dispatch('successModal', [
                'header' => 'Success',
                'message' => 'Dispute fully paid successfully.'
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('AdminTransactionsFullAmountModal.pay_full: ' . $ex->getMessage());
            return $this->dispatch('failedModal', [
                'header' => 'Failed to refund dispute.',
                'message' => 'Something went wrong. Please try again later.'
            ]);
        }
    }

    public function render()
    {
        return view('admin.disputes.transactions.modals.admin-transactions-full-amount-modal');
    }
}
