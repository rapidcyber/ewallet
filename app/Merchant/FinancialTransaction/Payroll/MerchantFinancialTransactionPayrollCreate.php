<?php

namespace App\Merchant\FinancialTransaction\Payroll;

use App\Models\Balance;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionRequest;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\WithBalance;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use App\Traits\WithTransactionLimit;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantFinancialTransactionPayrollCreate extends Component
{
    use WithPagination, WithNumberGeneration, WithTransactionLimit, WithImage, WithValidPhoneNumber, WithBalance, WithNotification;

    public Merchant $merchant;
    public Employee $employee_user;
    public $searchTerm = '';
    #[Locked]
    public $currentPageNumber = 1;
    #[Locked]
    public $hasPages = false;
    #[Locked]
    public $totalPages = 1;
    #[Locked]
    public $selectedEmployee = null;
    public $salaryDetailErrorMessage = '';

    public $days_worked;
    public $deductions = 0;
    public $net_pay = 0;

    public $displayConfirmation = false;
    public $confirmationMessage = '';

    public $successMessage = '';
    public $successAmount;
    public $successRemainingBal;

    public $failedMessage = '';

    #[Locked]
    public $need_approval = true;

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        $this->employee_user = $this->merchant->employees()->where('employees.user_id', auth()->id())->firstOrFail();

        if (Gate::allows('merchant-cash-outflow', [$this->merchant, 'approve'])) {  
            $this->need_approval = false;
        }
    }

    #[Computed]
    public function available_balance()
    {
        return $this->merchant->latest_balance()->first()->amount ?? 0;
    }

    public function updatedSearchTerm()
    {
        $this->currentPageNumber = 1;
    }

    public function handlePageArrow($direction)
    {
        if (!in_array($direction, ['left', 'right'])) {
            return;
        }

        if ($direction === 'left') {
            $this->currentPageNumber = $this->currentPageNumber !== 1 ? $this->currentPageNumber - 1 : $this->currentPageNumber;
        } else if ($direction === 'right') {
            $this->currentPageNumber = $this->currentPageNumber !== $this->totalPages ? $this->currentPageNumber + 1 : $this->currentPageNumber;
        }
    }

    public function handleEmployeeSelect($employee_id)
    {
        $this->selectedEmployee = $this->merchant->employees()
            ->where('employees.id', $employee_id)
            ->with(['user.profile', 'salary_type'])
            ->first();

        if ($this->selectedEmployee) {
            $this->days_worked = 1;
            $this->deductions = 0;
            $this->net_pay = $this->selectedEmployee->salary;
        }
    }

    public function clearEmployeeSelect()
    {
        $this->reset(['selectedEmployee']);
    }

    public function updatedDaysWorked()
    {
        $this->validate([
            'days_worked' => 'numeric|gt:0',
        ]);

        if ($this->selectedEmployee && $this->selectedEmployee->salary_type->slug === 'per_day' && $this->days_worked) {
            $this->net_pay = ($this->selectedEmployee->salary * $this->days_worked) - $this->deductions;
        } else {
            $this->net_pay = $this->selectedEmployee->salary - $this->deductions;
        }
    }

    public function updatedDeductions()
    {
        $this->validate([
            'deductions' => 'numeric|min:0',
        ]);

        if ($this->selectedEmployee && $this->selectedEmployee->salary_type->slug === 'per_day') {
            $this->net_pay = ($this->selectedEmployee->salary * $this->days_worked) - $this->deductions;
        } else {
            $this->net_pay = $this->selectedEmployee->salary - $this->deductions;
        }
    }

    public function showConfirmationModal()
    {
        $validated = $this->validate_salary_details();

        if (!$validated) {
            return;
        }

        if ($this->need_approval) {
            $this->confirmationMessage = "A request for a salary payment of " . Number::currency($this->net_pay, 'PHP') . " to " . $this->selectedEmployee->user->name . " will be sent for approval.";
        } else {
            $this->confirmationMessage = 'You are about to send a salary payment of ' . Number::currency($this->net_pay, 'PHP') . ' to ' . $this->selectedEmployee->user->name . '.';
        }
        $this->displayConfirmation = true;
    }

    private function validate_salary_details(): bool
    {
        if (!$this->selectedEmployee) {
            $this->displayConfirmation = false;
            $this->salaryDetailErrorMessage = 'Please select an employee';
            return false;
        }

        $rules = [
            'deductions' => 'required|numeric|min:0',
        ];

        if ($this->selectedEmployee->salary_type->slug === 'per_day') {
            $rules['days_worked'] = 'required|numeric|min:1';
        }

        try {
            $this->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $th) {
            $this->displayConfirmation = false;
            $this->salaryDetailErrorMessage = $th->validator->errors()->first();
            return false;
        }

        if ($this->selectedEmployee->salary_type->slug === 'per_day') {
            $days_worked = $this->days_worked ?? 1;
            $net_pay = ($this->selectedEmployee->salary * $days_worked) - $this->deductions;
        } else {
            $net_pay = $this->selectedEmployee->salary - $this->deductions;
        }

        $type = TransactionType::where('code', 'PS')->firstOrFail();
        if ($this->check_outbound_limit($this->merchant, $type, $net_pay) == true) {
            $this->displayConfirmation = false;
            $this->failedMessage = 'Merchant account has reached the monthly transaction limit';
            return false;
        }

        $balance = $this->merchant->latest_balance ?? new Balance;

        if ($balance->amount < $net_pay) {
            $this->displayConfirmation = false;
            $this->failedMessage = 'Insufficient balance';
            return false;
        }

        return true;
    }

    public function submit()
    {
        if ($this->need_approval) {
            $this->send_salary_for_approval();
        } else {
            $this->send_salary();
        }
    }

    private function send_salary_for_approval()
    {
        $validated = $this->validate_salary_details();

        if (!$validated) {
            return;
        }

        $recipient = $this->selectedEmployee->user;

        DB::beginTransaction();
        try {
            $transaction_request = new TransactionRequest;
            $transaction_request->merchant_id = $this->merchant->id;
            $transaction_request->recipient_id = $recipient->id;
            $transaction_request->recipient_type = User::class;
            $transaction_request->transaction_provider_id = TransactionProvider::where('code', 'RPY')->firstOrFail()->id;
            $transaction_request->transaction_channel_id = TransactionChannel::where('code', 'RPY')->firstOrFail()->id;
            $transaction_request->transaction_type_id = TransactionType::where('code', 'PS')->firstOrFail()->id;
            $transaction_request->service_fee = 0;
            $transaction_request->currency = 'PHP';
            $transaction_request->amount = $this->net_pay;
            $transaction_request->created_by = $this->employee_user->id;
            $transaction_request->extras = [
                'employee_name' => $this->selectedEmployee->user->name,
                'occupation' => $this->selectedEmployee->occupation,
                'salary' => Number::currency($this->selectedEmployee->salary, 'PHP'),
                'salary_type' => $this->selectedEmployee->salary_type->name,
                'days_worked' => $this->days_worked ?? null,
                'deductions' => Number::currency($this->deductions, 'PHP')
            ];

            $transaction_request->save();

            DB::commit();

            $this->displayConfirmation = false;

            $this->successMessage = "Salary payment has been sent for approval.";

            $this->reset(['selectedEmployee']);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error("MerchantFinancialTransactionPayrollCreate.send_salary_for_approval: " . $ex->getMessage());
            return $this->failedMessage = "An error has occurred. Please try again later.";
        }
    }

    private function send_salary()
    {
        $validated = $this->validate_salary_details();

        if (!$validated) {
            return;
        }

        $recipient = $this->selectedEmployee->user;

        $transaction = new Transaction;

        $transaction->sender_id = $this->merchant->id;
        $transaction->sender_type = Merchant::class;
        $transaction->recipient_id = $recipient->id;
        $transaction->recipient_type = User::class;
        $transaction->currency = 'PHP';
        $transaction->amount = $this->net_pay;
        $transaction->service_fee = 0;
        $transaction->rate = 1;

        DB::beginTransaction();
        try {
            $provider = TransactionProvider::where('code', 'RPY')->firstOrFail();
            $channel = TransactionChannel::where('code', 'RPY')->firstOrFail();
            $type = TransactionType::where('code', 'PS')->firstOrFail();
            $status = TransactionStatus::where('slug', 'successful')->firstOrFail();

            $txn_no = $this->generate_transaction_number();
            $ref_no = $this->generate_transaction_reference_number($provider, $channel, $type);

            $transaction->transaction_provider_id = $provider->id;
            $transaction->transaction_channel_id = $channel->id;
            $transaction->transaction_type_id = $type->id;

            $transaction->txn_no = $txn_no;
            $transaction->ref_no = $ref_no;
            $transaction->transaction_status_id = $status->id;

            $transaction->save();

            $this->credit($this->merchant, $transaction);
            $this->debit($recipient, $transaction);

            $this->alert(
                $recipient,
                'transaction',
                $transaction->txn_no,
                'You have received a salary payment of PHP' . number_format($transaction->amount, 2) . ' from ' . $this->merchant->name . '. Transaction number: ' . $transaction->txn_no
            );

            DB::commit();

            $this->displayConfirmation = false;

            $this->successMessage = "Successfully transferred " . Number::currency($this->net_pay, 'PHP') . " to {$this->format_phone_number_for_display($recipient->phone_number, $recipient->phone_iso)}";
            $this->successAmount = $this->net_pay;

            $sender_balance = $this->merchant->latest_balance()->first();
            $this->successRemainingBal = number_format($sender_balance->amount, 2);

            $this->reset(['selectedEmployee']);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantFinancialTransactionPayrollCreate.send_salary: ' . $th->getMessage());
            $this->failedMessage = 'Something went wrong. Please try again later.';
            return;
        }
    }

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        $employees = $this->merchant->employees()
            ->with(['user.profile', 'salary_type'])
            ->whereHas('user', function ($query) {
                $query->whereNotNull('email_verified_at');
                $query->whereNotNull('phone_verified_at');
                $query->whereHas('profile', function ($profile) {
                    $profile->whereNot(function ($query) {
                        $query->where('status', 'rejected')->orWhere('status', 'deactivated');
                    });

                    if ($this->searchTerm) {
                        $profile->where(function ($q) {
                            $q->where('first_name', 'like', '%' . $this->searchTerm . '%');
                            $q->orWhere('surname', 'like', '%' . $this->searchTerm . '%');
                        });
                    }
                });
            })
            ->whereDoesntHave('access_level', function ($query) {
                $query->where('slug', 'owner');
            });

        $employees = $employees->paginate(4, ['*'], 'page', $this->currentPageNumber);

        $this->hasPages = $employees->hasPages();
        $this->totalPages = $employees->lastPage();

        return view('merchant.financial-transaction.payroll.merchant-financial-transaction-payroll-create')->with([
            'employees' => $employees
        ]);
    }
}
