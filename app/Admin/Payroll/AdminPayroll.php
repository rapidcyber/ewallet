<?php

namespace App\Admin\Payroll;

use App\Models\Balance;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminPayroll extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage;

    public Merchant $merchant;
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $searchTerm = '';

    protected $allowedSortValues = [
        'salary',
        // 'salary_type_id',
        'amount',
        'created_at'
    ];

    public function mount()
    {
        $merchant = Merchant::where('id', 1)->firstOrFail();
        $this->merchant = $merchant;
    }

    #[Computed]
    public function balance_amount()
    {
        return $this->merchant->latest_balance()->first()->amount ?? 0;
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

        $this->sortBy = !in_array($fieldName, $this->allowedSortValues) ? 'created_at' : $fieldName;
        $this->sortDirection = 'desc';
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $payroll_transactions = Transaction::whereHasMorph('sender', [Merchant::class], function ($query) {
            $query->where('id', $this->merchant->id);
        })
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

        return view('admin.payroll.admin-payroll')->with([
            'payroll_transactions' => $payroll_transactions,
            'elements' => $elements
        ]);
    }
}
