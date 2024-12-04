<?php

namespace App\Admin\Transactions\CashOutflow;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithValidPhoneNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminCashOutflow extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithValidPhoneNumber;

    public User $user;
    public $dateFilter = 'past_year';
    #[Locked]
    public $fromDate;
    #[Locked]
    public $toDate;
    #[Locked]
    public $vsFromDate;
    #[Locked]
    public $vsToDate;

    public $moneySent = 0;

    public $vsMoneySent = 0;

    public $orderByFieldName = 'created_at';

    public $orderBy = 'desc';

    public $activeBox = '';

    public $searchTerm = '';

    public $transactionDetails = null;

    public $main_content_type = 'cash_outflow';

    public $allCashOutflow = 0;

    public $moneyTransfers = 0;

    public $orderPayments = 0;

    public $invoicePayments = 0;

    public $cashOutflowCount = 0;

    public $bills = 0;

    public $is_property_owner = false;

    protected $allowedOrderByFieldName = [
        'amount',
        'created_at',
    ];

    protected $allowedFilterBox = [
        '',
        'TR',
        'OR',
        'BP',
    ];

    public function mount()
    {
        $this->user = auth()->user();

        $this->updatedDateFilter();
    }

    #[Computed(persist: true)]
    public function transaction_types()
    {
        return TransactionType::whereIn('code', ['TR', 'OR', 'IV', 'CO', 'BP'])->get(['id', 'code']);
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function sortTable($fieldName)
    {
        if ($this->orderByFieldName !== $fieldName) {
            if (!in_array($fieldName, $this->allowedOrderByFieldName)) {
                $this->orderByFieldName = 'created_at';
            } else {
                $this->orderByFieldName = $fieldName;
            }
            $this->orderBy = 'desc';
        } else {
            if ($this->orderBy === 'desc') {
                $this->orderBy = 'asc';
            } elseif ($this->orderBy === 'asc') {
                $this->orderBy = 'desc';
            }
        }
    }

    public function handleFilterBoxClick($val)
    {
        if (! in_array($val, $this->allowedFilterBox)) {
            $this->activeBox = '';
        } else {
            $this->activeBox = $val;
        }

        $this->reset(['transactionDetails']);
        $this->resetPage();
    }

    #[Computed]
    public function allowed_status()
    {
        return TransactionStatus::whereIn('slug', ['successful', 'pending'])->pluck('id')->toArray();
    }

    public function updatedDateFilter()
    {
        if (! in_array($this->dateFilter, ['past_year', 'past_30_days', 'past_week', 'day'])) {
            $this->dateFilter = 'past_year';
        }

        $fromDate = null;
        $toDate = null;

        $vsFromDate = null;
        $vsToDate = null;
        $dateFilter = $this->dateFilter;
        if ($dateFilter === 'past_year') {
            $fromDate = Carbon::today()->subYear();
            $toDate = Carbon::now();

            $vsFromDate = Carbon::today()->subYears(2);
            $vsToDate = Carbon::now()->subYear();
        } elseif ($dateFilter === 'past_30_days') {
            $fromDate = Carbon::today()->subDays(29);
            $toDate = Carbon::now();

            $vsFromDate = Carbon::today()->subYears();
            $vsToDate = Carbon::now()->subDays(29);
        } elseif ($dateFilter === 'past_week') {
            $fromDate = Carbon::today()->subDays(6);
            $toDate = Carbon::now();

            $vsFromDate = Carbon::today()->subDays(58);
            $vsToDate = Carbon::now()->subDays(29);
        } elseif ($dateFilter === 'day') {
            $fromDate = Carbon::now()->subHours(24);
            $toDate = Carbon::now();

            $vsFromDate = Carbon::today()->subHours(48);
            $vsToDate = Carbon::now()->subDays(24);
        }

        $this->fromDate = $fromDate;
        $this->toDate = $toDate;

        $this->vsFromDate = $vsFromDate;
        $this->vsToDate = $vsToDate;

        $user = $this->user;
        $this->moneySent = $user->outgoing_transactions()->whereIn('transaction_status_id', $this->allowed_status)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum(DB::raw('amount + service_fee'));
        $this->vsMoneySent = $user->outgoing_transactions()->whereIn('transaction_status_id', $this->allowed_status)
            ->whereBetween('created_at', [$vsFromDate, $vsToDate])
            ->sum(DB::raw('amount + service_fee'));

        $this->allCashOutflow = $user->outgoing_transactions()->whereIn('transaction_type_id', $this->transaction_types->pluck('id')->toArray())->whereBetween('created_at', [$this->fromDate, $this->toDate])->count();

        $this->moneyTransfers = $user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->pluck('id')->first())->whereBetween('created_at', [$this->fromDate, $this->toDate])->count();

        $this->orderPayments = $user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->pluck('id')->first())->whereBetween('created_at', [$this->fromDate, $this->toDate])->count();

        $this->cashOutflowCount = $user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'CO')->pluck('id')->first())->whereBetween('created_at', [$this->fromDate, $this->toDate])->count();

        $this->bills = $user->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'BP')->pluck('id')->first())->whereBetween('created_at', [$this->fromDate, $this->toDate])->count();

        $this->resetPage();
    }

    public function handleTableRowClick($txn_no)
    {
        if (!is_null($this->transactionDetails) && $this->transactionDetails['txn_no'] === $txn_no) {
            $this->transactionDetails = null;
            return;
        }

        $transaction = $this->user->outgoing_transactions()
            ->with(['type', 'recipient'])
            ->where('txn_no', $txn_no)
            ->first();

        if (! $transaction) {
            session()->flash('error', 'Error: Transaction not found');
            return;
        }

        $this->transactionDetails = [
            'label' => 'Sent to:',
            'txn_no' => $transaction->txn_no,
            'transaction_type' => $transaction->type->name,
            'amount' => $transaction->amount,
            'service_fee' => $transaction->service_fee,
            'total_amount' => $transaction->amount + $transaction->service_fee,
            'ref_no' => $transaction->ref_no,
            'created_at' => Carbon::parse($transaction->created_at)->timezone('Asia/Manila')->format('F j, Y - g:i A'),
        ];

        if (get_class($transaction->recipient) === Merchant::class) {
            $this->transactionDetails['entity_name'] = $transaction->recipient->name;
            $this->transactionDetails['phone_number'] = $transaction->recipient->account_number;
        } elseif (get_class($transaction->recipient) === User::class) {
            $this->transactionDetails['entity_name'] = $this->format_phone_number($transaction->recipient->phone_number, $transaction->recipient->phone_iso);
            $this->transactionDetails['phone_number'] = '';
        } else {
            $this->transactionDetails['entity_name'] = $transaction->recipient->name;
            $this->transactionDetails['phone_number'] = '';
        }
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $orderByFieldName = $this->orderByFieldName;
        $orderBy = $this->orderBy;

        $cashOutflows = $this->user->outgoing_transactions()
            ->selectRaw('transactions.* , transactions.amount + transactions.service_fee as total_amount')
            ->whereBetween('transactions.created_at', [$this->fromDate, $this->toDate])
            ->with(['type', 'status', 'recipient']);

        if ($this->activeBox && in_array($this->activeBox, $this->allowedFilterBox)) {
            $cashOutflows = $cashOutflows->whereHas('type', function ($query) {
                $query->where('code', $this->activeBox);
            });
        }

        $searchTerm = trim($this->searchTerm);
        if ($searchTerm !== '') {
            $cashOutflows = $cashOutflows->where(function ($query) use ($searchTerm) {
                $query->whereHasMorph('recipient', [User::class], function ($user) use ($searchTerm) {
                    $user->whereHas('profile', function ($profile) use ($searchTerm) {
                        $profile->where('first_name', 'LIKE', '%' . $searchTerm . '%');
                        $profile->orWhere('surname', 'LIKE', '%' . $searchTerm . '%');
                    });
                });
                $query->orWhereHasMorph('recipient', [Merchant::class], function ($merchant) use ($searchTerm) {
                    $merchant->where('name', 'LIKE', '%' . $searchTerm . '%');
                });
                $query->orWhere('ref_no', 'LIKE', '%' . $searchTerm . '%');

                if (is_numeric($searchTerm)) {
                    $query->orWhere('amount', '=', $searchTerm);
                }
            });
        }

        if (! $orderByFieldName || ! in_array($orderByFieldName, $this->allowedOrderByFieldName)) {
            $this->orderByFieldName = 'created_at';
        }

        if (! $orderBy || ! in_array($orderBy, ['desc', 'asc'])) {
            $this->orderBy = 'desc';
        }

        $cashOutflows = $cashOutflows->orderBy($orderByFieldName, $orderBy)->paginate(10);

        $elements = $this->getPaginationElements($cashOutflows);

        return view('admin.transactions.cash-outflow.admin-cash-outflow')->with([
            'cashOutflows' => $cashOutflows,
            'elements' => $elements,
        ]);
    }
}
