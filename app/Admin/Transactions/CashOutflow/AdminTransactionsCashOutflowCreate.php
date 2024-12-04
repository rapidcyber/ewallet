<?php

namespace App\Admin\Transactions\CashOutflow;

use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\Traits\WithStringManipulation;
use App\Traits\WithAllBankFunctions;
use App\Traits\WithBalance;
use App\Traits\WithECPayFunctions;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use App\Traits\WithTransactionLimit;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class AdminTransactionsCashOutflowCreate extends Component
{
    use WithValidPhoneNumber, WithTransactionLimit, WithBalance, WithNumberGeneration, WithNotification, WithImage, WithStringManipulation;
    use WithAllBankFunctions, WithECPayFunctions;

    public User $user;

    #[Url(as: 'type')]
    public $transaction_type;
    public $transfer_to = 'another-account';

    public $amount;

    // For Money Transfer - Another Account
    public $phone_iso = 'PH';
    public $phone_number;
    public $message = '';

    // For Money Transfer - Another Bank
    public $send_via = 'instapay';
    public $selected_bank;
    public $account_number;
    public $account_name;
    public $email;

    // For Bill Payment
    public $biller_type;
    public $selected_biller;
    public $bill_info = [];

    #[Locked]
    public $service_charge = null;
    #[Locked]
    public $biller_description = '';

    public $agreed_to_correct_info = false;

    public $apiSuccessMsg = '';
    public $apiErrorMsg = '';

    protected $allowed_transaction_types = [
        'money-transfer',
        'bill-payment',
    ];

    protected $allowed_transfer_to = [
        'another-account',
        // 'unionbank-account',
        'another-bank',
    ];

    protected $allowed_send_via = [
        'instapay',
        'pesonet',
    ];

    public function mount()
    {
        $this->user = auth()->user();

        if (empty($this->transaction_type) || ! in_array($this->transaction_type, $this->allowed_transaction_types)) {
            $this->transaction_type = 'money-transfer';
        }
    }

    #[Computed]
    public function phone_isos()
    {
        return [
            'PH' => '63',
            'SG' => '65',
        ];
    }

    #[Computed]
    public function get_phone_iso()
    {
        if (isset($this->phone_isos[$this->phone_iso])) {
            return $this->phone_isos[$this->phone_iso];
        }

        return '';
    }

    #[Computed]
    public function available_balance()
    {
        return $this->user->latest_balance()->first()->amount ?? 0;
    }

    // For Money Transfer
    #[Computed(persist: true)]
    public function instapay_banks_list()
    {
        [$token, $tdt] = $this->generate_token();
        $str_xml = $this->generate_xml_string([
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'I-LST-BNK',
        ]);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return [];
        }

        $data = $this->get_xml_contents($response, false);
        if (empty($data['@attributes']['ErrorMsg']) == false) {
            return [];
        }

        $banks = [];

        foreach ($data['banks']['i'] as $bank) {
            $banks[] = [
                'code' => $bank['@attributes']['code'],
                'name' => $bank['@attributes']['name'],
            ];
        }

        return $banks;
    }

    #[Computed(persist: true)]
    public function pesonet_banks_list()
    {
        [$token, $tdt] = $this->generate_token();
        $str_xml = $this->generate_xml_string([
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'P-LST-BNK',
        ]);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return [];
        }

        $data = $this->get_xml_contents($response, false);
        if (empty($data['@attributes']['ErrorMsg']) == false) {
            return [];
        }

        $banks = [];

        foreach ($data['banks']['i'] as $bank) {
            $banks[] = [
                'code' => $bank['@attributes']['code'],
                'name' => $bank['@attributes']['name'],
            ];
        }

        return $banks;
    }

    #[Computed]
    public function get_bank_name()
    {
        if ($this->send_via === 'instapay' and $key = array_search($this->selected_bank, array_column($this->instapay_banks_list, 'code'))) {
            return $this->instapay_banks_list[$key]['name'];
        }

        if ($this->send_via === 'pesonet' and $key = array_search($this->selected_bank, array_column($this->pesonet_banks_list, 'code'))) {
            return $this->pesonet_banks_list[$key]['name'];
        }

        return '';
    }

    #[Computed]
    public function get_user()
    {
        if (!isset($this->phone_isos[$this->phone_iso])) {
            return [
                'name' => '-',
                'profile_picture' => url('images/user/default-avatar.png')
            ];
        }

        $phone_number = $this->phone_isos[$this->phone_iso] . $this->phone_number;

        $user = User::active()->where('phone_number', $phone_number)->with(['profile'])->first();

        if (!$user) {
            return [
                'name' => '-',
                'profile_picture' => url('images/user/default-avatar.png')
            ];
        }

        return [
            'name' => $this->mask_name($user->name),
            'profile_picture' => url('images/user/default-avatar.png'),
        ];
    }

    public function updatedTransactionType()
    {
        if (! in_array($this->transaction_type, $this->allowed_transaction_types)) {
            $this->transaction_type = 'money-transfer';
        }

        $this->reset([
            'phone_iso',
            'phone_number',
            'amount',
            'message',
            'agreed_to_correct_info',
            'transfer_to',
            'send_via',
            'selected_bank',
            'account_number',
            'account_name',
            'selected_biller',
            'bill_info',
        ]);
    }

    public function updatedTransferTo()
    {
        if (! in_array($this->transfer_to, $this->allowed_transfer_to)) {
            $this->transfer_to = 'another-account';
        }

        $this->reset([
            'phone_iso',
            'phone_number',
            'amount',
            'message',
            'agreed_to_correct_info',
            'send_via',
        ]);
    }

    public function updatedSendVia()
    {
        if (! in_array($this->send_via, $this->allowed_send_via)) {
            $this->send_via = 'instapay';
        }

        $this->selected_bank = '';
    }


    // For Bill Payment
    #[Computed(persist: true)]
    public function billers_list()
    {
        $token = $this->generate_ecpay_token();
        $response = $this->ecpay_get_request('/api/v1/Ecbills/Billers', [], $token);
        if ($response->failed()) {
            return [];
        }

        $data = json_decode($response->body(), true)['Data'];
        foreach ($data as $key => $value) {
            if (isset($value['Category'])) {
                $data[$key]['Category'] = str_replace(["\r", "\n"], '', $value['Category']);
                $data[$key]['Description'] = str_replace(["\r", "\n"], '', $value['Description']);
            }
        }

        return $data;
    }

    #[Computed]
    public function billers_list_display()
    {
        $billers = $this->billers_list;
        $billers_list = array_reduce($billers, function ($biller, $element) {
            $biller[$element['Category']][] = [
                'name' => $element['Description'],
                'tag' => $element['BillerTag'],
            ];
            sort($biller[$element['Category']]);
            return $biller;
        }, []);

        return $billers_list;
    }

    public function updatedSelectedBiller()
    {
        $this->reset('bill_info');
    }

    #[Computed]
    public function get_biller_input_fields()
    {
        if (!$this->biller_type) {
            return [];
        }

        $key = array_search($this->biller_type, array_column($this->billers_list, 'Description'));

        if ($key === false) {
            return [];
        }

        $field_details = $this->billers_list[$key]['FieldDetails'] ?? [];
        $old_value = $this->biller_type;
        $this->biller_type = $this->billers_list[$key]['Description'] ?? $old_value;

        if (empty($field_details)) {
            return [];
        }

        $input_fields = [];

        foreach ($field_details as $key => $value) {
            $input_fields[] = [
                'label' => $value['Caption'],
                'format' => match ($value['Format']) {
                    'Numeric' => 'numeric',
                    'Alphanumeric' => 'string',
                    default => 'string',
                },
                'maxlength' => $value['Width'],
            ];
        }

        return $input_fields;
    }

    #[Computed]
    public function get_biller_name()
    {
        if (empty($this->biller_type)) {
            return '-';
        }

        if ($key = array_search($this->biller_type, array_column($this->billers_list, 'Description'))) {
            return $this->billers_list[$key]['Description'];
        }

        return '-';
    }

    #[Computed]
    public function get_biller_description()
    {
        if ($key = array_search($this->biller_type, array_column($this->billers_list, 'Description'))) {
            $description = $this->billers_list[$key]['Remarks'];
            return str_replace('&nbsp;', '. ', $description); 
        }

        return '';
    }

    #[Computed]
    public function get_biller_service_charge()
    {
        if ($key = array_search($this->biller_type, array_column($this->billers_list, 'Description'))) {
            return $this->billers_list[$key]['ServiceCharge'];
        }

        return '';
    }


    #[Computed]
    public function button_submit_clickable()
    {
        $rules = [];
        if ($this->transaction_type == 'money-transfer') {
            if ($this->transfer_to == 'another-account') {
                $rules = [
                    'phone_iso' => 'required',
                    'phone_number' => 'required',
                ];
            } elseif ($this->transfer_to == 'another-bank') {
                $rules = [
                    'send_via' => 'required',
                    'selected_bank' => 'required',
                    'account_number' => 'required',
                    'account_name' => 'required',
                ];
            }
        } elseif ($this->transaction_type == 'bill-payment') {
            $rules = [
                'selected_biller' => 'required',
                'bill_info' => 'required|array',
                'bill_info.*' => 'required',
            ];
        }

        try {
            $this->validate([
                'agreed_to_correct_info' => 'accepted',
                'amount' => 'required|numeric|min:0.01|max:' . $this->available_balance,
                ...$rules
            ]);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

    public function submit()
    {
        if ($this->transaction_type == 'money-transfer') {
            if ($this->transfer_to == 'another-account') {
                $this->money_transfer_account();
            } elseif ($this->transfer_to == 'another-bank') {
                $this->money_transfer_bank();
            }
        } elseif ($this->transaction_type == 'bill-payment') {
            $this->bill_payment();
        } else {
            $this->apiErrorMsg = "Invalid submit action";
        }
    }

    private function money_transfer_account()
    {
        $this->validate([
            'phone_iso' => 'required|in:' . implode(',', array_keys($this->phone_isos)),
            'phone_number' => ['required', 'numeric', function ($attribute, $value, $fail) {
                if (! $this->phonenumber_info($value, $this->phone_iso) && ! in_array($this->phone_iso, array_keys($this->phone_isos))) {
                    $fail('Invalid phone number');
                }
                $phone_number = $this->phone_isos[$this->phone_iso] . $value;
                $user = User::active()->where('phone_number', $phone_number)->first();
                if (! $user) {
                    $fail('Number is not registered to repay.');
                }
            }],
            'amount' => 'required|numeric|min:0.01|max:' . $this->available_balance,
            'message' => 'nullable|string|max:255',
        ]);

        $recipient = User::where('phone_number', $this->phone_isos[$this->phone_iso] . $this->phone_number)
            ->with(['profile'])
            ->firstOrFail();

        if ($this->user->id == $recipient->id) {
            return $this->addError('phone_number', 'Invalid phone number');
        }

        $type_transfer = TransactionType::where('code', 'TR')->firstOrFail();

        if ($this->is_sufficient($this->user, $this->amount) == false) {
            return $this->addError('amount', 'Amount is beyond your balance');
        }

        if ($this->check_outbound_limit($this->user, $type_transfer, $this->amount) == true) {
            return $this->addError('amount', 'Insufficient balance');
        }

        if ($this->check_inbound_limit($recipient, $type_transfer, $this->amount) == true) {
            return $this->addError('amount', 'The recipient will exceed the transaction limit with this amount');
        }

        if ($this->is_balance_limit_reached($recipient, $this->amount) == true) {
            return $this->addError('amount', 'The recipient will exceed the balance limit with this amount');
        }

        DB::beginTransaction();
        try {
            $amount = round($this->amount, 2);

            $provider_repay = TransactionProvider::where('code', 'RPY')->firstOrFail();
            $channel_repay = TransactionChannel::where('code', 'RPY')->firstOrFail();
            $status_successful = TransactionStatus::where('slug', 'successful')->firstOrFail();

            $transaction = new Transaction;

            $transaction->sender_id = $this->user->id;
            $transaction->sender_type = get_class($this->user);
            $transaction->recipient_id = $recipient->id;
            $transaction->recipient_type = get_class($recipient);
            $transaction->txn_no = $this->generate_transaction_number();
            $transaction->ref_no = $this->generate_transaction_reference_number($provider_repay, $channel_repay, $type_transfer);
            $transaction->transaction_provider_id = $provider_repay->id;
            $transaction->transaction_channel_id = $channel_repay->id;
            $transaction->transaction_type_id = $type_transfer->id;
            $transaction->transaction_status_id = $status_successful->id;
            $transaction->service_fee = 0;
            $transaction->currency = 'PHP';
            $transaction->amount = $amount;
            $transaction->save();

            $this->credit($this->user, $transaction);
            $this->debit($recipient, $transaction);

            $message = "You have received P" . number_format($transaction->amount, 2) . " from {$this->user->phone_number}.";

            if (!empty($this->message)) {
                $message = $message . " with message: " . $this->message . ".";
            } else {
                $message = $message . ".";
            }

            $message = $message . "\n\nTransaction no: " . $transaction->txn_no;

            $this->alert(
                recipient: $recipient,
                module_slug: 'transaction',
                ref_id: $transaction->txn_no,
                message: $message,
                extras: null
            );

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("AdminTransactionsCashOutflowCreate.money_transfer_account: " . $th->getMessage());
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }

        unset($this->available_balance);

        $this->reset([
            'phone_iso',
            'phone_number',
            'amount',
            'message',
            'agreed_to_correct_info',
        ]);
        return $this->apiSuccessMsg = 'Successfully transferred to ' . $recipient->name . ' ' . $recipient->phone_number . '. Ref no: ' . $transaction->ref_no;
    }

    private function money_transfer_bank()
    {
        $this->validate([
            'send_via' => 'required|in:' . implode(',', $this->allowed_send_via),
            'selected_bank' => ['required', function ($attribute, $value, $fail) {
                if (!in_array($value, array_column($this->send_via === 'instapay' ? $this->instapay_banks_list : $this->pesonet_banks_list, 'code'))) {
                    $fail('The selected bank is invalid');
                }
            }],
            'account_number' => 'required',
            'account_name' => 'required',
            'amount' => 'required|numeric|min:0.01|max:' . $this->available_balance,
            'phone_iso' => 'nullable|in:' . implode(',', array_keys($this->phone_isos)),
            'phone_number' => ['nullable', function ($attribute, $value, $fail) {
                if (!isset($this->phone_isos[$this->phone_iso])) {
                    $fail('Invalid phone number');
                }

                $phone_number = $this->phone_isos[$this->phone_iso] . $value;
                $check = $this->phonenumber_info($phone_number, $this->phone_iso);
                if ($check == false) {
                    $fail('Invalid phone number');
                }
            }],
            'email' => 'nullable|email:rfc,dns',
            'message' => 'nullable|string|max:255',
        ]);

        $this->apiErrorMsg = "Not implemented yet";
    }

    private function bill_payment()
    {
        $this->validate([
            'selected_biller' => 'required|in:' . implode(',', array_column($this->billers_list, 'BillerTag')),
            'bill_info' => 'required|size:' . count($this->get_biller_input_fields),
            'bill_info.*' => ['required', function ($attribute, $value, $fail) {
                $key = substr($attribute, -1);

                if (!isset($this->get_biller_input_fields[$key])) {
                    $fail('Invalid input');
                }

                $format = $this->get_biller_input_fields[$key]['format'];
                $max = $this->get_biller_input_fields[$key]['maxlength'];

                if ($format === 'numeric') {
                    if (!is_numeric($value)) {
                        $fail('The ' . $this->get_biller_input_fields[$key]['label'] . ' must be a number.');
                    }
                    if (strlen($value) > $max) {
                        $fail('The ' . $this->get_biller_input_fields[$key]['label'] . ' must be no longer than ' . $max . ' characters.');
                    }
                } elseif ($format === 'string') {
                    if (!is_string($value)) {
                        $fail('The ' . $this->get_biller_input_fields[$key]['label'] . ' must be a string.');
                    }
                    if (strlen($value) > $max) {
                        $fail('The ' . $this->get_biller_input_fields[$key]['label'] . ' must be no longer than ' . $max . ' characters.');
                    }
                }
            }],
            'amount' => 'required|numeric|min:0.01|max:' . $this->available_balance,
            'email' => 'nullable|email:rfc,dns',
        ]);

        $this->apiErrorMsg = "Not implemented yet";
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $billers_list = $this->billers_list;
        $billers = [];

        if ($this->transaction_type === 'bill-payment' and array_search($this->biller_type, array_column($this->billers_list, 'Description')) === false) {
            $billers = array_filter($billers_list, function ($biller) {
                return !$this->biller_type || strpos(strtolower($biller['Description']), strtolower($this->biller_type)) !== false;
            });
    
            $billers = array_map(function ($biller) {
                return [
                    'category' => $biller['Category'],
                    'code' => $biller['BillerTag'],
                    'description' => $biller['Remarks'],
                    'name' => $biller['Description'],
                    'service_charge' => $biller['ServiceCharge'],
                    'status' => $biller['Status'],
                ];
            }, $billers);
    
            usort($billers, function ($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
        } else {
            $billers = [];
        }

        return view('admin.transactions.cash-outflow.admin-transactions-cash-outflow-create')->with([
            'billers' => $billers,
        ]);
    }
}
