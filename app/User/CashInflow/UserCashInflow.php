<?php

namespace App\User\CashInflow;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionProvider;
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

class UserCashInflow extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithValidPhoneNumber;

    public User $user;

    public $dateFilter = 'past_year';

    public $activeBox = '';

    public $searchTerm = '';

    public $orderByFieldName = 'created_at';

    public $orderBy = 'desc';

    public $transactionDetails = null;
    public $moneyReceived = 0;

    public $vsMoneyReceived = 0;

    public $allCashInflowCount = 0;

    public $moneyTransferCount = 0;

    public $cashInCount = 0;

    public $invoiceCount = 0;

    public $salaryCount = 0;

    protected $allowedOrderByFieldName = [
        'amount',
        'created_at',
    ];

    protected $allowedFilterBox = [
        '',
        'transfer',
        'cash_in',
        'payroll_salary',
    ];

    #[Locked]
    public $color = 'red';

    public function mount()
    {
        $this->user = User::find(auth()->id());

        $this->updatedDateFilter();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    #[Computed(persist: true)]
    public function transaction_types()
    {
        return TransactionType::whereIn('code', ['TR', 'CI', 'IV', 'PS'])->toBase()->get();
    }

    #[Computed(persist: true)]
    public function successful_status()
    {
        return TransactionStatus::where('slug', 'successful')->first()->id;
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
        $this->reset(['transactionDetails']);

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

        $user = $this->user;

        $type_transfer = $this->transaction_types->where('code', 'TR')->first();
        $type_cashin = $this->transaction_types->where('code', 'CI')->first();
        $type_invoice = $this->transaction_types->where('code', 'IV')->first();
        $type_salary = $this->transaction_types->where('code', 'PS')->first();

        $this->moneyReceived = $user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->whereBetween('created_at', [$fromDate, $toDate])->sum('amount');
        $this->vsMoneyReceived = $user->incoming_transactions()->where('transaction_status_id', $this->successful_status)->whereBetween('created_at', [$vsFromDate, $vsToDate])->sum('amount');

        $this->allCashInflowCount = $user->incoming_transactions()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();
        $this->moneyTransferCount = $user->incoming_transactions()
            ->where('transaction_type_id', $type_transfer->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();
        $this->cashInCount = $user->incoming_transactions()
            ->where('transaction_type_id', $type_cashin->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();
        $this->salaryCount = $user->incoming_transactions()
            ->where('transaction_type_id', $type_salary->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();
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
        if (!in_array($val, $this->allowedFilterBox)) {
            $this->activeBox = '';
        } else {
            $this->activeBox = $val;
        }
        $this->resetPage();
        $this->reset(['transactionDetails']);
    }

    public function handleTableRowClick($txn_no)
    {
        if (!is_null($this->transactionDetails) && $txn_no === $this->transactionDetails['txn_no']) {
            $this->transactionDetails = null;
            return;
        }

        $transaction = $this->user->incoming_transactions()
            ->select([
                'id',
                'sender_id',
                'sender_type',
                'txn_no',
                'ref_no',
                'amount',
                'transaction_type_id',
                'created_at',
            ])
            ->with(['type', 'sender'])
            ->where('txn_no', $txn_no)
            ->first();

        if (! $transaction) {
            session()->flash('error', 'Transaction not found');
            return;
        }

        $this->transactionDetails = [
            'label' => 'Sent by:',
            'txn_no' => $transaction->txn_no,
            'transaction_type' => $transaction->type->name,
            'amount' => $transaction->amount,
            'total_amount' => $transaction->amount,
            'ref_no' => $transaction->ref_no,
            'created_at' => Carbon::parse($transaction->created_at)->timezone('Asia/Manila')->format('F j, Y - g:i A'),
        ];

        if (get_class($transaction->sender) === Merchant::class) {
            $this->transactionDetails['entity_name'] = $transaction->sender->name;
            $this->transactionDetails['phone_number'] = $transaction->sender->account_number;
        } elseif (get_class($transaction->sender) === User::class) {
            $this->transactionDetails['entity_name'] = $this->format_phone_number($transaction->sender->phone_number, $transaction->sender->phone_iso);
            $this->transactionDetails['phone_number'] = '';
        } else {
            $this->transactionDetails['entity_name'] = $transaction->sender->name;
            $this->transactionDetails['phone_number'] = '';
        }
    }

    #[Layout('layouts.user')]
    public function render()
    {
        $fromDate = null;
        $toDate = null;

        $dateFilter = $this->dateFilter;
        if ($dateFilter === 'past_year') {
            $fromDate = Carbon::today()->subYear();
            $toDate = Carbon::now();
        } elseif ($dateFilter === 'past_30_days') {
            $fromDate = Carbon::today()->subDays(29);
            $toDate = Carbon::now();
        } elseif ($dateFilter === 'past_week') {
            $fromDate = Carbon::today()->subDays(6);
            $toDate = Carbon::now();
        } elseif ($dateFilter === 'day') {
            $fromDate = Carbon::now()->subHours(24);
            $toDate = Carbon::now();
        }

        $orderByFieldName = $this->orderByFieldName;
        $orderBy = $this->orderBy;

        $cashInflows = $this->user->incoming_transactions()
            ->whereBetween('transactions.created_at', [$fromDate, $toDate])
            ->with(['type', 'status', 'sender']);

        if ($this->activeBox && in_array($this->activeBox, $this->allowedFilterBox)) {
            $cashInflows = $cashInflows->whereHas('type', function ($query) {
                $query->where('slug', $this->activeBox);
            });
        }

        $searchTerm = trim($this->searchTerm);

        if ($searchTerm !== '') {
            $cashInflows = $cashInflows->where(function ($query) use ($searchTerm) {
                $query->whereHasMorph('sender', [User::class], function ($user) use ($searchTerm) {
                    $user->where('phone_number', 'LIKE', '%' . $searchTerm . '%');
                });
                $query->orWhereHasMorph('sender', [Merchant::class], function ($merchant) use ($searchTerm) {
                    $merchant->where('name', 'LIKE', '%' . $searchTerm . '%');
                    $merchant->orWhere('account_number', 'LIKE', '%' . $this->searchTerm . '%');
                });
                $query->orWhereHasMorph('sender', [TransactionProvider::class], function ($provider) {
                    $provider->where('name', 'LIKE', '%' . $this->searchTerm . '%');
                });
                $query->orWhere('txn_no', 'LIKE', '%' . $searchTerm . '%');
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

        $cashInflows = $cashInflows->orderBy($orderByFieldName, $orderBy)->paginate(10);

        $elements = $this->getPaginationElements($cashInflows);

        return view('user.cash-inflow.user-cash-inflow')->with([
            'cashInflows' => $cashInflows,
            'elements' => $elements,
        ]);
    }
}
