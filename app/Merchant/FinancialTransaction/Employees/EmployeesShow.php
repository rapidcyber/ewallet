<?php

namespace App\Merchant\FinancialTransaction\Employees;

use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Merchant;
use App\Models\SalaryType;
use App\Traits\WithImage;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class EmployeesShow extends Component
{
    use WithImage, WithValidPhoneNumber;
    
    public Merchant $merchant;
    public Employee $employee_user;
    public Employee $employee;
    public $occupation;
    public $access_level;
    public $salary_type;
    public $salary = 0;

    public function mount(Merchant $merchant, Employee $employee)
    {
        $this->merchant = $merchant;
        $this->employee = $merchant->employees()->where('employees.id', $employee->id)
            ->with(['access_level', 'salary_type', 'user.profile'])
            ->firstOrFail();

        $this->employee_user = $merchant->employees()
            ->where('user_id', auth()->id())
            ->with(['access_level'])
            ->firstOrFail();

        $this->occupation = $this->employee->occupation;
        $this->access_level = $this->employee->access_level->slug;
        $this->salary = $this->employee->salary;
        $this->salary_type = $this->employee->salary_type->slug;
    }

    #[Computed(persist: true)]
    public function access_levels()
    {
        if ($this->employee_user->access_level->slug === 'owner') {
            return EmployeeRole::whereNot('slug', 'owner')->toBase()->get();
        }

        return EmployeeRole::whereNotIn('slug', ['owner', 'admin'])->toBase()->get();
    }

    #[Computed]
    public function employee_access_level()
    {
        return $this->employee->access_level()->first()->name;
    }

    #[Computed(persist: true)]
    public function salary_types()
    {
        return SalaryType::toBase()->get();
    }

    #[Computed]
    public function phone_number()
    {
        $phone_number = $this->employee->user->phone_number;
        $phone_iso = $this->employee->user->phone_iso;

        $formatted = $this->phonenumber_info($phone_number, $phone_iso);

        if (! $formatted) {
            return $phone_number;
        }

        return '(+' . $formatted->getCountryCode() . ') ' . $formatted->getNationalNumber();
    }

    public function handleSave($data)
    {
        $type = $data['type'];
        $value = $data['value'];

        if ($this->employee->access_level === 'owner' && $this->employee_user->access_level !== 'owner') {
            return session()->flash('error', 'Unauthorized action');
        }

        if ($this->employee_user->access_level === 'owner' && $this->employee->access_level === 'owner' && $type === 'access_level') {
            return session()->flash('error', 'Cannot change owner access level');
        }

        DB::beginTransaction();

        try {
            if ($type === 'occupation') {
                $this->employee->occupation = $value;
                session()->flash('success', 'Occupation updated successfully');
                $this->employee->save();
            } elseif ($type === 'access_level') {
                $this->employee->employee_role_id = $this->access_levels->where('slug', $value)->first()->id;
                session()->flash('success', 'Access level updated successfully');
                $this->employee->save();
                $key = $this->merchant->id . '-' . $this->employee->user_id;
                Cache::forget("merchant-employee-data-$key");
            } elseif ($type === 'salary_type') {
                $this->employee->salary_type_id = $this->salary_types->where('slug', $value)->first()->id;
                session()->flash('success', 'Salary type updated successfully');
                $this->employee->save();
            } elseif ($type === 'salary') {
                $input = [
                    'salary' => $value,
                ];
                $validator = Validator::make($input, [
                    'salary' => 'numeric|min:0|max:10000000.00',
                ]);
    
                if ($validator->fails()) {
                    return;
                }
                $this->employee->salary = $value;
                session()->flash('success', 'Salary updated successfully');
                $this->employee->save();
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('EmployeesShow.handleSave: '.$th->getMessage());

            session()->flash('error', 'Something went wrong. Please try again later.');
        }
        
    }

    public function handleConfirmDeleteEmployee()
    {
        if ($this->employee->user_id == auth()->id()) {
            return session()->flash('error', 'Cannot delete own employee data');
        }

        if ($this->employee->access_level->slug == 'owner' && $this->merchant->user_id != auth()->id()) {
            return session()->flash('error', 'Employee with access level of owner cannot be deleted');
        }

        if ($this->employee->access_level->slug == 'admin' && $this->employee_user->access_level->slug !== 'owner') {
            return session()->flash('error', 'Unauthorized action');
        }

        $name = $this->employee->user->name;
        $this->employee->delete();

        session()->flash('success', $name . ' has been removed as an employee.');
        return $this->redirect(route('merchant.financial-transactions.employees.index', ['merchant' => $this->merchant]));
    }

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        return view('merchant.financial-transaction.employees.employees-show');
    }
}
