<?php

namespace App\User\Disputes;

use App\Models\TransactionDispute;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class UserDisputes extends Component
{
    use WithPagination, WithCustomPaginationLinks;

    public $searchTerm = '';
    public $sortDirection = 'desc';
    public User $user;

    public function mount()
    {
        $this->user = auth()->user();
    }

    #[Computed]
    public function disputes_count()
    {
        return $this->user->transaction_disputes()->count();
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

    public function view_dispute($dispute_id)
    {
        $dispute = $this->user->transaction_disputes()->where('transaction_disputes.id', $dispute_id)->first();

        if (!$dispute) {
            return session()->flash('error', 'Error: Transaction Dispute not found!');
        }

        return $this->redirect(route('user.disputes.show', ['transactionDispute' => $dispute]));
    }

    #[Layout('layouts.user')]
    public function render()
    {
        $disputes = $this->user->transaction_disputes()->with(['transaction', 'reason']);

        if ($this->searchTerm) {
            $disputes = $disputes->where(function ($query) {
                $query->whereHas('reason', function ($query) {
                    $query->where('name', 'like', '%' . $this->searchTerm . '%');
                });
                $query->orWhereHas('transaction', function ($query) {
                    $query->where('txn_no', 'like', '%' . $this->searchTerm . '%');
                });
            });
        }

        $disputes = $disputes->orderBy('created_at', $this->sortDirection)->paginate(10);

        $elements = $this->getPaginationElements($disputes);

        return view('user.disputes.user-disputes')->with([
            'disputes' => $disputes,
            'elements' => $elements
        ]);
    }
}
