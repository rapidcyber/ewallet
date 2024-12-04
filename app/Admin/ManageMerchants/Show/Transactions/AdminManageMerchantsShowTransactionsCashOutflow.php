<?php

namespace App\Admin\ManageMerchants\Show\Transactions;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithValidPhoneNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageMerchantsShowTransactionsCashOutflow extends Component
{
    use WithCustomPaginationLinks, WithPagination, WithValidPhoneNumber;

    public User $user;
    public Merchant $merchant;
    public Transaction $transaction;
    #[Locked]
    public $transaction_types;
    public $dateFilter = 'none';
    public $orderBy = 'desc';
    public $orderByFieldName = 'created_at';
    public $activeBox = 'ALL';
    public $searchTerm = '';
    private $allowedOrderByFieldName = [
        'total_amount',
        'created_at',
    ];
    public $transactionDetails;

    protected $allowedTransactionTypes = ['ALL', 'TR', 'OR', 'IV', 'CO', 'BP', 'PS'];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant->load('latest_balance');
        $this->transaction_types = TransactionType::whereIn('code', $this->allowedTransactionTypes)->get(['id', 'code']);
    }

    public function updatedDateFilter()
    {
        if (!in_array($this->dateFilter, ['none', 'past_24_hours', 'past_week', 'past_30_days', 'past_6_months', 'past_year'])) {
            $this->dateFilter = 'none';
        }

        $this->resetPage();
    }

    public function updatedActiveBox()
    {
        if (! in_array($this->activeBox, $this->allowedTransactionTypes)) {
            $this->activeBox = 'ALL';
        }

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

    public function updatedSearchTerm()
    {
        $this->resetPage();

        $this->reset(['orderByFieldName', 'orderBy']);
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

    public function handleTableRowClick($txn_no)
    {
        $transaction = $this->merchant->outgoing_transactions()
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
        $date = $this->get_date();

        if ($this->dateFilter === 'none') {
            $allCashOutflowCount = $this->merchant->outgoing_transactions()->count();
            $moneyTransferCount = $this->merchant->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->count();
            $orderPaymentsCount = $this->merchant->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->count();
            $invoicePaymentsCount = $this->merchant->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->count();
            $cashOutCount = $this->merchant->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'CO')->first()->id)->count();
            $billPaymentsCount = $this->merchant->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'BP')->first()->id)->count();
            $payrollPaymentsCount = $this->merchant->outgoing_transactions()->where('transaction_type_id', $this->transaction_types->where('code', 'PS')->first()->id)->count();
        } else {
            $allCashOutflowCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();
            $moneyTransferCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->count();
            $orderPaymentsCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->count();
            $invoicePaymentsCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->count();
            $cashOutCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'CO')->first()->id)->count();
            $billPaymentsCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'BP')->first()->id)->count();
            $payrollPaymentsCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'PS')->first()->id)->count();
        }
        

        $cashOutflows = $this->merchant->outgoing_transactions()
            ->selectRaw('transactions.* , transactions.amount + transactions.service_fee as total_amount')
            ->with(['sender', 'status', 'recipient', 'type']);
        
        if ($this->dateFilter !== 'none') {
            $cashOutflows = $cashOutflows->whereBetween('created_at', [$date['fromDate'], $date['toDate']]);
        }

        if ($this->activeBox != 'ALL') {
            $cashOutflows = $cashOutflows->where('transaction_type_id', $this->transaction_types->where('code', $this->activeBox)->first()->id);
        }

        if ($this->searchTerm) {
            $cashOutflows = $cashOutflows->where(function ($query) {
                $query->where('ref_no', 'LIKE', '%' . $this->searchTerm . '%');
                $query->orWhereHasMorph('recipient', [User::class], function ($user) {
                    $user->where('phone_number', 'LIKE', '%' . $this->searchTerm . '%');
                });
                $query->orWhereHasMorph('recipient', [Merchant::class], function ($merchant) {
                    $merchant->where('name', 'LIKE', '%' . $this->searchTerm . '%');
                });
            });
        }

        $cashOutflows = $cashOutflows->orderBy($this->orderByFieldName, $this->orderBy)->paginate(20);
        $elements = $this->getPaginationElements($cashOutflows);

        return view('admin.manage-merchants.show.transactions.admin-manage-merchants-show-transactions-cash-outflow', [
            'allCashOutflowCount' => $allCashOutflowCount,
            'moneyTransferCount' => $moneyTransferCount,
            'orderPaymentsCount' => $orderPaymentsCount,
            'invoicePaymentsCount' => $invoicePaymentsCount,
            'cashOutCount' => $cashOutCount,
            'billPaymentsCount' => $billPaymentsCount,
            'payrollPaymentsCount' => $payrollPaymentsCount,
            'cashOutflows' => $cashOutflows,
            'elements' => $elements,
        ]);
    }
}
