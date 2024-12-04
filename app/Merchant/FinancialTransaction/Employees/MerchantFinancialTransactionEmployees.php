<?php

namespace App\Merchant\FinancialTransaction\Employees;

use App\Models\Employee;
use App\Models\Merchant;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use App\Traits\WithValidPhoneNumber;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantFinancialTransactionEmployees extends Component
{
    use WithCustomPaginationLinks, WithPagination, WithImage, WithValidPhoneNumber;

    public Employee $employee_user;

    public Merchant $merchant;

    public $searchTerm = '';

    #[Locked]
    public $color = 'red';

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
        $this->employee_user = $this->merchant->employees()
            ->where('user_id', auth()->id())
            ->with('role')
            ->firstOrFail();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function delete(Employee $employee)
    {
        if ($employee->merchant_id != $this->merchant->id) {
            session()->flash('error', 'Removal of owner is not allowed');
            return;
        }
        
        if ($employee->user_id === auth()->id()) {
            session()->flash('error', 'Deleting own employee data is not allowed');
            return;
        }

        if ($employee->access_level->slug == 'owner') {
            session()->flash('error', 'Employee with access level of owner cannot be deleted');
            return;
        }

        if ($employee->access_level->slug == 'admin' && $this->employee_user->role->slug !== 'owner') {
            session()->flash('error', 'Unauthorized action');
            return;
        }

        $employee->delete();
        session()->flash('success', 'Employee deleted successfully');
    }

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        $employees = $this->merchant->employees()
            ->with(['user.profile', 'access_level']);

        if ($this->searchTerm) {
            $employees = $employees->whereHas('user', function ($query) {
                $query->whereHas('profile', function ($query) {
                    $terms = explode(' ', $this->searchTerm);
                    foreach ($terms as $term) {
                        $query->where('first_name', 'like', '%'.$term.'%');
                        $query->orWhere('surname', 'like', '%'.$term.'%');
                    }
                });
                $query->orWhere('phone_number', 'like', '%'.$this->searchTerm.'%');
            });
        }

        $employees = $employees->paginate(12);
        $elements = $this->getPaginationElements($employees);

        return view('merchant.financial-transaction.employees.merchant-financial-transaction-employees', [
            'employees' => $employees,
            'elements' => $elements,
        ]);
    }
}
