<?php

namespace App\Merchant\SellerCenter\Disputes;

use App\Models\Merchant;
use App\Models\TransactionDispute;
use App\Traits\WithImage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MerchantSellerCenterDisputesShow extends Component
{
    use WithImage;

    public Merchant $merchant;
    public TransactionDispute $dispute;

    public function mount(Merchant $merchant, TransactionDispute $transactionDispute)
    {
        $this->merchant = $merchant;
        $this->dispute = $transactionDispute->load(['reason', 'transaction', 'media']);
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.disputes.merchant-seller-center-disputes-show');
    }
}
