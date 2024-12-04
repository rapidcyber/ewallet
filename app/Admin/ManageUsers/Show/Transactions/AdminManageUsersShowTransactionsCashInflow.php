<?php

namespace App\Admin\ManageUsers\Show\Transactions;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionStatus;
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

class AdminManageUsersShowTransactionsCashInflow extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithValidPhoneNumber;

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
    }

    #[Computed]
    public function transaction_types()
    {
        return TransactionType::all();
    }

    #[Computed]
    public function successful_status()
    {
        return TransactionStatus::where('slug', 'successful')->first()->id;
    }

    public function updatedActiveBox()
    {
        if (! in_array($this->activeBox, ['all', 'money-transfers', 'cash_in', 'salary'])) {
            $this->activeBox = 'all';
        }

        $this->resetPage();
        $this->reset(['sortBy', 'sortDirection']);
    }

    public function sortTable($fieldName)
    {
        if ($this->sortBy === $fieldName) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            if (!in_array($fieldName, ['created_at', 'amount'])) {
                $this->sortBy = 'created_at';
            } else {
                $this->sortBy = $fieldName;
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
            'none' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->count(),
            'past_24_hours' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_week' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('created_at', '>=', now()->subWeek())->count(),
            'past_30_days' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('created_at', '>=', now()->subDays(30))->count(),
            'past_6_months' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('created_at', '>=', now()->subMonths(6))->count(),
            'past_year' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('created_at', '>=', now()->subYear())->count(),
        };

        return $count;
    }

    #[Computed]
    public function count_money_transfers()
    {
        $count = match ($this->dateFilter) {
            'none' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->count(),
            'past_24_hours' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_week' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_30_days' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->where('created_at', '>=', now()->subDays(30))->count(),
            'past_6_months' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->where('created_at', '>=', now()->subMonths(6))->count(),
            'past_year' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->where('created_at', '>=', now()->subYear())->count(),
        };

        return $count;
    }

    #[Computed]
    public function count_cash_in()
    {
        $count = match ($this->dateFilter) {
            'none' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'CI')->first()->id)->count(),
            'past_24_hours' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'CI')->first()->id)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_week' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'CI')->first()->id)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_30_days' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'CI')->first()->id)->where('created_at', '>=', now()->subDays(30))->count(),
            'past_6_months' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'CI')->first()->id)->where('created_at', '>=', now()->subMonths(6))->count(),
            'past_year' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'CI')->first()->id)->where('created_at', '>=', now()->subYear())->count()
        };

        return $count;
    }

    #[Computed]
    public function count_salary()
    {
        $count = match ($this->dateFilter) {
            'none' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'PS')->first()->id)->count(),
            'past_24_hours' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'PS')->first()->id)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_week' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'PS')->first()->id)->where('created_at', '>=', now()->subHours(24))->count(),
            'past_30_days' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'PS')->first()->id)->where('created_at', '>=', now()->subDays(30))->count(),
            'past_6_months' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'PS')->first()->id)->where('created_at', '>=', now()->subMonths(6))->count(),
            'past_year' => $this->user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'PS')->first()->id)->where('created_at', '>=', now()->subYear())->count()
        };

        return $count;
    }

    public function handleTableRowClick($txn_no)
    {
        $transaction = $this->user->incoming_transactions()
            ->where('txn_no', $txn_no)
            ->with(['type', 'sender' => function (MorphTo $q) {
                $q->morphWith([
                    User::class => ['profile'],
                ]);
            }])
            ->first();

        if (!$transaction) {
            return session()->flash('error', 'Transaction not found');
        }

        $sender = $transaction->sender;

        $number = '';
        if (get_class($sender) == User::class) {
            $number = $this->format_phone_number($sender->phone_number, $sender->phone_iso);
        } elseif (get_class($sender) == Merchant::class) {
            $number = $sender->account_number;
        }

        $this->transactionDetails = [
            'label' => 'Sent by:',
            'txn_no' => $transaction->txn_no,
            'transaction_type' => $transaction->type->name,
            'entity_name' => $transaction->sender->name,
            'phone_number' => $number,
            'amount' => $transaction->amount,
            'ref_no' => $transaction->ref_no,
            'created_at' => Carbon::parse($transaction->created_at)->format('F j, Y - g:i A'),
        ];

        $this->dispatch('showTransactionDetails');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $cash_inflows = $this->user->incoming_transactions()
            ->with(['type', 'sender', 'status']);

        if ($this->activeBox !== 'all') {
            $cash_inflows = match ($this->activeBox) {
                'money-transfers' => $cash_inflows->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id),
                'cash_in' => $cash_inflows->where('transaction_type_id', $this->transaction_types->where('code', 'CI')->first()->id),
                'salary' => $cash_inflows->where('transaction_type_id', $this->transaction_types->where('code', 'PS')->first()->id),
            };
        }

        if ($this->dateFilter !== 'none') {
            $cash_inflows = match ($this->dateFilter) {
                'past_24_hours' => $cash_inflows->where('created_at', '>=', now()->subHours(24)),
                'past_week' => $cash_inflows->where('created_at', '>=', now()->subDays(7)),
                'past_30_days' => $cash_inflows->where('created_at', '>=', now()->subDays(30)),
                'past_6_months' => $cash_inflows->where('created_at', '>=', now()->subMonths(6)),
                'past_year' => $cash_inflows->where('created_at', '>=', now()->subYear()),
            };
        }

        if ($this->searchTerm) {
            $cash_inflows = $cash_inflows->where(function ($query) {
                $query->where('ref_no', 'LIKE', '%' . $this->searchTerm . '%');
                $query->orWhereHasMorph('sender', [User::class], function ($user) {
                    $user->where('phone_number', 'LIKE', '%' . $this->searchTerm . '%');
                });
                $query->orWhereHasMorph('sender', [Merchant::class], function ($merchant) {
                    $merchant->where('name', 'LIKE', '%' . $this->searchTerm . '%');
                });
            });
        }

        $cash_inflows = $cash_inflows
            ->orderBy($this->sortBy, $this->sortDirection)
            ->select(
                [
                    'txn_no',
                    'amount',
                    'sender_type',
                    'sender_id',
                    'transaction_type_id',
                    'transaction_status_id',
                    'created_at',
                    'ref_no'
                ]
            )
            ->paginate(20);

        $elements = $this->getPaginationElements($cash_inflows);


        return view('admin.manage-users.show.transactions.admin-manage-users-show-transactions-cash-inflow')->with([
            'cash_inflows' => $cash_inflows,
            'elements' => $elements
        ]);
    }
}
