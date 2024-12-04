<?php

namespace App\Admin\Employees;

use App\Mail\RepayMail;
use App\Models\EmployeeRole;
use App\Models\Merchant;
use App\Models\Profile;
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
use Livewire\Attributes\Layout;
use Livewire\Component;

class AdminEmployeesCreate extends Component
{
    use WithCountryName, WithMail, WithNotification, WithPhoneNumberPrefixes, WithTempPassword, WithTempPin, WithValidPhoneNumber;

    public $mode = 'no_repay_account';

    public Merchant $merchant;

    // No repay account
    public $no_repay_phone_number_prefix = '';

    public $no_repay_phone_number = '';

    public $telephone_number = '';

    public $email = '';

    public $first_name = '';

    public $middle_name = '';

    public $surname = '';

    public $suffix = '';

    public $gender = '';

    public $nationality = '';

    public $birthdate = '';

    public $birthplace = '';

    public $mothers_maiden_name = '';

    public $no_repay_position = '';

    public $no_repay_salary = '';

    public $no_repay_salary_type = '';

    public $no_repay_access_level = '';

    // Has repay account

    public $has_repay_phone_number_prefix = '';

    public $has_repay_phone_number = '';

    public $has_repay_position = '';

    public $has_repay_salary_type = '';

    public $has_repay_salary = '';

    public $has_repay_access_level = '';

    public $emailErr = '';

    public $noRepayPhoneNumberErr = '';

    public $hasRepayPhoneNumberErr = '';

    public $isAgree = false;

    public $accessLevels;

    public $phone_number_prefixes;

    public $nationalities;

    public $successAddEmployeeMsg = '';

    public $failedAddEmployeeMsg = '';

    public $salary_types;

    public $access_level_no_repay;

    public $access_level_has_repay;

    protected $messages = [
        'no_repay_phone_number.required' => 'The phone number field is required.',
        'has_repay_phone_number.required' => 'The phone number field is required.',
        'no_repay_position.required' => 'Position is required.',
        'has_repay_position.required' => 'Position is required.',
        'no_repay_access_level.required' => 'Access level is required.',
        'has_repay_access_level.required' => 'Access level is required.',
        'no_repay_salary_type.required' => 'Salary type is required.',
        'no_repay_salary.required' => 'Salary is required.',
        'has_repay_salary_type.required' => 'Salary type is required.',
        'has_repay_salary.required' => 'Salary is required.',

    ];

    public $isEmployeeResendModalVisible = false;

    public $notified;

    public function __construct()
    {
        $this->nationalities = config('constants.nationalities');
    }

    public function mount()
    {
        $this->merchant = Merchant::find(1);
        $this->phone_number_prefixes = $this->get_phone_number_prefixes();
        $this->salary_types = SalaryType::all();
    }

    public function rules()
    {
        if ($this->mode === 'no_repay_account') {
            return [
                'no_repay_phone_number_prefix' => 'required',
                'no_repay_phone_number' => 'required',
                'email' => 'required|email',
                'first_name' => 'required',
                'surname' => 'required',
                'gender' => 'required',
                'nationality' => 'required',
                'birthdate' => 'required',
                'birthplace' => 'required',
                'no_repay_position' => 'required',
                'no_repay_access_level' => 'required',
                'no_repay_salary_type' => 'required',
                'no_repay_salary' => 'required|numeric|gt:0',
            ];
        } elseif ($this->mode === 'has_repay_account') {
            return [
                'has_repay_phone_number_prefix' => 'required',
                'has_repay_phone_number' => 'required',
                'has_repay_position' => 'required',
                'has_repay_access_level' => 'required',
                'has_repay_salary_type' => 'required',
                'has_repay_salary' => 'required|numeric|gt:0',
            ];
        }
    }

    public function handleAddEmployeeSubmit()
    {
        $this->isEmployeeResendModalVisible = false;
        $this->validate();
        $phoneNumberErr = '';
        $emailErr = '';

        $phoneNumber = $this->no_repay_phone_number_prefix . '' . $this->no_repay_phone_number;
        $phoneNumberWithoutPlusSign = substr($phoneNumber, 1);

        if ($this->mode === 'no_repay_account') {
            if (User::where('phone_number', $phoneNumberWithoutPlusSign)->exists()) {
                $phoneNumberErr = 'Phone number is already taken.';
            }

            if ($this->is_valid_phonenumber($phoneNumber) === false) {
                $phoneNumberErr = 'Invalid phone number.';
            }

            if (User::where('email', $this->email)->exists()) {
                $emailErr = 'Email address is already taken.';
            }

            if (!empty($phoneNumberErr) || !empty($emailErr)) {
                if (!empty($phoneNumberErr)) {
                    $this->noRepayPhoneNumberErr = $phoneNumberErr;
                }
                if (!empty($emailErr)) {
                    $this->emailErr = $emailErr;
                }

                if (empty($phoneNumberErr)) {
                    $this->noRepayPhoneNumberErr = '';
                }

                if (empty($emailErr)) {
                    $this->emailErr = '';
                }

                return;
            }
            $applicant = new User;

            [$plainpass, $hashpass] = $this->generate_temp_password($this->email);
            $pin = $this->generate_temp_pin();

            $applicant->app_id = Str::uuid();
            $applicant->username = explode('@', $this->email)[0];
            $applicant->password = $hashpass;
            $applicant->pin = $pin;
            $applicant->email = $this->email;
            $applicant->phone_iso = collect($this->phone_number_prefixes)->firstWhere('dial_code', $this->no_repay_phone_number_prefix)['code'];
            $applicant->phone_number = $phoneNumberWithoutPlusSign;

            try {
                DB::transaction(function () use ($applicant, $plainpass, $pin) {
                    $applicant->save();

                    $profile = new Profile;
                    $profile->user_id = $applicant->id;
                    $profile->first_name = $this->first_name;
                    $profile->middle_name = $this->middle_name ?? '';
                    $profile->surname = $this->surname;
                    $profile->suffix = $this->suffix ?? '';
                    $profile->mother_maiden_name = $this->mothers_maiden_name ?? '';
                    $profile->nationality = $this->nationality;
                    $profile->sex = $this->gender;
                    $profile->birth_date = $this->birthdate;
                    $profile->birth_place = $this->birthplace;
                    $profile->landline_iso = collect($this->phone_number_prefixes)->firstWhere('dial_code', $this->no_repay_phone_number_prefix)['code'];
                    $profile->landline_number = empty($this->telephone_number) ? '' : $this->telephone_number;
                    $profile->save();

                    $extras = [
                        'occupation' => $this->no_repay_position,
                        'salary' => $this->no_repay_salary,
                        'salary_type' => 'per_cutoff',
                        'role_id' => $this->no_repay_access_level,
                    ];

                    $this->alert(
                        $applicant,
                        'affiliation',
                        $this->merchant->account_number,
                        'Your account is pending addition to ' . $this->merchant->name . "'s merchant account",

                        $extras
                    );

                    $this->sendMail(
                        $applicant->email,
                        new RepayMail(
                            'Merchant Affiliation Invite',
                            [
                                'You are invited to join <b>' . strtoupper($this->merchant->name) . "</b> as part of it's affiliated personnel.",
                                ' ',
                                'You can now login to repay using your email address and a temporary password and pin:',
                                "Password: <b>$plainpass</b>",
                                "4 Digit Pin: <b>$pin</b>",
                                ' ',
                                'An invitation was sent to your Repay App.',
                            ]
                        ),
                    );
                });
                $this->successAddEmployeeMsg = 'Invitation sent successfully!';
            } catch (Exception $ex) {
                Log::error($ex->getMessage());
                $this->failedAddEmployeeMsg = 'Something went wrong. Please try again later.';
            }
        } elseif ($this->mode === 'has_repay_account') {
            $this->hasRepayPhoneNumberErr = '';
            if (str_starts_with($this->has_repay_phone_number, '0')) {
                $this->has_repay_phone_number = substr($this->has_repay_phone_number, 1);
            }

            $s_user = User::where('phone_number', 'like', '%' . $this->has_repay_phone_number)->first();

            if (!$s_user) {
                return $this->hasRepayPhoneNumberErr = "Phone number doesn't exist";

            }

            if ($this->is_valid_phonenumber($s_user->phone_number) === false) {
                return $this->hasRepayPhoneNumberErr = 'Invalid phone number.';
            }

            if (!$this->merchant->employees()->where('user_id', $s_user->id)->first()) {

                $applicant = User::where('id', $s_user->id)->first();

                $extras = [
                    'occupation' => $this->no_repay_position,
                    'salary' => $this->no_repay_salary,
                    'salary_type' => 'per_cutoff',
                    'role_id' => $this->no_repay_access_level,
                ];

                try {
                    DB::transaction(function () use ($applicant, $extras) {
                        $this->alert(
                            $applicant,
                            'affiliation',
                            $this->merchant->account_number,
                            'Your account is pending addition to ' . $this->merchant->name . "'s merchant account",
                            $extras
                        );
                    });

                    $this->successAddEmployeeMsg = 'Invitation sent successfully!';
                } catch (Exception $ex) {
                    Log::error($ex->getMessage());
                    $this->failedAddEmployeeMsg = 'Somthing went wrong. Please try again.';
                }
            } else {
                $this->failedAddEmployeeMsg = 'The user is already added on the current merchant.';
            }

        }
    }

    public function updatedMode()
    {
        $this->isAgree = false;
    }

    public function updatedHasRepayPhoneNumber($value)
    {
        $this->notified = null;
        $s_user = User::where('phone_number', 'like', '%' . $value)->first();

        if ($s_user) {
            $this->notified = $s_user->notifications()
                ->where('ref_id', $this->merchant->id)
                ->first();
        }
    }

    public function updated($propertyName)
    {
        if ($propertyName != 'isAgree') {
            $this->isAgree = false;

        }
    }

    public function handleSelectCardClick($val)
    {
        $this->mode = $val;
    }

    public function handleCloseMessageModal()
    {
        $this->isAgree = false;
        $this->successAddEmployeeMsg = '';
        $this->failedAddEmployeeMsg = '';
        if ($this->mode === 'no_repay_account') {
            $this->no_repay_phone_number_prefix = '';
            $this->no_repay_phone_number = '';
            $this->telephone_number = '';
            $this->email = '';
            $this->first_name = '';
            $this->middle_name = '';
            $this->surname = '';
            $this->suffix = '';
            $this->gender = '';
            $this->nationality = '';
            $this->birthdate = '';
            $this->birthplace = '';
            $this->mothers_maiden_name = '';
            $this->no_repay_position = '';
            $this->no_repay_salary = '';
            $this->no_repay_salary_type = '';
            $this->no_repay_access_level = '';
            $this->noRepayPhoneNumberErr = '';
            $this->emailErr = '';
        } elseif ($this->mode === 'has_repay_account') {
            $this->has_repay_phone_number_prefix = '';
            $this->has_repay_phone_number = '';
            $this->has_repay_position = '';
            $this->has_repay_salary_type = '';
            $this->has_repay_salary = '';
            $this->has_repay_access_level = '';
            $this->hasRepayPhoneNumberErr = '';
        }
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $this->accessLevels = EmployeeRole::all()->toArray();
        $this->access_level_no_repay = collect($this->accessLevels)->firstWhere('slug', $this->no_repay_access_level);
        $this->access_level_has_repay = collect($this->accessLevels)->firstWhere('slug', $this->has_repay_access_level);

        $formData = [
            'no_repay_phone_number_prefix' => $this->no_repay_phone_number_prefix,
            'no_repay_phone_number' => $this->no_repay_phone_number,
            'telephone_number' => $this->telephone_number,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'surname' => $this->surname,
            'sufix' => $this->suffix,
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'birthdate' => $this->birthdate,
            'birthplace' => $this->birthplace,
            'mothers_maiden_name' => $this->mothers_maiden_name,
            'no_repay_position' => $this->no_repay_position,
            'no_repay_salary' => $this->no_repay_salary,
            'no_repay_access_level' => $this->no_repay_access_level,
            'has_repay_phone_number' => $this->has_repay_phone_number,
            'has_repay_position' => $this->has_repay_position,
            'has_repay_salary' => $this->has_repay_salary,
            'has_repay_access_level' => $this->has_repay_access_level,
        ];

        return view('admin.employees.admin-employees-create');
    }
}
