<?php

namespace App\User\Disputes;

use App\Models\TransactionDispute;
use App\Models\User;
use App\Traits\WithImage;
use Livewire\Attributes\Layout;
use Livewire\Component;

class UserDisputesShow extends Component
{
    use WithImage;

    public TransactionDispute $dispute;

    public function mount(TransactionDispute $transactionDispute)
    {
        $this->dispute = TransactionDispute::whereHas('transaction', function ($transaction) {
            $transaction->whereHasMorph('sender', [User::class], function ($user) {
                $user->where('id', auth()->id());
            });
        })
            ->with(['reason', 'transaction', 'media'])
            ->findOrFail($transactionDispute->id);

    }

    #[Layout('layouts.user')]
    public function render()
    {
        return view('user.disputes.user-disputes-show');
    }
}
