<?php

namespace App\Admin\Disputes\Transactions;

use App\Models\Merchant;
use App\Models\ReturnOrder;
use App\Models\TransactionDispute;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminDisputesTransactions extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage, WithValidPhoneNumber;

    public $searchTerm = '';
    public $sortDirection = 'desc';

    #[Computed]
    public function return_order_count()
    {
        return ReturnOrder::count();
    }

    #[Computed]
    public function disputes_count()
    {
        return TransactionDispute::count();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
        $this->sortDirection = 'desc';
    }

    public function toggleSortDirection()
    {
        $this->sortDirection = $this->sortDirection === 'desc' ? 'asc' : 'desc';
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $disputes = TransactionDispute::with(['transaction.sender' => function (MorphTo $q) {
            $q->morphWith([
                User::class => ['profile', 'media' => function ($q) {
                    $q->where('collection_name', 'profile_picture');
                }],
                Merchant::class => ['media' => function ($q) {
                    $q->where('collection_name', 'merchant_logo');
                }]
            ]);
        }, 'reason:id,name']);

        if ($this->searchTerm) {
            $disputes = $disputes->where(function ($query) {
                $query->whereHas('reason', function ($query) {
                    $query->where('name', 'like', '%' . $this->searchTerm . '%');
                });
                $query->orWhereHas('transaction', function ($query) {
                    $query->where('ref_no', 'like', '%' . $this->searchTerm . '%');
                    $query->orWhere(function ($q) {
                        $q->whereHasMorph('sender', User::class, function ($q) {
                            $q->whereHas('profile', function ($q) {
                                $q->where('first_name', 'like', '%' . $this->searchTerm . '%');
                                $q->orWhere('surname', 'like', '%' . $this->searchTerm . '%');
                            });
                            $q->orWhere('phone_number', 'like', '%' . $this->searchTerm . '%');
                        });
                        $q->orWhereHasMorph('sender', Merchant::class, function ($q) {
                            $q->where('name', 'like', '%' . $this->searchTerm . '%');
                            $q->orWhere('phone_number', 'like', '%' . $this->searchTerm . '%');
                        });
                    });
                });
            });
        }

        $disputes = $disputes->orderBy('created_at', $this->sortDirection)->paginate(10);

        $elements = $this->getPaginationElements($disputes);

        return view('admin.disputes.transactions.admin-disputes-transactions')->with([
            'disputes' => $disputes,
            'elements' => $elements
        ]);
    }
}
