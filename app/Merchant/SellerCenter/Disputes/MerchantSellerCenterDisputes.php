<?php

namespace App\Merchant\SellerCenter\Disputes;

use App\Models\Merchant;
use App\Models\TransactionDispute;
use App\Traits\WithCustomPaginationLinks;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantSellerCenterDisputes extends Component
{
    use WithPagination, WithCustomPaginationLinks;

    public Merchant $merchant;
    public $searchTerm = '';
    public $sortDirection = 'desc';

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    public function toggleSortDirection()
    {
        $this->sortDirection = $this->sortDirection == 'asc' ? 'desc' : 'asc';
    }

    #[Computed]
    public function transaction_disputes_count()
    {
        return $this->merchant->transaction_disputes()->count();
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        $disputes = TransactionDispute::with(['transaction', 'reason'])
            ->whereHas('transaction', function ($query) {
                $query->whereHasMorph('sender', Merchant::class, function ($q) {
                    $q->where('id', $this->merchant->id);
                });
            });

        if ($this->searchTerm) {
            $disputes = $disputes->where(function ($query) {
                $query->whereHas('transaction', function ($q) {
                    $q->where('ref_no', 'like', '%' . $this->searchTerm . '%');
                });
                $query->orWhereHas('reason', function ($q) {
                    $q->where('name', 'like', '%' . $this->searchTerm . '%');
                });
            });
        }

        $disputes = $disputes->orderBy('created_at', $this->sortDirection)
            ->paginate(10);

        $elements = $this->getPaginationElements($disputes);

        return view('merchant.seller-center.disputes.merchant-seller-center-disputes-list')->with([
            'disputes' => $disputes,
            'elements' => $elements,
        ]);
    }
}
