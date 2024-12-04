<?php

namespace App\Admin\ManageUsers\Show\Disputes\Transactions;

use App\Models\TransactionDispute;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageUsersShowDisputesTransactions extends Component
{
    use WithCustomPaginationLinks, WithPagination;

    public User $user;

    public $searchTerm = '';

    public $orderBy = 'desc';

    public $orderByFieldName = 'created_at';

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function sortTable($fieldName)
    {

        if ($this->orderByFieldName !== $fieldName) {
            $this->orderByFieldName = $fieldName;
            $this->orderBy = 'desc';
        } else {
            if ($this->orderBy === 'desc') {
                $this->orderBy = 'asc';
            } elseif ($this->orderBy === 'asc') {
                $this->orderBy = 'desc';
            }
        }
    }

    #[Computed(persist: true)]
    public function transaction_disputes_count()
    {
        return $this->user->transaction_disputes()->count();
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $disputes = TransactionDispute::with(['transaction', 'reason'])
            ->whereHas('transaction', function ($query) {
                $query->whereHasMorph('sender', User::class, function ($q) {
                    $q->where('id', $this->user->id);
                });
            });

        if ($this->searchTerm) {
            $disputes = $disputes->where(function ($query) {
                $query->whereHas('transaction', function ($q) {
                    $q->where('ref_no', 'like', '%'.$this->searchTerm.'%');
                });
                $query->orWhereHas('reason', function ($q) {
                    $q->where('name', 'like', '%'.$this->searchTerm.'%');
                });
            });
        }

        $disputes = $disputes->orderBy($this->orderByFieldName, $this->orderBy)
            ->paginate(15);
        $disputesCount = $disputes->count();

        $elements = $this->getPaginationElements($disputes);

        return view('admin.manage-users.show.disputes.transactions.admin-manage-users-show-disputes-transactions')->with([
            'disputes' => $disputes,
            'elements' => $elements,
            'disputesCount' => $disputesCount,
        ]);
    }
}
