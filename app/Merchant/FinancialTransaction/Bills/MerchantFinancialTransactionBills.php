<?php

namespace App\Merchant\FinancialTransaction\Bills;

use App\Models\Bill;
use App\Models\Merchant;
use App\Models\SystemService;
use App\Traits\WithCustomPaginationLinks;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantFinancialTransactionBills extends Component
{
    use WithCustomPaginationLinks, WithPagination;

    public Merchant $merchant;

    public Bill $billDetails;

    public $dateFilter = 'past_year';

    public $activeBox = 'ALL';

    public $searchTerm = '';

    public $orderByFieldName = 'created_at';

    public $orderBy = 'desc';

    private $allowedOrderByFieldName = [
        'amount',
        'created_at',
        'due_date',
    ];

    private $allowedFilterBox = [
        'paid',
        'unpaid',
        'overdue',
    ];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        $this->check_service_availability();
    }

    private function check_service_availability()
    {
        $system_service = SystemService::where('slug', 'bills_management')->first();
        if ($system_service->availability !== 'active') {
            session()->flash('warning', 'Bill Payment is currently not available.');
            return $this->redirect(route('user.dashboard'));
        }
    }

    public function updatedDateFilter()
    {
        $this->resetPage();

        if (! in_array($this->dateFilter, ['past_year', 'past_30_days', 'past_week', 'day'])) {
            $this->dateFilter = 'past_year';
        }
    }

    public function handleFilterBoxClick($boxType)
    {
        $this->activeBox = $boxType;
        $this->updatedActiveBox();
    }

    public function updatedActiveBox()
    {
        $this->resetPage();

        if (! in_array($this->activeBox, $this->allowedFilterBox)) {
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

    public function showBill(Bill $billDetails)
    {
        if ($billDetails->entity_id == $this->merchant->id && $billDetails->entity_type == Merchant::class) {
            $this->billDetails = $billDetails;
        } else {
            session()->flash('error', 'Transaction not found');
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

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        $date = $this->get_date();

        $moneySent = $this->merchant->bills()->whereNotNull('payment_date')->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->sum('amount');
        $vsMoneySent = $this->merchant->bills()->whereNotNull('payment_date')->whereBetween('created_at', [$date['vsFromDate'], $date['vsToDate']])->sum('amount');

        $allBillsCount = $this->merchant->bills()->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();
        $paidBillsCount = $this->merchant->bills()->whereNotNull('payment_date')->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();
        $unpaidBillsCount = $this->merchant->bills()->whereDate('due_date', '>=', now())->whereNull('payment_date')->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();
        $overdueBillsCount = $this->merchant->bills()->whereDate('due_date', '<', now())->whereNull('payment_date')->whereBetween('created_at', [$date['fromDate'], $date['toDate']])->count();

        $bills = Bill::with('entity')->whereHasMorph('entity', [Merchant::class], function ($query) {
            $query->where('entity_id', $this->merchant->id);
        })
            ->whereBetween('created_at', [$date['fromDate'], $date['toDate']]);

        if ($this->activeBox == 'paid') {
            $bills = $bills->whereNotNull('payment_date');
        } elseif ($this->activeBox == 'unpaid') {
            $bills = $bills->whereNull('payment_date')->whereDate('due_date', '>=', now());
        } elseif ($this->activeBox == 'overdue') {
            $bills = $bills->whereNull('payment_date')->whereDate('due_date', '<', now());
        }

        if ($this->searchTerm) {
            $bills = $bills->where('biller_name', 'like', '%'.$this->searchTerm.'%');
        }

        $bills = $bills->orderBy($this->orderByFieldName, $this->orderBy)->paginate(15);
        $elements = $this->getPaginationElements($bills);
        // dd($bills);
        foreach ($bills as $bill) {
            if ($bill->payment_date) {
                $bill->status = 'paid';
            } elseif ($bill->due_date < now()) {
                $bill->status = 'overdue';
            } elseif ($bill->due_date >= now()) {
                $bill->status = 'unpaid';
            } else {
                $bill->status = 'n/a';
            }
        }

        return view('merchant.financial-transaction.bills.merchant-financial-transaction-bills', [
            'bills' => $bills,
            'elements' => $elements,
            'moneySent' => $moneySent,
            'vsMoneySent' => $vsMoneySent,
            'allBillsCount' => $allBillsCount,
            'paidBillsCount' => $paidBillsCount,
            'unpaidBillsCount' => $unpaidBillsCount,
            'overdueBillsCount' => $overdueBillsCount,
        ]);
    }
}
