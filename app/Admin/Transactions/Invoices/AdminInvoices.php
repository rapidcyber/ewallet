<?php

namespace App\Admin\Transactions\Invoices;

use App\Models\InvoiceItem;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithValidPhoneNumber;
use Carbon\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminInvoices extends Component
{
    use WithCustomPaginationLinks, WithPagination, WithValidPhoneNumber;

    #[Locked]
    public $color = 'primary';

    public Merchant $merchant;

    public $dateFilter = 'past_year';

    public $moneyReceived = 0;

    public $vsMoneyReceived = 0;

    public $activeBox = '';

    public $searchTerm = '';

    public $orderByFieldName = 'created_at';

    public $orderBy = 'desc';

    protected $allowedOrderByFieldName = ['final_price', 'created_at', 'due_date'];

    protected $allowedStatus = ['', 'paid', 'partial', 'unpaid', 'overdue'];

    #[Locked]
    public $invoice_data;

    public function mount()
    {
        $this->merchant = Merchant::find(1);
    }

    public function show_invoice($invoice_no)
    {
        if ($this->invoice_data && $this->invoice_data['invoice_no'] === $invoice_no) {
            return $this->invoice_data = null;
        }

        $invoice = $this->merchant->outgoing_invoices()
            ->where('invoice_no', $invoice_no)
            ->with(['items', 'inclusions', 'logs' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->first();

        if (!$invoice) {
            session()->flash('error', 'Error: Invoice not found');
        }

        $this->invoice_data = [];

        $this->invoice_data['invoice_no'] = $invoice->invoice_no;
        $subtotal = $invoice->items->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
        $this->invoice_data['sub_total'] = $subtotal;

        $this->invoice_data['items'] = [];
        $this->invoice_data['inclusions'] = [];

        foreach($invoice->items as $item) {
            $this->invoice_data['items'][] = [
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total' => $item->price * $item->quantity
            ];
        }
        foreach($invoice->inclusions as $inclusion) {
            $name = $inclusion->name === 'vat' ? 'VAT (12%)' : $inclusion->name;
            $this->invoice_data['inclusions'][] = [
                'name' => $name,
                'amount' => $inclusion->amount
            ];
        }

        $this->invoice_data['total'] = $invoice->total_price;
        $this->invoice_data['minimum_partial'] = $invoice->minimum_partial ?? 0;

        $this->invoice_data['logs'] = [];

        foreach($invoice->logs as $log) {            
            $this->invoice_data['logs'][] = [
                'message' => $log->message,
                'created_at' => Carbon::parse($log->created_at)->format('M d, Y h:i A'),
            ];
        }
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();

        $this->reset(['orderByFieldName', 'orderBy']);
    }

    public function updatedDateFilter()
    {
        $this->resetPage();

        if (! in_array($this->dateFilter, ['past_year', 'past_30_days', 'past_week', 'day'])) {
            $this->dateFilter = 'past_year';
        }
    }

    public function sortTable($fieldName)
    {
        if ($this->orderByFieldName !== $fieldName) {
            $this->orderByFieldName = $fieldName;
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
        if (! in_array($val, $this->allowedStatus)) {
            return $this->activeBox = '';
        }

        return $this->activeBox = $val;
    }

    public function updatedActiveBox()
    {
        $this->resetPage();

        if (! in_array($this->activeBox, $this->allowedStatus)) {
            $this->activeBox = 'ALL';
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

    #[Layout('layouts.admin')]
    public function render()
    {
        $date = $this->get_date();

        $incomingInvoices = $this->merchant->outgoing_invoices()
            ->whereHas('transactions')
            ->whereBetween('created_at', [$date['fromDate'], $date['toDate']])
            ->pluck('invoice_no')
            ->toArray();
        $this->moneyReceived = Transaction::whereIn('ref_no', $incomingInvoices)->sum('amount');

        $vsIncomingInvoices = $this->merchant->outgoing_invoices()
            ->whereHas('transactions')
            ->whereBetween('created_at', [$date['vsFromDate'], $date['vsToDate']])
            ->pluck('invoice_no')
            ->toArray();
        $this->vsMoneyReceived = Transaction::whereIn('ref_no', $vsIncomingInvoices)->sum('amount');

        $allInvoicesCount = $this->merchant->outgoing_invoices()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();
        $unpaidCount = $this->merchant->outgoing_invoices()->where('status', 'unpaid')->where('due_date', '>=', now()->timezone('Asia/Manila'))->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();
        $partiallyPaidCount = $this->merchant->outgoing_invoices()->where('status', 'partial')->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();
        $fullyPaidCount = $this->merchant->outgoing_invoices()->where('status', 'paid')->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();
        $overdueCount = $this->merchant->outgoing_invoices()->where('status', 'unpaid')->where('due_date', '<', now()->timezone('Asia/Manila'))->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();

        $subquery = $this->merchant->outgoing_invoices()
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
                        ELSE users.phone_number
                    END as recipient"),

            ])
            ->whereBetween('invoices.created_at', [$date['fromDate'], $date['toDate']])
            ->leftjoin('invoice_inclusions', 'invoice_inclusions.invoice_id', '=', 'invoices.id')
            ->leftjoin('merchants', function ($join) {
                $join->on('merchants.id', '=', 'invoices.recipient_id');
                $join->where('invoices.recipient_type', '=', Merchant::class);
            })
            ->leftjoin('users', function ($join) {
                $join->on('users.id', '=', 'invoices.recipient_id');
                $join->where('invoices.recipient_type', '=', User::class);
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

        if ($this->searchTerm) {
            $invoices = $invoices->where(function ($query) {
                $query->where('invoice_no', 'LIKE', '%'.$this->searchTerm.'%');
                $query->orWhere('recipient', 'LIKE', '%'.$this->searchTerm.'%');
            });
        }

        if ($this->activeBox && in_array($this->activeBox, $this->allowedStatus)) {
            if ($this->activeBox === 'overdue') {
                $invoices = $invoices->where('due_date', '<', now()->timezone('Asia/Manila'));
            } elseif ($this->activeBox === 'unpaid') {
                $invoices = $invoices->where('status', 'unpaid')->where('due_date', '>=', now()->timezone('Asia/Manila'));
            } else {
                $invoices = $invoices->where('status', $this->activeBox);
            }

        }

        if (! $this->orderByFieldName || ! in_array($this->orderByFieldName, $this->allowedOrderByFieldName)) {
            $this->orderByFieldName = 'created_at';
        }

        if (! $this->orderBy || ! in_array($this->orderBy, ['desc', 'asc'])) {
            $this->orderBy = 'desc';
        }

        $invoices = $invoices->orderBy($this->orderByFieldName, $this->orderBy)->paginate(20);
        $elements = $this->getPaginationElements($invoices);

        return view('merchant.financial-transaction.invoices.merchant-invoices', [
            'allInvoicesCount' => $allInvoicesCount,
            'unpaidCount' => $unpaidCount,
            'partiallyPaidCount' => $partiallyPaidCount,
            'fullyPaidCount' => $fullyPaidCount,
            'overdueCount' => $overdueCount,
            'elements' => $elements,
            'invoices' => $invoices,
        ]);
    }
}
