<?php

namespace App\Merchant\FinancialTransaction\CashOutflow;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionProvider;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithValidPhoneNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantFinancialTransactionCashOutflow extends Component
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

    #[Locked]
    public $can_approve = false;

    protected $allowedOrderByFieldName = [
        'total_amount',
        'created_at',
    ];

    protected $allowedTransactionTypes = ['TR', 'OR', 'IV', 'CO', 'BP', 'PS'];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        if (Gate::allows('merchant-cash-outflow', [$this->merchant, 'approve'])) {  
            $this->can_approve = true;
        }
    }

    #[Computed(persist: true)]
    public function transaction_types()
    {
        return TransactionType::whereIn('code', $this->allowedTransactionTypes)->toBase()->get();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();

        $this->reset(['transactionDetails']);

        if (! in_array($this->dateFilter, ['past_year', 'past_30_days', 'past_week', 'day'])) {
            $this->dateFilter = 'past_year';
        }
    }

    public function updatedActiveBox()
    {
        $this->resetPage();

        $this->reset(['transactionDetails']);

        if (! in_array($this->activeBox, $this->allowedTransactionTypes)) {
            $this->activeBox = 'ALL';
        }
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

        $transaction = $this->merchant->outgoing_transactions()
            ->with(['type', 'recipient'])
            ->where('txn_no', $txn_no)
            ->first();

        if (! $transaction) {
            $this->transactionDetails = [];
            return session()->flash('error', 'Error: Transaction not found');
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

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        $date = $this->get_date();

        $moneySent = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->sum(DB::raw('amount + service_fee'));
        $vsMoneySent = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['vsFromDate'], $date['vsToDate']])->sum(DB::raw('amount + service_fee'));

        $allCashOutflowCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();
        $moneyTransferCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'TR')->first()->id)->count();
        $orderPaymentsCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'OR')->first()->id)->count();
        $invoicePaymentsCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'IV')->first()->id)->count();
        $cashOutCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'CO')->first()->id)->count();
        $billPaymentsCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'BP')->first()->id)->count();
        $payrollPaymentsCount = $this->merchant->outgoing_transactions()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->where('transaction_type_id', $this->transaction_types->where('code', 'PS')->first()->id)->count();

        $cashOutflows = $this->merchant->outgoing_transactions()->with(['status', 'type', 'recipient'])
            ->selectRaw('transactions.* , transactions.amount + transactions.service_fee as total_amount')
            ->whereBetween('created_at', [$date['fromDate'], $date['toDate']]);

        if ($this->activeBox != 'ALL') {
            $cashOutflows = $cashOutflows->where('transaction_type_id', $this->transaction_types->where('code', $this->activeBox)->first()->id);
        }

        if ($this->searchTerm) {
            $cashOutflows = $cashOutflows->where(function ($query) {
                $query->where('ref_no', 'LIKE', '%'.$this->searchTerm.'%');
                $query->orWhereHasMorph('recipient', [User::class], function ($user) {
                    $user->where('phone_number', 'LIKE', '%'.$this->searchTerm.'%');
                });
                $query->orWhereHasMorph('recipient', [Merchant::class], function ($merchant) {
                    $merchant->where('name', 'LIKE', '%'.$this->searchTerm.'%');
                    $merchant->orWhere('account_number', 'LIKE', '%'.$this->searchTerm.'%');
                });
                $query->orWhereHasMorph('recipient', [TransactionProvider::class], function ($provider) {
                    $provider->where('name', 'LIKE', '%'.$this->searchTerm.'%');
                });
            });
        }

        $cashOutflows = $cashOutflows->orderBy($this->orderByFieldName, $this->orderBy)->paginate(20);
        $elements = $this->getPaginationElements($cashOutflows);

        return view('merchant.financial-transaction.cash-outflow.merchant-financial-transaction-cash-outflow', [
            'moneySent' => $moneySent,
            'vsMoneySent' => $vsMoneySent,
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
