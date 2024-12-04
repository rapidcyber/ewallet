<?php

namespace App\Admin\ManageMerchants\Show\Transactions;

use App\Models\Balance;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageMerchantsShowTransactionsPayroll extends Component
{
    use WithPagination, WithCustomPaginationLinks;

    public Merchant $merchant;
    public Balance $balance;
    public $sortBy = 'salary';
    public $sortDirection = 'desc';
    public $searchTerm = '';

    protected $allowedSortValues = [
        'salary',
        'salary_type_id',
        // 'total_deductions',
        // 'sss_deduction',
        // 'net_pay',
    ];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
        $this->balance = $merchant->latest_balance()->first() ?? new Balance;
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function sortTable($fieldName)
    {
        if ($this->sortBy == $fieldName) {
            return $this->sortDirection = $this->sortDirection == 'asc' ? 'desc' : 'asc';
        }

        $this->sortBy = !in_array($fieldName, $this->allowedSortValues) ? 'salary' : $fieldName;
        $this->sortDirection = 'desc';
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $payroll_transactions = $this->merchant->outgoing_transactions()
            ->whereHas('type', function ($query) {
                $query->where('code', 'PS');
            })
            ->whereHasMorph('recipient', [User::class], function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('merchant_id', $this->merchant->id);
                });

                if ($this->searchTerm) {
                    $query->whereHas('profile', function ($q) {
                        $q->where('first_name', 'like', '%' . $this->searchTerm . '%');
                        $q->orWhere('surname', 'like', '%' . $this->searchTerm . '%');
                    });
                }
            })
            ->with(['recipient' => function (MorphTo $q) {
                $q->morphWith([
                    User::class => ['profile', 'employee' => function ($q) {
                        $q->where('merchant_id', $this->merchant->id);
                        $q->with(['salary_type']);
                    }]
                ]);
            }]);

        $payroll_transactions = $payroll_transactions->join('employees', 'transactions.recipient_id', 'employees.user_id')
            ->select('transactions.*', 'employees.salary as salary');

        $payroll_transactions = $payroll_transactions->orderBy($this->sortBy, $this->sortDirection)->paginate(10);

        $elements = $this->getPaginationElements($payroll_transactions);

        return view('admin.manage-merchants.show.transactions.admin-manage-merchants-show-transactions-payroll')->with([
            'payroll_transactions' => $payroll_transactions,
            'elements' => $elements
        ]);
    }
}
