<?php

namespace App\Admin\ManageUsers\Show\Transactions;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithValidPhoneNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageUsersShowTransactionsCashOutflow extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithValidPhoneNumber;

    #[Locked]
    public $transaction_types;
    public User $user;
    public $activeBox = 'all';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $dateFilter = 'none';
    public $searchTerm = '';

    public $transactionDetails;

    public function mount(User $user)
    {
        $this->user = $user->load(['profile', 'latest_balance', 'media' => function ($query) {
            $query->whereIn('collection_name', ['profile_picture']);
        }]);

        $this->transaction_types = TransactionType::all();
    }

    public function handleFilterBoxClick($value)
    {
        if ($this->activeBox !== $value) {
            if (in_array($value, ['all', 'money_transfers', 'order_payments', 'invoice_payments', 'cash_out', 'bills'])) {
                $this->activeBox = $value;
            } else {
                $this->activeBox = 'all';
            }

            $this->resetPage();
            $this->reset(['sortBy', 'sortDirection']);
        }
    }

    public function sortTable($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            if (!in_array($field, ['created_at', 'total_amount'])) {
                $this->sortBy = 'created_at';
            } else {
                $this->sortBy = $field;
            }

            $this->sortDirection = 'desc';
        }
    }

    public function updatedDateFilter()
    {
        if (!in_array($this->dateFilter, ['none', 'past_24_hours', 'past_week', 'past_30_days', 'past_6_months', 'past_year'])) {
            $this->dateFilter = 'none';
        }

        $this->resetPage();
    }

    #[Computed]
    public function count_all()
    {
        $count = match ($this->dateFilter) {
            'none' => $this->user->outgoing_transactions()->count(),
            'past_24_hours' => $this->user->outgoing_transactions()->where('created_at', '>=', now()->subHours(24))->count(),
            'past_week' => $this->user->outgoing_transactions()->where('created_at', '>=', now()->subWeek())->count(),
            'past_30_days' => $this->user->outgoing_transactions()->where('created_at', '>=', now()->subDays(30))->count(),
            'past_6_months' => $this->user->outgoing_transactions()->where('created_at', '>=', now()->subMonths(6))->count(),
            'past_year' => $this->user->outgoing_transactions()->where('created_at', '>=', now()->subYear())->count(),
        };

        return $count;
    }

    #[Computed]
    public function count_money_transfers()
    {
        $count = match ($this->dateFilter) {
            'none' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->count(),
            'past_24_hours' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_week' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->where('created_at', '>=', now()->subWeek())->count(),
            'past_30_days' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->where('created_at', '>=', now()->subDays(30))->count(),
            'past_6_months' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->where('created_at', '>=', now()->subMonths(6))->count(),
            'past_year' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->where('created_at', '>=', now()->subYear())->count(),
        };

        return $count;
    }

    #[Computed]
    public function count_order_payments()
    {
        $count = match ($this->dateFilter) {
            'none' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->count(),
            'past_24_hours' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_week' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->where('created_at', '>=', now()->subWeek())->count(),
            'past_30_days' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->where('created_at', '>=', now()->subDays(30))->count(),
            'past_6_months' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->where('created_at', '>=', now()->subMonths(6))->count(),
            'past_year' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->where('created_at', '>=', now()->subYear())->count(),
        };

        return $count;
    }

    #[Computed]
    public function count_invoice_payments()
    {
        $count = match ($this->dateFilter) {
            'none' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->count(),
            'past_24_hours' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_week' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->where('created_at', '>=', now()->subWeek())->count(),
            'past_30_days' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->where('created_at', '>=', now()->subDays(30))->count(),
            'past_6_months' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->where('created_at', '>=', now()->subMonths(6))->count(),
            'past_year' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->where('created_at', '>=', now()->subYear())->count(),
        };

        return $count;
    }

    #[Computed]
    public function count_cash_out()
    {
        $count = match ($this->dateFilter) {
            'none' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'CO')->first()->id)->count(),
            'past_24_hours' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'CO')->first()->id)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_week' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'CO')->first()->id)->where('created_at', '>=', now()->subWeek())->count(),
            'past_30_days' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'CO')->first()->id)->where('created_at', '>=', now()->subDays(30))->count(),
            'past_6_months' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'CO')->first()->id)->where('created_at', '>=', now()->subMonths(6))->count(),
            'past_year' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'CO')->first()->id)->where('created_at', '>=', now()->subYear())->count(),
        };

        return $count;
    }

    #[Computed]
    public function count_bills()
    {
        $count = match ($this->dateFilter) {
            'none' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'BP')->first()->id)->count(),
            'past_24_hours' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'BP')->first()->id)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_week' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'BP')->first()->id)->where('created_at', '>=', now()->subWeek())->count(),
            'past_30_days' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'BP')->first()->id)->where('created_at', '>=', now()->subDays(30))->count(),
            'past_6_months' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'BP')->first()->id)->where('created_at', '>=', now()->subMonths(6))->count(),
            'past_year' => $this->user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'BP')->first()->id)->where('created_at', '>=', now()->subYear())->count(),
        };

        return $count;
    }

    public function handleTableRowClick($txn_no)
    {
        $transaction = $this->user->outgoing_transactions()
            ->where('txn_no', $txn_no)
            ->first();

        if (! $transaction) {
            return session()->flash('error', 'Error: Transaction not found');
        }

        $recipient = $transaction->recipient;

        $number = '';
        if (get_class($recipient) == User::class) {
            $number = $this->format_phone_number($recipient->phone_number, $recipient->phone_iso);
        } elseif (get_class($recipient) == Merchant::class) {
            $number = $recipient->account_number;
        }

        $this->transactionDetails = [
            'label' => 'Sent to:',
            'txn_no' => $transaction->txn_no,
            'transaction_type' => $transaction->type->name,
            'entity_name' => $transaction->recipient->name,
            'phone_number' => $number,
            'amount' => $transaction->amount,
            'service_fee' => $transaction->service_fee,
            'total_amount' => $transaction->amount + $transaction->service_fee,
            'ref_no' => $transaction->ref_no,
            'created_at' => Carbon::parse($transaction->created_at)->timezone('Asia/Manila')->format('F j, Y - g:i A'),
        ];

        $this->dispatch('showTransactionDetails');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $cash_outflows = $this->user->outgoing_transactions()
            ->selectRaw('transactions.* , transactions.amount + transactions.service_fee as total_amount')
            ->with(['type', 'status', 'recipient']);


        if ($this->activeBox !== 'all') {
            $cash_outflows = match ($this->activeBox) {
                'money_transfers' => $cash_outflows->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id),
                'order_payments' => $cash_outflows->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id),
                'invoice_payments' => $cash_outflows->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id),
                'cash_out' => $cash_outflows->where('transaction_type_id', $this->transaction_types->where('code', 'CO')->first()->id),
                'bills' => $cash_outflows->where('transaction_type_id', $this->transaction_types->where('code', 'BP')->first()->id),
            };
        }
        

        if ($this->dateFilter !== 'none') {
            $cash_outflows = match ($this->dateFilter) {
                'past_24_hours' => $cash_outflows->where('created_at', '>=', now()->subHours(24)),
                'past_week' => $cash_outflows->where('created_at', '>=', now()->subDays(7)),
                'past_30_days' => $cash_outflows->where('created_at', '>=', now()->subDays(30)),
                'past_6_months' => $cash_outflows->where('created_at', '>=', now()->subMonths(6)),
                'past_year' => $cash_outflows->where('created_at', '>=', now()->subYear()),
            };
        }

        if ($this->searchTerm) {
            $cash_outflows = $cash_outflows->where(function ($query) {
                $query->where('ref_no', 'LIKE', '%' . $this->searchTerm . '%');
                $query->orWhereHasMorph('recipient', [User::class], function ($user) {
                    $user->whereHas('profile', function ($profile) {
                        $profile->where('first_name', 'LIKE', '%' . $this->searchTerm . '%');
                        $profile->orWhere('surname', 'LIKE', '%' . $this->searchTerm . '%');
                    });
                });
                $query->orWhereHasMorph('recipient', [Merchant::class], function ($merchant) {
                    $merchant->where('name', 'LIKE', '%' . $this->searchTerm . '%');
                });
            });
        }

        $cash_outflows = $cash_outflows
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);

        $elements = $this->getPaginationElements($cash_outflows);

        return view('admin.manage-users.show.transactions.admin-manage-users-show-transactions-cash-outflow')->with([
            'cash_outflows' => $cash_outflows,
            'elements' => $elements
        ]);
    }
}
