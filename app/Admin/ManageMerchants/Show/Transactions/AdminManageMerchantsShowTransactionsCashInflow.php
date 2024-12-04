<?php

namespace App\Admin\ManageMerchants\Show\Transactions;

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

class AdminManageMerchantsShowTransactionsCashInflow extends Component
{
    use WithCustomPaginationLinks, WithPagination, WithValidPhoneNumber;

    public Merchant $merchant;
    public Transaction $transaction;
    public $dateFilter = 'none';
    public $orderBy = 'desc';
    public $orderByFieldName = 'created_at';
    public $activeBox = 'ALL';
    public $searchTerm = '';
    public $transactionDetails;
    private $allowedOrderByFieldName = [
        'amount',
        'created_at',
    ];

    public $allowedTransactionTypes = ['TR', 'OR', 'CI', 'IV'];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    #[Computed]
    public function latest_balance()
    {
        return $this->merchant->latest_balance()->first()->amount ?? 0;
    }

    #[Computed]
    public function transaction_types()
    {
        return TransactionType::select(['id', 'code'])->toBase()->get();
    }

    #[Computed]
    public function successful_status()
    {
        return TransactionStatus::where('slug', 'successful')->first()->id;
    }

    public function updatedDateFilter()
    {
        $this->resetPage();

        if (! in_array($this->dateFilter, ['none', 'past_year', 'past_6_months', 'past_30_days', 'past_week', 'past_24_hours'])) {
            $this->dateFilter = 'none';
        }
    }

    public function updatedActiveBox()
    {
        if (!in_array($this->activeBox, ['ALL', 'TR', 'OR', 'CI', 'IV'])) {
            $this->activeBox = 'ALL';
        }

        $this->resetPage();
        $this->reset(['orderByFieldName', 'orderBy']);
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();

        $this->reset(['orderByFieldName', 'orderBy']);
    }

    public function sortTable($fieldName)
    {
        if ($this->orderByFieldName === $fieldName) {
            $this->orderBy = $this->orderBy === 'desc' ? 'asc' : 'desc';
        } elseif (in_array($fieldName, $this->allowedOrderByFieldName)) {
            $this->orderByFieldName = $fieldName;
            $this->orderBy = 'desc';
        }
    }

    public function handleTableRowClick($txn_no)
    {
        $transaction = $this->merchant->incoming_transactions()
            ->where('txn_no', $txn_no)
            ->with(['type', 'sender'])
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
            'created_at' => Carbon::parse($transaction->created_at)->timezone('Asia/Manila')->format('F j, Y - g:i A'),
        ];

        $this->dispatch('showTransactionDetails');
    }

    private function get_date()
    {
        if ($this->dateFilter == 'past_year') {
            $date['fromDate'] = Carbon::today()->subYear();
            $date['toDate'] = Carbon::now();
        } elseif ($this->dateFilter == 'past_6_months') {
            $date['fromDate'] = Carbon::today()->subMonths(6);
            $date['toDate'] = Carbon::now();  
        } elseif ($this->dateFilter == 'past_30_days') {
            $date['fromDate'] = Carbon::today()->subDays(30);
            $date['toDate'] = Carbon::now();
        } elseif ($this->dateFilter == 'past_week') {
            $date['fromDate'] = Carbon::today()->subDays(7);
            $date['toDate'] = Carbon::now();
        } elseif ($this->dateFilter == 'past_24_hours') {
            $date['fromDate'] = Carbon::now()->subHours(24);
            $date['toDate'] = Carbon::now();
        } else {
            $date = null;
        }

        return $date;
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $date = $this->get_date();

        if (! $date) {
            $allCashInflowCount = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->successful_status)->count();
            $moneyTransferCount = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->count();
            $orderPaymentsCount = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->count();
            $invoicePaymentsCount = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->count();
            $cashInCount = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->successful_status)->where('transaction_type_id', $this->transaction_types->where('code', 'CI')->first()->id)->count();
        } else {
            $allCashInflowCount = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->successful_status)->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();
            $moneyTransferCount = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->successful_status)->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->count();
            $orderPaymentsCount = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->successful_status)->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->count();
            $invoicePaymentsCount = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->successful_status)->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->count();
            $cashInCount = $this->merchant->incoming_transactions()->where('transaction_status_id', $this->successful_status)->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'CI')->first()->id)->count();
        } 

        $cashInflows = $this->merchant->incoming_transactions()
            ->with(['sender', 'type', 'status']);

        if ($this->dateFilter != 'none') {
            $cashInflows = $cashInflows->whereBetween('created_at', [$date['fromDate'], $date['toDate']]);
        }

        if ($this->activeBox != 'ALL') {
            $cashInflows = $cashInflows->where('transaction_type_id', $this->transaction_types->where('code', $this->activeBox)->first()->id);
        }

        if ($this->searchTerm) {
            $cashInflows = $cashInflows->where(function ($query) {
                $query->where('ref_no', 'LIKE', '%' . $this->searchTerm . '%');
                $query->orWhereHasMorph('sender', [User::class], function ($user) {
                    $user->where('phone_number', 'LIKE', '%' . $this->searchTerm . '%');
                });
                $query->orWhereHasMorph('sender', [Merchant::class, TransactionProvider::class], function ($merchant) {
                    $merchant->where('name', 'LIKE', '%' . $this->searchTerm . '%');
                });
            });
        }

        $cashInflows = $cashInflows
            ->orderBy($this->orderByFieldName, $this->orderBy)
            ->select(
                [
                    'id',
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

        $elements = $this->getPaginationElements($cashInflows);

        return view('admin.manage-merchants.show.transactions.admin-manage-merchants-show-transactions-cash-inflow', [
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
