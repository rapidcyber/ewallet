<?php

namespace App\Admin\Payroll;

use App\Models\AdminLog;
use App\Models\Balance;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithNumberGeneration;
use App\Traits\WithTransactionLimit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AdminPayrollSend extends Component
{
    use WithPagination, WithNumberGeneration, WithTransactionLimit, WithImage;

    public $searchTerm = '';
    #[Locked]
    public $currentPageNumber = 1;
    #[Locked]
    public $hasPages = false;
    #[Locked]
    public $totalPages = 1;
    #[Url(as: 'employee', keep: false)]
    public $selected_employee_id = null;
    #[Locked]
    public $selectedEmployee = null;
    public $salaryDetailErrorMessage = '';

    public $days_worked;
    public $deductions = 0;
    public $net_pay = 0;

    public $displayConfirmation = false;
    public $confirmationMessage = '';

    public $successMessage = '';
    public $successAmount = 0;
    public $successRemainingBal = 0;

    public $failedMessage = '';

    public function mount()
    {
        if ($this->selected_employee_id !== null) {
            $this->handleEmployeeSelect($this->selected_employee_id);
        }
    }

    #[Computed]
    public function merchant()
    {
        return Merchant::find(1);
    }

    #[Computed]
    public function balance_amount()
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
        if ($this->selectedEmployee && $this->selectedEmployee->id === $employee_id) {
            $this->reset(['selectedEmployee', 'selected_employee_id']);
            return;
        }

        $this->selectedEmployee = Employee::where('merchant_id', $this->merchant->id)
            ->whereHas('access_level', function ($query) {
                $query->where('slug', '!=', 'owner');
            })
            ->with(['user.profile', 'salary_type'])
            ->find($employee_id);

        if (!$this->selectedEmployee) {
            session()->flash('error', 'Employee not found');
            return;    
        }

        $this->days_worked = 1;
        $this->deductions = 0;
        $this->net_pay = $this->selectedEmployee->salary;

        $this->selected_employee_id = $employee_id;
    }

    public function clearEmployeeSelect()
    {
        $this->reset(['selectedEmployee']);
    }

    public function updatedDaysWorked()
    {
        $this->validate([
            'days_worked' => 'numeric|min:1',
        ]);

        if ($this->selectedEmployee && $this->selectedEmployee->salary_type->slug === 'per_day') {
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
        $this->confirmationMessage = 'You are about to send ' . $this->selectedEmployee->user->name . ' a salary payment of ₱' . number_format($this->net_pay, 2);
        $this->displayConfirmation = true;
    }

    public function validate_salary_details(): bool
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
            $net_pay = ($this->selectedEmployee->salary * $this->days_worked) - $this->deductions;
        } else {
            $net_pay = $this->selectedEmployee->salary - $this->deductions;
        }

        $type = TransactionType::where('code', 'PS')->firstOrFail();
        if ($this->check_outbound_limit($this->merchant, $type, $net_pay) == true) {
            $this->displayConfirmation = false;
            $this->failedMessage = 'Merchant account has reached the monthly transaction limit';
            return false;
        }

        $balance = $this->merchant->latest_balance()->first() ?? new Balance;

        if ($balance->amount < $net_pay) {
            $this->displayConfirmation = false;
            $this->failedMessage = 'Insufficient balance';
            return false;
        }

        return true;
    }

    public function send_salary()
    {
        $validated = $this->validate_salary_details();

        if (!$validated) {
            return;
        }

        $transaction = new Transaction;

        $transaction->sender_id = $this->merchant->id;
        $transaction->sender_type = Merchant::class;
        $transaction->recipient_id = $this->selectedEmployee->user->id;
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

            $sender_balance = new Balance;
            $sender_balance->entity_id = $this->merchant->id;
            $sender_balance->entity_type = Merchant::class;
            $sender_balance->amount = $this->merchant->latest_balance->amount - $this->net_pay;
            $sender_balance->transaction_id = $transaction->id;
            $sender_balance->save();

            $recipient = User::with('latest_balance')->findOrFail($this->selectedEmployee->user->id);

            $recipient_balance = new Balance;
            $recipient_balance->entity_id = $recipient->id;
            $recipient_balance->entity_type = User::class;
            $recipient_balance->amount = ($recipient->latest_balance?->amount ?? 0) + $this->net_pay;
            $recipient_balance->transaction_id = $transaction->id;
            $recipient_balance->save();

            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Sent salary to user ' . $recipient->id;
            $log->description = 'Sent net salary of PHP ' . number_format($this->net_pay, 2) . ' with deductions of PHP ' . number_format($this->deductions, 2);
            $log->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminPayrollSend.send_salary: ' . $th->getMessage());
            $this->failedMessage = 'Something went wrong. Please try again later.';
            return;
        }

        $this->displayConfirmation = false;

        $this->successMessage = "Successfully transferred PHP " . number_format($this->net_pay, 2) . " to $recipient->phone_iso$recipient->phone_number";
        $this->successAmount = $this->net_pay;
        $this->successRemainingBal = number_format($sender_balance->amount, 2);

        $this->reset(['selectedEmployee']);
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $employees = Employee::where('merchant_id', $this->merchant->id)
            ->whereHas('access_level', function ($query) {
                $query->where('slug', '!=', 'owner');
            })
            ->with(['user.profile', 'user.profile_picture', 'salary_type']);

        if ($this->searchTerm) {
            $employees = $employees->whereHas('user', function ($query) {
                $query->whereHas('profile', function ($query) {
                    $query->where('first_name', 'like', '%' . $this->searchTerm . '%');
                    $query->orWhere('surname', 'like', '%' . $this->searchTerm . '%');
                });
            });
        }

        $employees = $employees->paginate(4, ['*'], 'page', $this->currentPageNumber);

        $this->hasPages = $employees->hasPages();
        $this->totalPages = $employees->lastPage();

        return view('admin.payroll.admin-payroll-send')->with([
            'employees' => $employees,
        ]);
    }
}
