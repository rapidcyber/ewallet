<?php

namespace App\Merchant\FinancialTransaction\CashInflow;

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
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantFinancialTransactionCashInflow extends Component
{
    use WithCustomPaginationLinks, WithPagination, WithValidPhoneNumber;

    public Merchant $merchant;

    public $dateFilter = 'past_year';

    public $activeBox = 'ALL';

    public $searchTerm = '';

    public $orderByFieldName = 'created_at';

    public $orderBy = 'desc';

    #[Locked]
    public $transactionDetails = [];

    private $allowedOrderByFieldName = [
        'amount',
        'created_at',
    ];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    #[Computed(persist: true)]
    public function transaction_status()
    {
        return TransactionStatus::where('slug', 'successful')->first()->id;
    }

    #[Computed(persist: true)]
    public function transaction_types()
    {
        return TransactionType::whereIn('code', ['TR', 'OR', 'CI', 'IV'])->get();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();

        if (! in_array($this->dateFilter, ['past_year', 'past_30_days', 'past_week', 'day'])) {
            $this->dateFilter = 'past_year';
        }
    }

    public function updatedActiveBox()
    {
        $this->resetPage();
        $this->reset(['transactionDetails']);

        if (! in_array($this->activeBox, ['ALL', 'TR', 'OR', 'CI', 'IV'])) {
            $this->activeBox = 'ALL';
        }
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();

        $this->reset(['orderByFieldName', 'orderBy']);
    }

    public function sortView($fieldName)
    {
        if ($this->orderByFieldName === $fieldName) {
            $this->orderBy = $this->orderBy === 'desc' ? 'asc' : 'desc';
        } elseif (in_array($fieldName, $this->allowedOrderByFieldName)) {
            $this->orderByFieldName = $fieldName;
            $this->orderBy = 'desc';
        }
    }

    private function get_date()
    {
        if ($this->dateFilter == 'past_year') {
            $date['fromDate'] = Carbon::today()->subYear();
            $date['toDate'] = Carbon::now();

            $date['vsFromDate'] = Carbon::today()->subYears(2);
            $date['vsToDate'] = Carbon::now()->subYear();
        } elseif ($this->dateFilter == 'past_30_days') {
            $date['fromDate'] = Carbon::today()->subDays(30);
            $date['toDate'] = Carbon::now();

            $date['vsFromDate'] = Carbon::today()->subDays(60);
            $date['vsToDate'] = Carbon::now()->subDays(31);
        } elseif ($this->dateFilter == 'past_week') {
            $date['fromDate'] = Carbon::today()->subDays(7);
            $date['toDate'] = Carbon::now();

            $date['vsFromDate'] = Carbon::today()->subDays(14);
            $date['vsToDate'] = Carbon::now()->subDays(8);
        } elseif ($this->dateFilter == 'day') {
            $date['fromDate'] = Carbon::now()->subHours(24);
            $date['toDate'] = Carbon::now();

            $date['vsFromDate'] = Carbon::today()->subHours(48);
            $date['vsToDate'] = Carbon::now()->subDays(24);
        }

        return $date;
    }

    public function handleTableRowClick($txn_no)
    {
        if (!empty($this->transactionDetails) && $this->transactionDetails['txn_no'] == $txn_no) {
            $this->transactionDetails = [];
            return;
        }

        $transaction = $this->merchant->incoming_transactions()
            ->with(['type', 'sender'])
            ->where('txn_no', $txn_no)
            ->first();

        if (! $transaction) {
            $this->transactionDetails = [];
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

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        $date = $this->get_date();

        $moneyReceived = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->sum('amount');
        $vsMoneyReceived = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$date['vsFromDate'], $date['vsToDate']])->sum('amount');

        $allCashInflowCount = $this->merchant->incoming_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();
        $moneyTransferCount = $this->merchant->incoming_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->count();
        $orderPaymentsCount = $this->merchant->incoming_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->count();
        $invoicePaymentsCount = $this->merchant->incoming_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->count();
        $cashInCount = $this->merchant->incoming_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'CI')->first()->id)->count();

        $cashInflows = $this->merchant->incoming_transactions()->with(['recipient', 'type', 'status', 'sender'])
            ->whereBetween('created_at', [$date['fromDate'], $date['toDate']]);

        if ($this->activeBox != 'ALL') {
            $cashInflows = $cashInflows->where('transaction_type_id', $this->transaction_types->where('code', $this->activeBox)->first()->id);
        }

        if ($this->searchTerm) {
            $cashInflows = $cashInflows->where(function ($query) {
                $query->where('ref_no', 'LIKE', '%' . $this->searchTerm . '%');
                $query->orWhereHasMorph('sender', [User::class], function ($user) {
                    $user->where('phone_number', 'LIKE', '%' . $this->searchTerm . '%');
                });
                $query->orWhereHasMorph('sender', [Merchant::class], function ($merchant) {
                    $merchant->where('name', 'LIKE', '%' . $this->searchTerm . '%');
                    $merchant->orWhere('account_number', 'LIKE', '%' . $this->searchTerm . '%');
                });
                $query->orWhereHasMorph('sender', [TransactionProvider::class], function ($provider) {
                    $provider->where('name', 'LIKE', '%' . $this->searchTerm . '%');
                });
            });
        }

        $cashInflows = $cashInflows->orderBy($this->orderByFieldName, $this->orderBy)->paginate(20);
        $elements = $this->getPaginationElements($cashInflows);

        return view('merchant.financial-transaction.cash-inflow.merchant-financial-transaction-cash-inflow', [
            'moneyReceived' => $moneyReceived,
            'vsMoneyReceived' => $vsMoneyReceived,
            'allCashInflowCount' => $allCashInflowCount,
            'moneyTransferCount' => $moneyTransferCount,
            'orderPaymentsCount' => $orderPaymentsCount,
            'invoicePaymentsCount' => $invoicePaymentsCount,
            'cashInCount' => $cashInCount,
            'cashInflows' => $cashInflows,
            'elements' => $elements,

        ]);
    }
}
