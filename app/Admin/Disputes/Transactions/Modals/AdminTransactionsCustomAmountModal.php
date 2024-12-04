<?php

namespace App\Admin\Disputes\Transactions\Modals;

use App\Models\TransactionDispute;
use App\Traits\WithBalance;
use App\Traits\WithLog;
use App\Traits\WithNotification;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AdminTransactionsCustomAmountModal extends Component
{
    use WithLog, WithBalance, WithNotification;

    public TransactionDispute $dispute;

    public $amount;

    public function mount($dispute_id)
    {
        $this->dispute = TransactionDispute::where('id', $dispute_id)
            ->where('status', 'pending')
            ->with(['transaction.sender', 'transaction.recipient'])
            ->firstOrFail();
    }

    public function pay_custom()
    {
        if ($this->dispute->status != 'pending') {
            return;
        }

        $this->validate([
            'amount' => 'required|numeric|min:1|max:' . $this->dispute->transaction->amount,
        ]);

        DB::beginTransaction();
        try {
            $this->dispute->status = $this->dispute->transaction->amount == $this->amount ? 'fully-paid' : 'partially-paid';
            $this->dispute->save();

            $this->credit($this->dispute->transaction->recipient, $this->dispute->transaction);
            $this->refund($this->dispute->transaction->sender, $this->dispute->transaction);

            $this->admin_action_log('Partially paid transaction dispute', 'Transaction Dispute ID: ' . $this->dispute->id . ', Amount: ' . $this->amount);

            $this->alert(
                $this->dispute->transaction->sender,
                'notification',
                $this->dispute->id,
                "Your dispute for transaction number {$this->dispute->transaction->txn_no} has been resolved. A refund of {$this->amount} has been made to your account.",
            );

            DB::commit();

            $this->dispatch('successModal', [
                'header' => 'Success',
                'message' => 'Dispute partially paid successfully.'
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('AdminTransactionsCustomAmountModal.pay_custom: ' . $ex->getMessage());
            $this->dispatch('failedModal', [
                'header' => 'Failed to refund dispute.',
                'message' => 'Something went wrong. Please try again later.'
            ]);
        }
    }

    public function render()
    {
        return view('admin.disputes.transactions.modals.admin-transactions-custom-amount-modal');
    }
}
