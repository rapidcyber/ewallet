<?php

namespace App\Admin\ManageMerchants\Show\Transactions;

use App\Models\AdminLog;
use App\Models\Employee;
use App\Models\Merchant;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageMerchantsShowTransactionsEmployees extends Component
{
    use WithCustomPaginationLinks, WithPagination, WithImage;

    #[Locked]
    public $has_employees = false;

    public Merchant $merchant;

    public $searchTerm = '';

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        $this->has_employees = $this->merchant->employees()->count() > 0;
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function delete(Employee $employee)
    {
        if ($this->merchant->id !== 1) {
            return;
        }

        if ($employee->merchant_id != $this->merchant->id) {
            return session()->flash('error', 'Employee not found');
        }

        if ($employee->user_id === $this->merchant->user_id) {
            return session()->flash('error', 'Deletion of merchant owner is not allowed');
        }

        DB::beginTransaction();
        try {
            $employee->delete();
            
            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Removed user ' . $employee->user->id . ' as employee from merchant ' . $this->merchant->id;
            $log->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('AdminManageMerchantsShowTransactionsEmployees.delete: '.$ex->getMessage());
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }

        return session()->flash('success', 'Employee deleted successfully');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        if (! $this->has_employees) {
            $employees = [];
            $elements = null;
        } else {
            $employees = $this->merchant->employees()
                ->with(['user.profile', 'access_level']);

            if ($this->searchTerm) {
                $employees = $employees->whereHas('user', function ($query) {
                    $query->whereHas('profile', function ($query) {
                        $query->where('first_name', 'like', '%'.$this->searchTerm.'%');
                        $query->orWhere('surname', 'like', '%'.$this->searchTerm.'%');
                    });
                    $query->orWhere('phone_number', 'like', '%'.$this->searchTerm.'%');
                });
            }

            $employees = $employees->paginate(10);
            $elements = $this->getPaginationElements($employees);
        }

        return view('admin.manage-merchants.show.transactions.admin-manage-merchants-show-transactions-employees', [
            'employees' => $employees,
            'elements' => $elements,
        ]);
    }
}
