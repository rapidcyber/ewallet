<?php

namespace App\User\Bills;

use App\Models\Bill;
use App\Models\SystemService;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class UserBills extends Component
{
    use WithPagination, WithCustomPaginationLinks;
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

    public $billDetails = null;

    public $right_sidebar_content_type = '';

    public $moneySent = 0;

    public $vsMoneySent = 0;

    public $allBillsCount = 0;

    public $paidBillsCount = 0;

    public $unpaidBillsCount = 0;

    public $overdueBillsCount = 0;

    public $activeBox = 'all';

    public $searchTerm = '';

    private $allowedFilterBox = [
        'paid',
        'unpaid',
        'overdue',
    ];

    private $allowedFieldName = [
        'amount',
        'created_at',
        'due_date',
    ];

    private $allowedDateFilter = [
        'past_year',
        'past_30_days',
        'past_week',
        'day',
    ];

    public $fieldName = 'created_at';

    public $sort = 'desc';

    public function mount()
    {
        $this->check_service_availability();

        $user = User::find(auth()->id());

        $this->user = $user;

        $this->updateDateRange();
    }

    private function check_service_availability()
    {
        $system_service = SystemService::where('slug', 'bills_management')->first();
        if ($system_service->availability !== 'active') {
            session()->flash('warning', 'Bill Payment is currently not available.');
            return $this->redirect(route('user.dashboard'));
        }
    }

    public function handleFilterBoxClick($val)
    {
        $this->activeBox = $val;
        $this->resetPage();
        $this->reset(['billDetails', 'right_sidebar_content_type']);
    }

    // Table functionalities
    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        if (! in_array($this->dateFilter, $this->allowedDateFilter)) {
            $this->dateFilter = 'past_year';
        }

        $this->updateDateRange();

        $this->resetPage();
    }

    private function updateDateRange()
    {
        $fromDate = null;
        $toDate = null;

        $vsFromDate = null;
        $vsToDate = null;
        $dateFilter = $this->dateFilter;
        if ($dateFilter === 'past_year') {
            $fromDate = Carbon::now()->subYear();
            $toDate = Carbon::now();

            $vsFromDate = Carbon::now()->subYears(2);
            $vsToDate = Carbon::now()->subYear();
        } elseif ($dateFilter === 'past_30_days') {
            $fromDate = Carbon::now()->subDays(29);
            $toDate = Carbon::now();

            $vsFromDate = Carbon::now()->subYears();
            $vsToDate = Carbon::now()->subDays(29);
        } elseif ($dateFilter === 'past_week') {
            $fromDate = Carbon::now()->subDays(6);
            $toDate = Carbon::now();

            $vsFromDate = Carbon::now()->subDays(58);
            $vsToDate = Carbon::now()->subDays(29);
        } elseif ($dateFilter === 'day') {
            $fromDate = Carbon::now()->subHours(24);
            $toDate = Carbon::now();

            $vsFromDate = Carbon::now()->subHours(48);
            $vsToDate = Carbon::now()->subDays(24);
        }

        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->vsFromDate = $vsFromDate;
        $this->vsToDate = $vsToDate;

        $this->updateBillCount();
    }

    private function updateBillCount()
    {
        $user = $this->user;

        $fromDate = $this->fromDate;
        $toDate = $this->toDate;
        $vsFromDate = $this->vsFromDate;
        $vsToDate = $this->vsToDate;

        $this->moneySent = $user->bills()->whereBetween('created_at', [$fromDate, $toDate])->sum('amount');
        $this->vsMoneySent = $user->bills()->whereBetween('created_at', [$vsFromDate, $vsToDate])->sum('amount');
        $this->allBillsCount = $user->bills()->whereBetween('created_at', [$fromDate, $toDate])->count();
        $this->paidBillsCount = $user->bills()->whereNotNull('payment_date')->whereBetween('created_at', [$fromDate, $toDate])->count();
        $this->unpaidBillsCount = $user->bills()->where(function ($q) {
            $q->whereNull('due_date')->orWhere('due_date', '>', Carbon::now());
        })->whereNull('payment_date')->whereBetween('created_at', [$fromDate, $toDate])->count();
        $this->overdueBillsCount = $user->bills()->where('due_date', '<', Carbon::now())->whereNull('payment_date')->whereBetween('created_at', [$fromDate, $toDate])->count();
    }

    public function sortTable($fieldName)
    {
        if ($this->fieldName !== $fieldName) {
            $this->fieldName = $fieldName;
            $this->sort = 'desc';
        } else {
            if ($this->sort === 'desc') {
                $this->sort = 'asc';
            } elseif ($this->sort === 'asc') {
                $this->sort = 'desc';
            }
        }
    }

    public function handleTableRowClick($id)
    {
        if ($this->billDetails && $id == $this->billDetails->id) {
            $this->reset(['billDetails', 'right_sidebar_content_type']);
            return;
        }

        $bill = Bill::find($id);

        if (empty($bill)) {
            return;
        }

        $this->billDetails = $bill;
        $this->right_sidebar_content_type = 'bill_details';
    }

    #[Layout('layouts.user')]
    public function render()
    {
        $user = $this->user;

        $fieldName = $this->fieldName;
        $sort = $this->sort;

        $bills = Bill::whereHasMorph('entity', [User::class], function ($q) {
            $q->where('id', $this->user->id);
        })->selectRaw("
            *,
            CASE
                WHEN payment_date IS NOT NULL THEN 'paid'
                WHEN due_date < '".Carbon::now()."' AND payment_date IS NULL THEN 'overdue'
                ELSE 'unpaid'
            END AS status
            ")->whereBetween('created_at', [$this->fromDate, $this->toDate]);

        if ($this->activeBox && in_array($this->activeBox, $this->allowedFilterBox)) {
            if ($this->activeBox === 'paid') {
                $bills = $bills->whereNotNull('payment_date');
            } elseif ($this->activeBox === 'unpaid') {
                $bills = $bills->where('due_date', '>', Carbon::now())->whereNull('payment_date');
            } elseif ($this->activeBox === 'overdue') {
                $bills = $bills->where('due_date', '<', Carbon::now())->whereNull('payment_date');
            }
        }

        $searchTerm = trim($this->searchTerm);

        if ($searchTerm !== '') {
            $bills = $bills->where('biller_name', 'like', '%'.$this->searchTerm.'%');
        }

        if (! $this->fieldName || ! in_array($this->fieldName, $this->allowedFieldName)) {
            $this->fieldName = 'created_at';
        }

        if (! $this->sort || ! in_array($this->sort, ['desc', 'asc'])) {
            $this->sort = 'desc';
        }

        $bills = $bills->orderBy($fieldName, $sort)->paginate(10);

        $elements = $this->getPaginationElements($bills);

        return view('user.bills.user-bills')->with([
            'bills' => $bills,
            'elements' => $elements,
        ]);
    }
}
