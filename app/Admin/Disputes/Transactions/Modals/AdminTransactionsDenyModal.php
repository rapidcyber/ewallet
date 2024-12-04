<?php

namespace App\Admin\Disputes\Transactions\Modals;

use App\Models\TransactionDispute;
use App\Traits\WithLog;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AdminTransactionsDenyModal extends Component
{
    use WithLog;

    public TransactionDispute $dispute;

    public function mount($dispute_id)
    {
        $this->dispute = TransactionDispute::where('id', $dispute_id)
            ->where('status', 'pending')
            ->firstOrFail();
    }

    public function deny_dispute()
    {
        if ($this->dispute->status != 'pending') {
            return;
        }

        DB::beginTransaction();
        try {
            $this->dispute->status = 'denied';
            $this->dispute->save();

            $this->admin_action_log('Denied transaction dispute', 'Transaction Dispute ID: ' . $this->dispute->id);

            $this->alert(
                $this->dispute->transaction->sender,
                'notification',
                $this->dispute->id,
                "Your dispute for transaction number {$this->dispute->transaction->txn_no} has been denied.",
                false
            );
            
            DB::commit();

            return $this->dispatch('successModal', [
                'header' => 'Success',
                'message' => 'Dispute denied successfully.'
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('AdminTransactionsDenyModal.deny_dispute: ' . $ex->getMessage());
            return $this->dispatch('errorModal', [
                'header' => 'Failed to deny dispute.',
                'message' => 'Something went wrong. Please try again later.'
            ]);
        }
    }

    public function render()
    {
        return view('admin.disputes.transactions.modals.admin-transactions-deny-modal');
    }
}
