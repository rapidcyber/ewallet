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
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class MerchantFinancialTransactionPayrollBulkUpload extends Component
{
    use WithFileUploads, WithValidPhoneNumber, WithNumberGeneration, WithBalance, WithNotification;

    public Merchant $merchant;
    public Employee $employee_user;
    public $uploaded_csv_file;
    public $modal_visible = false;

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

    #[Locked]
    public $need_approval = true;

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        $this->employee_user = $this->merchant->employees()->where('employees.user_id', auth()->id())->first();

        if (Gate::allows('merchant-cash-outflow', [$this->merchant, 'approve'])) {
            $this->need_approval = false;
        }
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
        $data = array_map(function ($row) {
            return array_values(array_filter(array_map(function ($cell, $index) {
                return $index === 0 ? trim($cell) : ($index > 2 ? (float) trim(preg_replace('/[\s\,\-+()]/', '', $cell)) : trim(preg_replace('/[\s\,\-+()]/', '', $cell)));
            }, $row, array_keys($row))));
        }, array_slice(array_filter(array_map('str_getcsv', file($this->uploaded_csv_file->path())), function ($row) {
            return array_filter($row);
        }), 1));

        $validator = Validator::make($data, [
            '*' => 'required|array|size:5',
            '*.0' => 'required|distinct',
            '*.1' => 'required|distinct',
            "*.2" => 'required|numeric|min:1|max:500000',
            "*.3" => 'required|numeric|min:1|max:500000',
            "*.4" => 'required|numeric|min:1|max:500000',
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

        $employee_numbers = array_column($data, 1);

        $employee_numbers = array_map(function ($num) {
            return str_replace([' ', '+', '-', '(', ')'], '', $num);
        }, $employee_numbers);

        $users = User::select(['id', 'phone_number'])
            ->with([
                'profile:id,user_id,first_name,surname',
                'employee' => function ($q) {
                    $q->where('merchant_id', $this->merchant->id);
                }
            ])
            ->whereHas('employee', function ($q) {
                $q->where('merchant_id', $this->merchant->id);
            })
            ->whereIn('phone_number', $employee_numbers)
            ->get();

        foreach ($data as $key => $row) {
            $net_salary = floatval($row[2]) - floatval($row[3]);
            $row[4] = number_format($net_salary, 2);

            $this->total_salary += $net_salary;

            $row['Remarks'] = [];
            $row['Errors'] = [];

            if (!empty($row[1])) {
                $phone_number = str_replace([' ', '+', '-', '(', ')'], '', $row[1]);

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
            } else {
                $row['Remarks'][] = 'Missing mobile number.';
                $row['Errors'][] = 1;
                $disable_submit = true;
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
        if ($this->need_approval) {
            return $this->save_for_approval();
        }

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

                $this->credit($this->merchant, $transaction);

                $recipient = $employee->user;

                $this->debit($recipient, $transaction);
                $this->alert(
                    $recipient,
                    'transaction',
                    $transaction->txn_no,
                    'You have received a payroll payment of PHP ' . number_format($transaction->amount, 2) . ' from ' . $this->merchant->name . "Transaction No: {$transaction->txn_no}",

                );

                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                $this->savingErr[] = $key;
                Log::error('MerchantFinancialTransactionPayrollBulkUpload - save - ' . $ex->getMessage());
                return;
            }
        }

        session()->flash('success', 'Bulk upload successful.');
        session()->flash('success_message', 'Uploaded payroll has been successfully processed.');
        return $this->redirect(route('merchant.financial-transactions.payroll.bulk-upload', ['merchant' => $this->merchant]));
    }

    private function save_for_approval()
    {
        $provider = TransactionProvider::where('code', 'RPY')->first();
        $channel = TransactionChannel::where('code', 'RPY')->first();
        $type = TransactionType::where('code', 'PS')->first();

        foreach ($this->file_data as $key => $row) {
            if ($this->showSaveStatus && !in_array($key, $this->savingErr) == false) {
                continue;
            }

            $employee = $this->merchant->employees()->where('user_id', $row['user_id'])->with(['user.profile', 'salary_type'])->first();

            DB::beginTransaction();
            try {
                $amount = floatval($row[2]) - floatval($row[3]);

                $transaction_request = new TransactionRequest;

                $transaction_request->merchant_id = $this->merchant->id;
                $transaction_request->recipient_id = $employee->user->id;
                $transaction_request->recipient_type = User::class;
                $transaction_request->transaction_provider_id = $provider->id;
                $transaction_request->transaction_channel_id = $channel->id;
                $transaction_request->transaction_type_id = $type->id;
                $transaction_request->service_fee = 0;
                $transaction_request->currency = 'PHP';
                $transaction_request->amount = $amount;
                $transaction_request->created_by = $this->employee_user->id;
                $transaction_request->extras = [
                    'employee_name' => $employee->user->name,
                    'occupation' => $employee->occupation,
                    'salary' => Number::currency(floatval($row[2]), 'PHP'),
                    'salary_type' => null,
                    'days_worked' => null,
                    'deductions' => Number::currency(floatval($row[3]), 'PHP')
                ];

                $transaction_request->save();

                DB::commit();
                session()->flash('success', 'Bulk upload successful.');
                session()->flash('success_message', 'Uploaded payroll has been successfully saved for approval.');
                return $this->redirect(route('merchant.financial-transactions.payroll.bulk-upload', ['merchant' => $this->merchant]));
            } catch (\Exception $ex) {
                DB::rollBack();
                $this->savingErr[] = $key;
                Log::error('MerchantFinancialTransactionPayrollBulkUpload - save_for_approval - ' . $ex->getMessage());
                return;
            }
        }
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

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        return view('merchant.financial-transaction.payroll.merchant-financial-transaction-payroll-bulk-upload');
    }
}
