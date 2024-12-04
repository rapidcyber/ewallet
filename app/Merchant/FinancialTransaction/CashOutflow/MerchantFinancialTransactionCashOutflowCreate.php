<?php

namespace App\Merchant\FinancialTransaction\CashOutflow;

use App\Models\BillingRequest;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\OTP;
use App\Models\SystemService;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionRequest;
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
use App\Traits\WithOTP;
use App\Traits\WithServiceFee;
use App\Traits\WithTransactionLimit;
use App\Traits\WithValidPhoneNumber;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class MerchantFinancialTransactionCashOutflowCreate extends Component
{
    use WithValidPhoneNumber, WithTransactionLimit, WithBalance, WithNumberGeneration, WithNotification, WithImage, WithStringManipulation, WithOTP;
    use WithAllBankFunctions, WithECPayFunctions;
    use WithServiceFee;
    
    public Merchant $merchant;
    public Employee $employee;

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
    public $can_pay_bill = false;

    #[Locked]
    public $service_charge = null;
    #[Locked]
    public $biller_description = '';

    #[Locked]
    public $verification_id = null;
    #[Locked]
    public $resend_available_at = null;
    public $otp;
    public $show_otp_modal = false;

    public $agreed_to_correct_info = false;

    public $apiSuccessMsg = '';
    public $apiErrorMsg = '';

    #[Locked]
    public $outflow_needs_approval = true;

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

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        $this->employee = $merchant->employees()->where('user_id', auth()->id())->firstOrFail();
        
        if (empty($this->transaction_type) || ! in_array($this->transaction_type, $this->allowed_transaction_types)) {
            $this->transaction_type = 'money-transfer';
        }

        if (Gate::allows('merchant-bills', [$this->merchant, 'approve'])) {
            $this->can_pay_bill = true;
        }

        $this->check_service_availability();

        if (Gate::allows('merchant-cash-outflow', [$this->merchant, 'approve'])) {  
            $this->outflow_needs_approval = false;
        }
    }

    private function check_service_availability()
    {
        if ($this->transaction_type == 'bill-payment') {
            $system_service = SystemService::where('slug', 'bills_management')->first();
            if ($system_service->availability !== 'active') {
                $this->transaction_type = 'money-transfer';
                return session()->flash('warning', 'Bill Payment is currently not available.');
            }
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
        return $this->merchant->latest_balance()->first()->amount ?? 0;
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
    public function service_fee()
    {
        if ($this->transfer_to === 'another-bank' && $this->send_via === 'instapay') {
            $provider = TransactionProvider::where('slug', 'allbank')->first();
            $channel = TransactionChannel::where('slug', 'instapay')->first();

            return $this->get_service_fee($provider, $channel);
        } elseif ($this->transfer_to === 'another-bank' && $this->send_via === 'pesonet') {
            $provider = TransactionProvider::where('slug', 'allbank')->first();
            $channel = TransactionChannel::where('slug', 'pesonet')->first();

            return $this->get_service_fee($provider, $channel);
        }

        return 0;
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

        $this->check_service_availability();

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
    #[Computed(persist: true, seconds: 7200, cache: true, key: 'ecpay_billers_list')]
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

    public function updatedSearchBiller()
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
    public function get_biller_category()
    {
        if (empty($this->biller_type)) {
            return '-';
        }

        if ($key = array_search($this->biller_type, array_column($this->billers_list, 'Description'))) {
            return $this->billers_list[$key]['Category'];
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

    #[On('closeModal')]
    public function closeModal()
    {
        $this->show_otp_modal = false;
        $this->verification_id = null;
        $this->resend_available_at = null;
        $this->otp = null;
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
                'biller_type' => 'required',
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
            if (Gate::denies('merchant-bills', [$this->merchant, 'create'])) {
                $this->apiErrorMsg = "You don't have permission to create bills.";
            }
    
            if (Gate::allows('merchant-bills', [$this->merchant, 'approve'])) {  
                $this->bill_payment();
            } else {
                $this->bill_request();
            }
        } else {
            $this->apiErrorMsg = "Invalid submit action";
        }
    }

    public function resend_otp()
    {
        if ($this->resend_available_at && now()->isBefore($this->resend_available_at)) {
            return session()->flash('warning', 'Warning: Please wait for ' . $this->resend_available_at->diffForHumans(now()) . ' before requesting a new OTP.');
        }

        $response = $this->send_otp();

        if ($response == false) {
            return session()->flash('error', 'Error sending OTP. Please try again later.');
        }
    }

    private function send_otp()
    {
        $response = $this->generate_otp(User::find(auth()->id()), 'transaction');
            
        if ($response == null) {
            Log::error('MerchantFinancialTransactionCashOutflowCreate.send_otp - Error generating OTP');
            return false;
        }

        if (!empty($response['code'])) {
            $this->otp = $response['code'];
        }

        $this->verification_id = $response['verification_id'];
        $this->resend_available_at = now()->addMinutes(3);
        return true;
    }

    private function verify_otp()
    {
        try {
            $this->validate(['otp' => 'required|string|size:6']);
        } catch (ValidationException $ex) {
            $this->setErrorBag($ex->validator->errors());
            return false;
        }

        $otp_data = OTP::where([
            'verification_id' => $this->verification_id,
            'contact' => auth()->user()->phone_number,
            'code' => $this->otp,
            'type' => 'transaction',
            'verified_at' => null
        ])->first();

        if (empty($otp_data)) {
            $this->addError('otp', 'The OTP is invalid.');
            return false;
        }
        
        return true;
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
        
        $type_transfer = TransactionType::where('code', 'TR')->firstOrFail();

        if ($this->is_sufficient($this->merchant, $this->amount) == false) {
            return $this->addError('amount', 'Amount is beyond your balance');
        }

        if ($this->check_outbound_limit($this->merchant, $type_transfer, $this->amount) == true) {
            return $this->addError('amount', 'Insufficient balance');
        }

        if ($this->check_inbound_limit($recipient, $type_transfer, $this->amount) == true) {
            return $this->addError('amount', 'The recipient will exceed the transaction limit with this amount');
        }

        if ($this->is_balance_limit_reached($recipient, $this->amount) == true) {
            return $this->addError('amount', 'The recipient will exceed the balance limit with this amount');
        }

        if ($this->outflow_needs_approval == true) {
            $this->money_transfer_account_request($type_transfer, $recipient);
        } else {
            $this->money_transfer_account_pay($type_transfer, $recipient);
        }
    }

    private function money_transfer_account_pay(TransactionType $type_transfer, User $recipient)
    {
        DB::beginTransaction();
        try {
            $amount = round($this->amount, 2);

            $provider_repay = TransactionProvider::where('code', 'RPY')->firstOrFail();
            $channel_repay = TransactionChannel::where('code', 'RPY')->firstOrFail();
            $status_successful = TransactionStatus::where('slug', 'successful')->firstOrFail();
    
            $transaction = new Transaction;
    
            $transaction->sender_id = $this->merchant->id;
            $transaction->sender_type = get_class($this->merchant);
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

            $this->credit($this->merchant, $transaction);
            $this->debit($recipient, $transaction);

            $message = "You have received " . Number::currency($transaction->amount, 'PHP') . " from  {$this->format_phone_number($this->merchant->phone_number, $this->merchant->phone_iso)}";

            if (!empty($this->message)) {
                $message = $message . " with message: " . $this->message . ".";
            } else {
                $message = $message . ".";
            }

            $message = $message . "\n\nTransaction no: " . $transaction->txn_no;

            $this->alert(
                 $recipient,
                'transaction',
                $transaction->txn_no,
                $message,
            );

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("MerchantFinancialTransactionCashOutflowCreate.money_transfer_account: " . $th->getMessage());
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
        return $this->apiSuccessMsg = 'Successfully transferred to ' . $this->format_phone_number($recipient->phone_number, $recipient->phone_iso) . '. Ref no: ' . $transaction->ref_no;
    }
    
    private function money_transfer_account_request(TransactionType $type_transfer, User $recipient)
    {
        DB::beginTransaction();
        try {
            $amount = round($this->amount, 2);

            $provider_repay = TransactionProvider::where('code', 'RPY')->firstOrFail();
            $channel_repay = TransactionChannel::where('code', 'RPY')->firstOrFail();
    
            $transaction_request = new TransactionRequest;
    
            $transaction_request->merchant_id = $this->merchant->id;
            $transaction_request->recipient_id = $recipient->id;
            $transaction_request->recipient_type = get_class($recipient);
            $transaction_request->transaction_provider_id = $provider_repay->id;
            $transaction_request->transaction_channel_id = $channel_repay->id;
            $transaction_request->transaction_type_id = $type_transfer->id;
            $transaction_request->service_fee = 0;
            $transaction_request->currency = 'PHP';
            $transaction_request->amount = $amount;
            $transaction_request->message = $this->message;
            $transaction_request->created_by = $this->employee->id;
            $transaction_request->save();
 
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("MerchantFinancialTransactionCashOutflowCreate.money_transfer_account_request: " . $th->getMessage());
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
        return $this->apiSuccessMsg = "Your transaction request has been successfully submitted.";
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
            'account_name' => 'required|string',
            'amount' => ['required', 'numeric', 'min:0.01', function ($attribute, $value, $fail) {
                if ($this->send_via === 'instapay') {
                    $channel_slug = 'instapay';
                } elseif ($this->send_via === 'pesonet') {
                    $channel_slug = 'pesonet';
                }

                $provider = TransactionProvider::where('slug', 'allbank')->first();
                $channel = TransactionChannel::where('slug', $channel_slug)->first();

                if (!$provider || !$channel) {
                    $fail("The send via field is invalid");
                }

                $service_fee = $this->get_service_fee($provider, $channel);
                $amount = $value + $service_fee;

                if (!$this->is_sufficient($this->merchant, $amount)) {
                    $fail('Insufficient balance');
                }
            }],
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

        if ($this->outflow_needs_approval == true) {
            if ($this->send_via === 'instapay') {
                return $this->transfer_request_instapay();
            } elseif ($this->send_via === 'pesonet') {
                return $this->transfer_request_pesonet();
            }
        }

        if (!$this->otp) {
            $this->send_otp();
            return $this->show_otp_modal = true;
        }

        $valid_otp = $this->verify_otp();
        if (!$valid_otp) {
            return $this->show_otp_modal = true;
        }

        if ($this->send_via === 'instapay') {
            return $this->transfer_instapay();
        } elseif ($this->send_via === 'pesonet') {
            return $this->transfer_pesonet();
        }
    }

    private function transfer_instapay()
    {
        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('slug', 'instapay')->first();
        $transaction_type = TransactionType::where('slug', 'cash_out')->first();
        $ref_no = $this->generate_transaction_reference_number($provider, $channel, $transaction_type);
    
        [$token, $tdt] = $this->generate_token();

        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'IPAYPTR',
            'acctno' => config('services.alb.opc'), // Origination Account Number
            'acctno2' => $this->account_number, // Destination Account Number
            'ln' => $this->account_name, // Destination Account Name
            'amt' => $this->amount,
            'dbk' => $this->selected_bank,
            'ref_id' => $ref_no,
        ];

        $str_xml = $this->generate_xml_string($request_body);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            Log::error('MerchantFinancialTransactionCashOutflowCreate:transfer_instapay: ' . $response->body());
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            Log::error('MerchantFinancialTransactionCashOutflowCreate:transfer_instapay: ' . $data['ErrorMsg']);
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }

        DB::beginTransaction();
        try {
            $transaction = new Transaction;

            $transaction->fill([
                'sender_id' => $this->merchant->id,
                'sender_type' => get_class($this->merchant),
                'recipient_id' => $provider->id,
                'recipient_type' => get_class($provider),
                'txn_no' => $this->generate_transaction_number(),
                'ref_no' => $ref_no,
                'transaction_provider_id' => $provider->id,
                'transaction_channel_id' => $channel->id,
                'transaction_type_id' => $transaction_type->id,
                'transaction_status_id' => TransactionStatus::where('slug', 'pending')->first()->id,
                'currency' => 'PHP',
                'amount' => $this->amount,
                'extras' => [
                    'inv' => $data['inv'],
                    'ibft_id_code' => $data['ibft_id_code']
                ],
                'service_fee' => $this->get_service_fee($provider, $channel),
            ]);

            $transaction->save();

            $this->credit($this->merchant, $transaction);

            DB::commit();

            $this->closeModal();
            $this->reset([
                'amount',
                'message',
                'agreed_to_correct_info',
                'transfer_to',
                'send_via',
                'selected_bank',
                'account_number',
                'account_name',
            ]);
            return $this->apiSuccessMsg = "Your transaction has been successfully processed. Please wait for the transaction to be approved.";
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('MerchantFinancialTransactionCashOutflowCreate:transfer_instapay: ' . $ex->getMessage());
            $this->closeModal();
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }
    }

    private function transfer_pesonet()
    {
        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('slug', 'pesonet')->first();
        $transaction_type = TransactionType::where('slug', 'cash_out')->first();
        $ref_no = $this->generate_transaction_reference_number($provider, $channel, $transaction_type);
    
        [$token, $tdt] = $this->generate_token();

        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'PNETPTR',
            'acctno' => config('services.alb.opc'), // Origination Account Number
            'acctno2' => $this->account_number, // Destination Account Number
            'ln' => $this->account_name, // Destination Account Name
            'amt' => $this->amount,
            'dbk' => $this->selected_bank,
            'ref_id' => $ref_no,
        ];

        $str_xml = $this->generate_xml_string($request_body);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            Log::error('MerchantFinancialTransactionCashOutflowCreate:transfer_pesonet: ' . $response->body());
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            Log::error('MerchantFinancialTransactionCashOutflowCreate:transfer_pesonet: ' . $data['ErrorMsg']);
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }

        DB::beginTransaction();
        try {
            $transaction = new Transaction;

            $transaction->fill([
                'sender_id' => $this->merchant->id,
                'sender_type' => get_class($this->merchant),
                'recipient_id' => $provider->id,
                'recipient_type' => get_class($provider),
                'txn_no' => $this->generate_transaction_number(),
                'ref_no' => $ref_no,
                'transaction_provider_id' => $provider->id,
                'transaction_channel_id' => $channel->id,
                'transaction_type_id' => $transaction_type->id,
                'transaction_status_id' => TransactionStatus::where('slug', 'pending')->first()->id,
                'currency' => 'PHP',
                'amount' => $this->amount,
                'extras' => [
                    'peso_ref_id' => $data['peso_ref_id'],
                ],
                'service_fee' => $this->get_service_fee($provider, $channel),
            ]);

            $transaction->save();

            $this->credit($this->merchant, $transaction);

            DB::commit();

            $this->closeModal();
            $this->reset([
                'amount',
                'message',
                'agreed_to_correct_info',
                'transfer_to',
                'send_via',
                'selected_bank',
                'account_number',
                'account_name',
            ]);
            return $this->apiSuccessMsg = "Your transaction has been successfully processed. Please wait for the transaction to be approved.";
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('MerchantFinancialTransactionCashOutflowCreate:transfer_pesonet: ' . $ex->getMessage());
            $this->closeModal();
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }
    }

    private function transfer_request_instapay()
    {
        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('slug', 'instapay')->first();
        $transaction_type = TransactionType::where('slug', 'cash_out')->first();

        DB::beginTransaction();
        try {
            $transaction_request = new TransactionRequest;

            $transaction_request->merchant_id = $this->merchant->id;
            $transaction_request->recipient_id = $provider->id;
            $transaction_request->recipient_type = get_class($provider);
            $transaction_request->transaction_provider_id = $provider->id;
            $transaction_request->transaction_channel_id = $channel->id;
            $transaction_request->transaction_type_id = $transaction_type->id;
            $transaction_request->currency = 'PHP';
            $transaction_request->amount = $this->amount;
            $transaction_request->service_fee = $this->get_service_fee($provider, $channel);
            $transaction_request->phone_number = $this->phone_number;
            $transaction_request->email = $this->email;
            $transaction_request->message = $this->message;
            $transaction_request->extras = [
                'account_number' => $this->account_number,
                'account_name' => $this->account_name,
                'selected_bank' => $this->selected_bank,
                'bank_name' => $this->get_bank_name
            ];
            $transaction_request->created_by = $this->employee->id;

            $transaction_request->save();

            DB::commit();

            $this->closeModal();
            $this->reset([
                'amount',
                'message',
                'agreed_to_correct_info',
                'transfer_to',
                'send_via',
                'selected_bank',
                'account_number',
                'account_name',
            ]);
            return $this->apiSuccessMsg = "Your transaction request has been created. Please wait for the admin/owner to approve.";
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('MerchantFinancialTransactionCashOutflowCreate:transfer_request_instapay: ' . $ex->getMessage());
            $this->closeModal();
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }
    }

    private function transfer_request_pesonet()
    {
        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('slug', 'pesonet')->first();
        $transaction_type = TransactionType::where('slug', 'cash_out')->first();

        DB::beginTransaction();
        try {
            $transaction_request = new TransactionRequest;

            $transaction_request->merchant_id = $this->merchant->id;
            $transaction_request->recipient_id = $provider->id;
            $transaction_request->recipient_type = get_class($provider);
            $transaction_request->transaction_provider_id = $provider->id;
            $transaction_request->transaction_channel_id = $channel->id;
            $transaction_request->transaction_type_id = $transaction_type->id;
            $transaction_request->currency = 'PHP';
            $transaction_request->amount = $this->amount;
            $transaction_request->service_fee = $this->get_service_fee($provider, $channel);
            $transaction_request->phone_number = $this->phone_number;
            $transaction_request->email = $this->email;
            $transaction_request->message = $this->message;
            $transaction_request->extras = [
                'account_number' => $this->account_number,
                'account_name' => $this->account_name,
                'selected_bank' => $this->selected_bank,
                'bank_name' => $this->get_bank_name
            ];
            $transaction_request->created_by = $this->employee->id;

            $transaction_request->save();

            DB::commit();

            $this->closeModal();
            $this->reset([
                'amount',
                'message',
                'agreed_to_correct_info',
                'transfer_to',
                'send_via',
                'selected_bank',
                'account_number',
                'account_name',
            ]);
            return $this->apiSuccessMsg = "Your transaction has been successfully processed. Please wait for the transaction to be approved.";
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('MerchantFinancialTransactionCashOutflowCreate:transfer_pesonet: ' . $ex->getMessage());
            $this->closeModal();
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }
    }

    private function bill_payment()
    {
        $this->validate([
            'biller_type' => 'required|in:' . implode(',', array_column($this->billers_list, 'Description')),
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
        ], [
            'bill_info.0.required' => 'The ' . $this->get_biller_input_fields[0]['label'] . ' field is required.',
            'bill_info.1.required' => 'The ' . $this->get_biller_input_fields[1]['label'] . ' field is required.',
        ]);

        $this->apiErrorMsg = "Not implemented yet";
    }

    private function bill_request()
    {
        $this->validate([
            'biller_type' => 'required|in:' . implode(',', array_column($this->billers_list, 'Description')),
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
        ], [
            'bill_info.0.required' => 'The ' . $this->get_biller_input_fields[0]['label'] . ' field is required.',
            'bill_info.1.required' => 'The ' . $this->get_biller_input_fields[1]['label'] . ' field is required.',
        ]);

        $key = array_search($this->biller_type, array_column($this->billers_list, 'Description'));
        $biller = $this->billers_list[$key];
        DB::beginTransaction();
        try {
            $billing_request = new BillingRequest;
            $billing_request->merchant_id = $this->merchant->id;
            $billing_request->name = $biller['BillerTag'];
            $billing_request->amount = $this->amount;
            $billing_request->service_charge = $this->get_biller_service_charge ?? 0;
            $billing_request->email = $this->email ?? null;

            $infos = [];
            foreach ($biller['FieldDetails'] as $key => $field) {
                $infos[$field['Caption']] = $this->bill_info[$key];
            }

            $billing_request->infos = $infos;
            $billing_request->created_by = $this->employee->id;
            $billing_request->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error("MerchantFinancialTransactionCashOutflowCreate::bill_request: " . $ex->getMessage());
            return $this->apiErrorMsg = "Something went wrong. Please try again later.";
        }

        $this->reset([
            'biller_type',
            'amount',
            'bill_info',
            'email',
            'agreed_to_correct_info',
        ]);
        return $this->apiSuccessMsg = "Your billing request has been successfully submitted.";
    }

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        if ($this->transaction_type === 'bill-payment') {
            $billers_list = $this->billers_list;
            $billers = [];

            if (array_search($this->biller_type, array_column($this->billers_list, 'Description')) === false) {
                $billers = array_filter($billers_list, function ($biller) {
                    return !$this->biller_type || strpos(strtolower($biller['Description']), strtolower($this->biller_type)) !== false;
                });
        
                $billers = array_map(function ($biller) {
                    $data = [
                        'category' => $biller['Category'],
                        'code' => $biller['BillerTag'],
                        'description' => $biller['Remarks'],
                        'name' => $biller['Description'],
                        'service_charge' => $biller['ServiceCharge'],
                        'status' => $biller['Status'],
                    ];
    
                    if (str_contains($biller['Remarks'], 'DEACTIVATED BILLER')) {
                        $data['status'] = false;
                    }
    
                    return $data;
                }, $billers);
        
                usort($billers, function ($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
            }
            
        } else {
            $billers = [];
        }
        
        return view('merchant.financial-transaction.cash-outflow.merchant-financial-transaction-cash-outflow-create')->with([
            'billers' => $billers
        ]);
    }
}
