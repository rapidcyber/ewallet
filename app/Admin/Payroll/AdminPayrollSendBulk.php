<?php

namespace App\Admin\Payroll;

use App\Models\AdminLog;
use App\Models\Balance;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\WithNumberGeneration;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class AdminPayrollSendBulk extends Component
{
    use WithFileUploads, WithValidPhoneNumber, WithNumberGeneration;

    public Merchant $merchant;
    public $uploaded_csv_file;

    #[Locked]
    public $file_data = [];
    #[Locked]
    public $allowSubmit = false;
    #[Locked]
    public $total_salary = 0;
    #[Locked]
    public $headers = ['Employee Name', 'Phone Number', 'Base Salary', 'Deduction', 'Net Pay'];

    #[Locked]
    public $savingErr = [];
    #[Locked]
    public $showSaveStatus = false;
    public $savingDone = false;

    public $apiErrorMsg = '';

    public function mount()
    {
        $this->merchant = Merchant::find(1);
    }

    #[Computed]
    public function balance_amount()
    {
        return $this->merchant->latest_balance()->first()->amount ?? 0;
    }

    public function downloadFormat()
    {
        $filePath = public_path("files/Repay Payroll Format.xlsx");
        return response()->download($filePath);
    }

    #[Computed]
    public function employees()
    {
        return $this->merchant->employees()->pluck('user_id')->toArray();
    }

    public function updatedUploadedCsvFile()
    {
        $this->validate([
            'uploaded_csv_file' => 'required|file|extensions:csv,txt|mimes:csv,txt',
        ]);

        $this->file_data = [];
        $data = array_map('str_getcsv', file($this->uploaded_csv_file->path()));
        $headers = array_shift($data);

        if ($headers !== $this->headers) {
            $this->addError('uploaded_csv_file', 'Invalid headers. Please check your file and try again.');
            return;
        }

        $validator = Validator::make($data, [
            '*' => 'required|array|size:5',
            '*.0' => 'required|distinct',
            '*.1' => 'required|distinct',
            "*.2" => 'required|numeric|min:1|max:500000|regex:/^\d*(\.\d{2})?$/',
            "*.3" => 'required|numeric|min:1|max:500000|regex:/^\d*(\.\d{2})?$/',
            "*.4" => 'required|numeric|min:1|max:500000|regex:/^\d*(\.\d{2})?$/',
        ], [
            "required" => '- Missing: ',
            "distinct" => '- Duplicated: ',
            "numeric" => '- Must only contain numbers: ',
            "min" => '- Minimum value is 1: ',
            "max" => "- Value must not exceed 500,000: ",
        ]);

        $errs = $validator->errors()->toArray();

        $disable_submit = false;

        $rows = [];

        $employee_numbers = array_map(function ($row) {
            $parsed = $this->phonenumber_info($row);
            if (!$parsed) {
                return '';
            }
            return $parsed->getCountryCode() .  $parsed->getNationalNumber();
        }, array_column($data, 1));

        $employee_numbers = array_filter($employee_numbers, function ($row) {
            return $row !== '';
        });

        $users = User::active()
            ->select(['id', 'phone_number'])
            ->with(['profile:id,user_id,first_name,surname,status', 'employee' => function ($q) {
                $q->where('merchant_id', $this->merchant->id);
            }])
            ->whereIn('phone_number', $employee_numbers)
            ->get();

        foreach ($data as $key => $row) {
            $net_salary = floatval($row[2]) - floatval($row[3]);
            $row[4] = number_format($net_salary, 2);

            $this->total_salary += $net_salary;

            $row['Remarks'] = [];
            $row['Errors'] = [];

            if (empty($row[1])) {
                $row['Remarks'][] = 'Missing mobile number.';
                $row['Errors'][] = 1;
                $disable_submit = true;
                $rows[] = $row;
                continue;
            }

            $check_phone = $this->phonenumber_info($row[1]);
            if (!$check_phone) {
                $row['Remarks'][] = 'Invalid mobile number.';
                $row['Errors'][] = 1;
                $disable_submit = true;
                $rows[] = $row;
                continue;
            }

            $phone_number = $check_phone->getCountryCode() . $check_phone->getNationalNumber();

            $user = $users->where('phone_number', $phone_number)->first();
            if (!$user) {
                $row['Remarks'][] = "Mobile number is not registered to Repay.";
                $row['Errors'][] = 1;
                $disable_submit = true;
            } else {
                $employee = $user->employee->first();

                if (empty($employee)) {
                    $row['Remarks'][] = 'Mobile number does not belong to any of your employee.';
                    $row['Errors'][] = 1;
                    $disable_submit = true;
                } else {
                    $row['user_id'] = $user->id;
                    $row[0] = $row[0] . ' (' . $user->name . ')';
                }
            }

            $rows[] = $row;
        }

        $errs = $validator->errors()->toArray();
        foreach ($errs as $key => $err) {
            $keys = explode('.', $key);
            $rowIdx = $keys[0];
            $headerKey = $keys[1];

            if (in_array($err[0], $rows[$rowIdx]['Remarks']) == false) {
                array_push($rows[$rowIdx]['Remarks'], "$err[0] " . $this->headers[$headerKey]);
            }

            array_push($rows[$rowIdx]['Errors'], $headerKey);
        }

        $this->file_data = $rows;
        $this->allowSubmit = $disable_submit ? false : true;
    }

    public function save()
    {
        if ($this->total_salary > $this->balance_amount) {
            $this->apiErrorMsg = 'Insufficient balance.';
            return;
        }

        $provider = TransactionProvider::where('code', 'RPY')->first();
        $channel = TransactionChannel::where('code', 'RPY')->first();
        $type = TransactionType::where('code', 'PS')->first();
        $status = TransactionStatus::where('slug', 'successful')->first();

        foreach ($this->file_data as $key => $row) {
            if ($this->showSaveStatus && !in_array($key, $this->savingErr) == false) {
                continue;
            }

            $employee = $this->merchant->employees()->where('user_id', $row['user_id'])->with('user.latest_balance')->first();

            DB::beginTransaction();
            try {
                $amount = floatval($row[2]) - floatval($row[3]);

                $transaction = new Transaction;

                $transaction->sender_id = $this->merchant->id;
                $transaction->sender_type = Merchant::class;
                $transaction->recipient_id = $employee->user->id;
                $transaction->recipient_type = User::class;
                $transaction->currency = 'PHP';
                $transaction->amount = $amount;
                $transaction->service_fee = 0;
                $transaction->rate = 1;
                $txn_no = $this->generate_transaction_number();
                $ref_no = $this->generate_transaction_reference_number($provider, $channel, $type);
                $transaction->transaction_provider_id = $provider->id;
                $transaction->transaction_channel_id = $channel->id;
                $transaction->transaction_type_id = $type->id;

                $transaction->txn_no = $txn_no;
                $transaction->ref_no = $ref_no;
                $transaction->transaction_status_id = $status->id;

                $transaction->save();

                $merchant_balance = new Balance;
                $merchant_balance->entity_id = $this->merchant->id;
                $merchant_balance->entity_type = Merchant::class;
                $merchant_balance->amount = $this->merchant->latest_balance()->first()->amount - $amount;
                $merchant_balance->transaction_id = $transaction->id;
                $merchant_balance->save();

                $recipient = $employee->user;

                $recipient_balance = new Balance;
                $recipient_balance->entity_id = $recipient->id;
                $recipient_balance->entity_type = User::class;
                $recipient_balance->amount = ($recipient->latest_balance?->amount ?? 0) + $amount;
                $recipient_balance->transaction_id = $transaction->id;
                $recipient_balance->save();

                $log = new AdminLog;
                $log->user_id = auth()->id();
                $log->title = 'Bulk sent salary to user ' . $recipient->id;
                $log->description = 'Sent net salary of PHP ' . number_format($amount, 2) . ' with deductions of PHP ' . number_format(floatval($row[3]), 2);

                $log->save();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                $this->savingErr[] = $key;
                Log::error('AdminPayrollSendBulk.save: ' . $th->getMessage());
            }
        }

        $this->savingDone = $this->showSaveStatus = true;
        unset($this->balance_amount);
    }

    public function cancel_upload()
    {
        $this->reset([
            'uploaded_csv_file',
            'file_data',
            'allowSubmit',
            'total_salary',
        ]);
    }
    #[Layout('layouts.admin')]
    public function render()
    {
        return view('admin.payroll.admin-payroll-send-bulk');
    }
}
