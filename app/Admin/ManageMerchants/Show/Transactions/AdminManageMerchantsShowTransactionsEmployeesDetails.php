<?php

namespace App\Admin\ManageMerchants\Show\Transactions;

use App\Models\AdminLog;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Merchant;
use App\Models\SalaryType;
use App\Traits\WithImage;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AdminManageMerchantsShowTransactionsEmployeesDetails extends Component
{
    use WithImage, WithValidPhoneNumber;

    public Employee $employee;
    public Merchant $merchant;
    public $occupation;
    public $access_level;
    public $salary_type;
    public $salary = 0;

    #[Locked]
    public $sameUser;

    public function mount(Merchant $merchant,Employee $employee)
    {
        $this->merchant = $merchant;
        $this->employee = $this->merchant->employees()
            ->where('employees.id', $employee->id)
            ->with(['access_level', 'salary_type', 'user.profile'])
            ->firstOrFail();

        $this->sameUser = auth()->user()->id === $employee->user->id;

        $this->occupation = $this->employee->occupation;
        $this->access_level = $this->employee->access_level->slug;
        $this->salary = $this->employee->salary;
        $this->salary_type = $this->employee->salary_type->slug;
    }

    #[Computed(persist: true)]
    public function access_levels()
    {
        return EmployeeRole::toBase()->get();
    }

    #[Computed]
    public function employee_access_level()
    {
        $employee = $this->employee->load(['access_level']);
        return $employee->access_level->name;
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
        if ($this->merchant->id !== 1) {
            return;
        }

        $type = $data['type'];
        $value = $data['value'];

        DB::beginTransaction();

        try {
            $log = new AdminLog;
            $log->user_id = auth()->id();

            if ($type === 'occupation') {
                $log->title = 'Updated occupation of employee ' . $this->employee->id;
                $log->description = 'Occupation updated from ' . $this->employee->occupation . ' to ' . $value;

                $this->employee->occupation = $value;
                session()->flash('success', 'Occupation updated successfully');
                $this->employee->save();
            } elseif ($type === 'access_level') {
                if ($this->employee->access_level->slug == 'owner' && $this->employee->user_id !== $this->merchant->user_id) {
                    return session()->flash('error', 'You cannot change the access level of the owner.');
                }

                if ($this->employee->user_id === $this->merchant->user_id) {
                    return session()->flash('error', 'You cannot change the access level of the merchant owner.');
                }

                $log->title = 'Updated access level of employee ' . $this->employee->id;
                $log->description = 'Access level updated from ' . $this->access_level . ' to ' . $value;

                $this->employee->employee_role_id = $this->access_levels->where('slug', $value)->first()->id;
                session()->flash('success', 'Access level updated successfully');
                $this->employee->save();
            } elseif ($type === 'salary_type') {
                $log->title = 'Updated salary type of  employee ' . $this->employee->id;
                $log->description = 'Salary type updated from ' . $this->salary_type . ' to ' . $value;
                $this->employee->salary_type_id = SalaryType::where('slug', $value)->first()->id;
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

                $log->title = 'Updated salary of employee ' . $this->employee->id;
                $log->description = 'Salary updated from ' . $this->employee->salary . ' to ' . $value;
                $this->employee->salary = $value;
                session()->flash('success', 'Salary updated successfully');
                $this->employee->save();
            }

            $log->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageMerchantsShowTransactionsEmployeesDetails.handleSave: '.$th->getMessage());

            session()->flash('error', 'Something went wrong. Please try again later.');
        }

    }

    public function handleConfirmDeleteEmployee()
    {
        if ($this->merchant->id !== 1) {
            return;
        }

        if ($this->employee->user_id === $this->merchant->user_id) {
            return session()->flash('error', 'You cannot delete the merchant owner.');
        }

        $name = $this->employee->user->name;

        DB::beginTransaction();
        try {
            $this->employee->delete();
            
            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Removed user ' . $this->employee->user->id . ' as employee from merchant ' . $this->merchant->id;
            $log->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminEmployeesShow.handleConfirmDeleteEmployee: '.$th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', $name.' has been removed as an employee.');

        return $this->redirect(route('admin.employees.index'));
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('admin.employees.admin-employees-show');
    }
}
