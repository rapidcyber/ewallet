<?php

namespace App\Admin\ManageMerchants\Show\Transactions;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageMerchantsShowTransactionsInvoices extends Component
{
    use WithCustomPaginationLinks, WithPagination;

    public Merchant $merchant;

    public Transaction $transaction;

    #[Locked]
    public $transaction_types;

    public $dateFilter = '';

    public $orderBy = 'desc';

    public $orderByFieldName = 'created_at';

    public $activeBox = 'unpaid';

    public $searchTerm = '';

    protected $allowedOrderByFieldName = [
        'final_price',
        'created_at',
        'due_date'
    ];

    protected $allowedDateFilter = [
        '',
        'past_year',
        'past_6_months',
        'past_30_days',
        'past_week',
        'past_24_hours',
    ];

    protected $allowedInvoiceStatus = ['unpaid', 'paid', 'partial'];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    #[Computed]
    public function balance_amount()
    {
        return $this->merchant->latest_balance()->first()->amount ?? 0;
    }

    public function updatedDateFilter()
    {
        $this->resetPage();

        if (!in_array($this->dateFilter, $this->allowedDateFilter)) {
            $this->dateFilter = '';
        }
    }

    public function updatedActiveBox()
    {
        $this->resetPage();

        if (! in_array($this->activeBox, $this->allowedInvoiceStatus)) {
            $this->activeBox = 'unpaid';
        }
    }

    public function sortTable($fieldName)
    {
        if ($this->orderByFieldName === $fieldName) {
            $this->orderBy = $this->orderBy === 'desc' ? 'asc' : 'desc';
        } elseif (in_array($fieldName, $this->allowedOrderByFieldName)) {
            $this->orderByFieldName = $fieldName;
            $this->orderBy = 'desc';
        } else {
            $this->orderByFieldName = 'created_at';
            $this->orderBy = 'desc';
        }
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();

        $this->reset(['orderByFieldName', 'orderBy']);
    }

    #[Computed]
    public function unpaidInvoicesCount()
    {
        $count =  $this->merchant->outgoing_invoices()->where('status',  'unpaid');
        
        if ($this->get_from_date) {
            $count = $count->whereBetween('created_at', [$this->get_from_date, now()]);
        }

        return $count->count();
    }

    #[Computed]
    public function partialInvoicesCount() 
    {
        $count =  $this->merchant->outgoing_invoices()->where('status',  'partial');
        
        if ($this->get_from_date) {
            $count = $count->whereBetween('created_at', [$this->get_from_date, now()]);
        }

        return $count->count();
    }

    #[Computed]
    public function paidInvoicesCount() 
    {
        $count =  $this->merchant->outgoing_invoices()->where('status',  'paid');
        
        if ($this->get_from_date) {
            $count = $count->whereBetween('created_at', [$this->get_from_date, now()]);
        }

        return $count->count();
    }

    #[Computed]
    public function get_from_date()
    {
        if ($this->dateFilter == 'past_year') {
            $date = Carbon::today()->subYear();
        } elseif ($this->dateFilter == 'past_30_days') {
            $date = Carbon::today()->subDays(30);
        } elseif ($this->dateFilter == 'past_week') {
            $date = Carbon::today()->subDays(7);
        } elseif ($this->dateFilter == 'day') {
            $date = Carbon::now()->subHours(24);
        } else {
            $date = null;
        }

        return $date;
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $subquery = Invoice::whereHasMorph('sender', [Merchant::class], function ($query) {
            $query->where('id', $this->merchant->id);
        })
            ->select([
                'invoices.id',
                'invoices.invoice_no',
                'invoices.created_at',
                'invoices.due_date',
                'invoices.status',
                DB::raw('COALESCE(SUM(
                    CASE
                        WHEN invoice_inclusions.deduct = 1 THEN invoice_inclusions.amount
                    END
                ),0) as deductions'),
                DB::raw('COALESCE(SUM(
                    CASE
                        WHEN invoice_inclusions.deduct = 0 THEN invoice_inclusions.amount
                    END
                ),0) as surcharge'),
                DB::raw("
                    CASE
                        WHEN merchants.name IS NOT NULL THEN merchants.name
                        WHEN profiles.first_name IS NOT NULL AND profiles.surname IS NOT NULL THEN CONCAT(profiles.first_name, ' ', profiles.surname)
                    END as recipient"),

            ])
            ->leftjoin('invoice_inclusions', 'invoice_inclusions.invoice_id', '=', 'invoices.id')
            ->leftJoinSub(function ($query) {
                $query->from('merchants')
                    ->select('id', 'name');
            }, 'merchants', function ($join) {
                $join->on('invoices.recipient_id', '=', 'merchants.id')
                    ->where('invoices.recipient_type', '=', Merchant::class);
            })
            ->leftJoinSub(function ($query) {
                $query->from('users')
                    ->select('id');
            }, 'users', function ($join) {
                $join->on('invoices.recipient_id', '=', 'users.id')
                    ->where('invoices.recipient_type', '=', User::class)
                    ->leftjoin('profiles', 'profiles.user_id', '=', 'users.id');
            })

            ->groupBy(
                'invoices.id',
                'invoices.invoice_no',
                'invoices.recipient_id',
                'recipient',
                'invoices.created_at',
                'invoices.due_date',
                'invoices.status',
            );

        $mainquery = InvoiceItem::select([
            'invoices.*',
            DB::raw('SUM(invoice_items.price * invoice_items.quantity) as total_price'),
            DB::raw('(SUM(invoice_items.price * invoice_items.quantity) - invoices.deductions) + invoices.surcharge as final_price'),
        ])
            ->rightJoinSub($subquery, 'invoices', function (JoinClause $join) {
                $join->on('invoice_items.invoice_id', 'invoices.id');
            })
            ->groupBy(
                'invoices.id',
                'invoices.invoice_no',
                'invoices.recipient',
                'invoices.deductions',
                'invoices.surcharge',
                'invoices.created_at',
                'invoices.due_date',
                'invoices.status',
            );

        $invoices = DB::table(DB::raw("({$mainquery->toSql()}) as invoices_with_final_price"))
            ->mergeBindings($mainquery->getQuery())
            ->select(['*']);

        if (!in_array($this->activeBox, $this->allowedInvoiceStatus)) {
            $this->activeBox = 'unpaid';
        }
        $invoices = $invoices->where('status', $this->activeBox);

        if ($this->get_from_date) {
            $invoices = $invoices->whereBetween('created_at', [$this->get_from_date, now()]);
        }

        if ($this->searchTerm) {
            $invoices = $invoices->where(function ($query) {
                $query->where('recipient', 'like', '%' . $this->searchTerm . '%');
                $query->orWhere('invoice_no', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $invoices = $invoices->orderBy($this->orderByFieldName, $this->orderBy)->paginate(20);
        $elements = $this->getPaginationElements($invoices);

        return view('admin.manage-merchants.show.transactions.admin-manage-merchants-show-transactions-invoices', [
            'invoices' => $invoices,
            'elements' => $elements,
        ]);
    }
}
