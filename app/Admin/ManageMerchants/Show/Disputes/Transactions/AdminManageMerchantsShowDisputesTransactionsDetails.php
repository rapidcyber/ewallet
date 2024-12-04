<?php

namespace App\Admin\ManageMerchants\Show\Disputes\Transactions;

use App\Models\Merchant;
use App\Models\TransactionDispute;
use App\Traits\WithImage;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminManageMerchantsShowDisputesTransactionsDetails extends Component
{
    use WithImage, WithValidPhoneNumber;

    public TransactionDispute $dispute;

    #[Locked]
    public $action = '';

    protected $allowedActions = [
        'pay_full',
        'pay_custom',
        'deny'
    ];

    public function mount(Merchant $merchant, TransactionDispute $transactionDispute)
    {
        $this->dispute = $transactionDispute->load(['reason', 'transaction.sender', 'media' => function ($q) {
            $q->where('collection_name', 'dispute_images');
        }]);
    }

    #[Computed]
    public function action_allowed()
    {
        if ($this->dispute->status == 'pending') {
            return true;
        }
        return false;
    }

    #[On('successModal')]
    public function successModal($message)
    {
        $this->closeModal();
        if (isset($message['header'])) {
            session()->flash('success', $message['header']);
        }

        if (isset($message['message'])) {
            session()->flash('success_message', $message['message']);
        }
    }

    #[On('failedModal')]
    public function failedModal($message)
    {
        $this->closeModal();
        if (isset($message['header'])) {
            session()->flash('error', $message['header']);
        }

        if (isset($message['message'])) {
            session()->flash('error_message', $message['message']);
        }
    }

    #[On('closeModal')]
    public function closeModal()
    {
        $this->action = '';
    }

    public function set_action($action)
    {
        if (!in_array($action, $this->allowedActions)) {
            return;
        }

        $this->action = $action;
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('admin.disputes.transactions.admin-disputes-transactions-show');
    }
}
