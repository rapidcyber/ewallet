<?php

namespace App\Merchant\FinancialTransaction\Employees;

use App\Mail\NewEmployeeRegistrationSuccess;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Merchant;
use App\Models\NotificationModule;
use App\Models\Profile;
use App\Models\Role;
use App\Models\SalaryType;
use App\Models\User;
use App\Traits\WithCountryName;
use App\Traits\WithMail;
use App\Traits\WithNotification;
use App\Traits\WithPhoneNumberPrefixes;
use App\Traits\WithTempPassword;
use App\Traits\WithTempPin;
use App\Traits\WithValidPhoneNumber;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EmployeesCreate extends Component
{
    use WithCountryName, WithMail, WithNotification, WithPhoneNumberPrefixes, WithTempPassword, WithTempPin, WithValidPhoneNumber;

    public $mode = 'no_repay_account';

    public Merchant $merchant;
    public Employee $employee;

    #[Validate]
    public $phone_iso = 'PH';
    #[Validate]
    public $phone_number = '';
    public $position;
    public $salary_type;
    public $salary;
    public $access_level;
    public $isAgree = false;

    #[Locked]
    public $notified = false;

    // No repay account
    public $telephone_number;
    public $email;
    public $first_name;
    public $middle_name;
    public $surname;
    public $suffix;
    public $gender;
    public $nationality;
    public $birthdate;
    public $birthplace;
    public $mothers_maiden_name;

    // Toast notifs
    public $success_message = '';
    public $error_message = '';

    public $isEmployeeResendModalVisible = false;

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
        $this->employee = $this->merchant->employees()->where('user_id', auth()->id())->firstOrFail();
    }

    #[Computed(persist: true)]
    public function affiliation_module()
    {
        return NotificationModule::where('slug', 'affiliation')->first();
    }

    public function rules()
    {
        $rules = [
            'mode' => 'required|in:no_repay_account,has_repay_account',
            'phone_iso' => 'required|in:' . implode(',', array_column($this->phone_number_prefixes, 'code')),
            'phone_number' => ['required', function ($attribute, $value, $fail) {
                $phone_number = $this->format_phone_number_for_saving($value, $this->phone_iso);

                if (! $phone_number) {
                    $fail('phone_number', 'Invalid phone number');
                }

                if ($this->mode === 'no_repay_account') {
                    if (User::where('phone_number', $phone_number)->first()) {
                        $fail('phone_number', 'The phone number already belongs to an existing user');
                    }
                    if (! $this->is_valid_phonenumber($phone_number)) {
                        $fail('phone_number', 'Invalid phone number');
                    }
                } elseif ($this->mode === 'has_repay_account') {
                    $user = User::where('phone_number', $phone_number)->first();
                    if (!$user) {
                        $fail('phone_number', 'The phone number is not associated with an existing user');
                    }

                    if ($user->notifications()->where('ref_id', $this->merchant->account_number)->where('notification_module_id', $this->affiliation_module->id)->first()) {
                        $this->notified = true;
                    } else {
                        $this->notified = false;
                    }
                }
            }],
            'position' => 'required',
            'access_level' => 'required|in:' . implode(',', array_column($this->access_levels, 'slug')),
            'salary_type' => 'required|in:' . implode(',', array_column($this->salary_types, 'slug')),
            'salary' => 'required|numeric|gt:0',
            'isAgree' => 'accepted',
        ];
        if ($this->mode === 'no_repay_account') {
            $rules = array_merge($rules, [
                'telephone_number' => 'nullable|numeric',
                'email' => 'required|email:rfc,dns|unique:users,email',
                'first_name' => 'required|string|max:120',
                'middle_name' => 'nullable|string|max:120',
                'surname' => 'required|string|max:120',
                'suffix' => 'nullable|string|max:50',
                'gender' => 'nullable|in:male,female',
                'nationality' => 'nullable|in:' . implode(',', $this->nationalities),
                'birthdate' => 'nullable|date|before:-18 years',
                'birthplace' => 'nullable|string|max:255',
                'mothers_maiden_name' => 'nullable|string|max:255',
            ]);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'isAgree.accepted' => 'Please agree that the information above is correct before submitting.',
        ];
    }

    #[Computed]
    public function phone_number_prefixes()
    {
        return $this->get_phone_number_prefixes();
    }

    #[Computed]
    public function nationalities()
    {
        return config('constants.nationalities');
    }

    #[Computed(persist: true)]
    public function salary_types()
    {
        return SalaryType::toBase()->get()->toArray();
    }

    #[Computed(persist: true)]
    public function access_levels()
    {
        return EmployeeRole::whereNot('slug', 'owner')->toBase()->get()->toArray();
    }

    #[On('updateMode')]
    public function updatedMode()
    {
        if (!in_array($this->mode, ['no_repay_account', 'has_repay_account'])) {
            $this->mode = 'no_repay_account';
        }

        $this->resetValidation();
        $this->isAgree = false;
    }

    public function add_employee_no_repay_account()
    {
        if ($this->mode !== 'no_repay_account') {
            return $this->error_message = 'Something went wrong. Please refresh the page and try again.';
        }

        $this->validate();

        $phone_number_data = $this->phonenumber_info($this->phone_number, $this->phone_iso);
        if (! $phone_number_data) {
            return $this->addError('phone_number', 'Invalid phone number');
        }

        DB::beginTransaction();
        try {
            $applicant = new User;

            [$plainpass, $hashpass] = $this->generate_temp_password();
            $pin = $this->generate_temp_pin();

            $applicant->app_id = Str::uuid();
            $applicant->username = explode('@', $this->email)[0] . '_' . User::count() + 1;
            $applicant->password = $hashpass;
            $applicant->pin = $pin;
            $applicant->email = $this->email;
            $applicant->phone_iso = $this->phone_iso;
            $applicant->phone_number = $phone_number_data->getCountryCode() . $phone_number_data->getNationalNumber();

            $applicant->save();

            $applicant->roles()->attach(Role::where('slug', str('user')->slug())->first()->id);

            $profile = new Profile;
            $profile->user_id = $applicant->id;
            $profile->first_name = $this->first_name;
            $profile->middle_name = $this->middle_name ?? null;
            $profile->surname = $this->surname;
            $profile->suffix = $this->suffix ?? null;
            $profile->mother_maiden_name = $this->mothers_maiden_name ?? null;
            $profile->nationality = $this->nationality ?? null;
            $profile->sex = $this->gender ?? null;
            $profile->birth_date = $this->birthdate ?? null;
            $profile->birth_place = $this->birthplace ?? null;
            $profile->landline_iso = $this->telephone_number ? $this->phone_iso : null;
            $profile->landline_number = $this->telephone_number ?? null;
            $profile->save();

            $extras = [
                'occupation' => $this->position,
                'salary' => $this->salary,
                'salary_type' => $this->salary_type,
                'role_id' => $this->access_level,
            ];

            $this->alert(
                $applicant,
                'affiliation',
                $this->merchant->account_number,
                'Your account is pending addition to '.$this->merchant->name."'s merchant account",
                
                $extras
            );

            $name = $this->first_name.' '.$this->surname;
            $phone_number = $this->format_phone_number($this->phone_number, $this->phone_iso);
            $email = $this->email;
            $temp_password = $plainpass;

            $this->sendMail(
                $applicant->email,
                new NewEmployeeRegistrationSuccess($name, $phone_number, $email, $temp_password, $pin),
            );

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return $this->error_message = 'Something went wrong. Please refresh the page and try again.';
        }

        $this->reset_input_fields();

        return $this->success_message = 'Invitation sent to the employee.';
    }

    public function add_employee_has_repay_account()
    {
        if ($this->mode !== 'has_repay_account') {
            return $this->error_message = 'Something went wrong. Please refresh the page and try again.';
        }

        $this->validate();

        $phone_number = $this->format_phone_number_for_saving($this->phone_number, $this->phone_iso);
        if (! $phone_number) {
            return $this->addError('phone_number', 'Invalid phone number');
        }

        $user = User::where('phone_number', $phone_number)->first();
        if (! $user) {
            return $this->addError('phone_number', 'Phone number is not associated with an existing user');
        }

        if ($this->merchant->employees()->where('user_id', $user->id)->first()) {
            return $this->addError('phone_number', 'The user is already added on the current merchant.');
        }

        DB::beginTransaction();
        try {
            $extras = [
                'occupation' => $this->position,
                'salary' => $this->salary,
                'salary_type' => $this->salary_type,
                'role_id' => $this->access_level,
            ];

            $response = $this->set_other_notifs_to_expired($user);

            if ($response !== null) {
                throw new Exception($response);
            }

            $this->alert(
                $user,
                'affiliation',
                $this->merchant->account_number,
                'Your account is pending addition to '.$this->merchant->name."'s merchant account",
                
                $extras
            );

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());
            return $this->error_message = 'Something went wrong. Please refresh the page and try again.';
        }

        $this->reset_input_fields();
        return $this->success_message = 'Invitation sent to the employee.';
    }

    private function set_other_notifs_to_expired(User $user)
    {
        DB::beginTransaction();
        try {
            $notification_module = NotificationModule::where('slug', 'affiliation')->firstOrFail();
            $expired_module = NotificationModule::where('slug', 'expired')->firstOrFail();
    
            $notifications = $user->notifications()
                ->where('notification_module_id', $notification_module->id)
                ->where('ref_id', $this->merchant->account_number)
                ->get();

            foreach ($notifications as $notification) {
                $notification->notification_module_id = $expired_module->id;
                $notification->save();
            }

            DB::commit();
            return null;
        } catch (Exception $ex) {
            DB::rollBack();
            return $ex;
        }
    }

    private function reset_input_fields()
    {
        $this->reset([
            'phone_iso',
            'phone_number',
            'telephone_number',
            'email',
            'first_name',
            'middle_name',
            'surname',
            'suffix',
            'gender',
            'nationality',
            'birthdate',
            'birthplace',
            'mothers_maiden_name',
            'position',
            'salary',
            'salary_type',
            'access_level',
            'isEmployeeResendModalVisible',
            'isAgree',
        ]);
    }

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        return view('merchant.financial-transaction.employees.employees-create');
    }
}
