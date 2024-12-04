<?php

namespace App\User\CashOutflow;

use App\Models\OTP;
use App\Models\SystemService;
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
use App\Traits\WithOTP;
use App\Traits\WithServiceFee;
use App\Traits\WithTransactionLimit;
use App\Traits\WithValidPhoneNumber;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class UserCashOutflowCreate extends Component
{
    use WithValidPhoneNumber, WithTransactionLimit, WithBalance, WithNumberGeneration, WithNotification, WithImage, WithStringManipulation, WithOTP;
    use WithAllBankFunctions, WithECPayFunctions;
    use WithServiceFee;

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

    #[Locked]
    public $verification_id = null;
    #[Locked]
    public $resend_available_at = null;
    public $otp;
    public $show_otp_modal = false;

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

        if (empty($this->transaction_type) || !in_array($this->transaction_type, $this->allowed_transaction_types)) {
            $this->transaction_type = 'money-transfer';
        }

        $this->check_service_availability();
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

        $user = User::active()->where('phone_number', $phone_number)->with(['profile', 'profile_picture'])->first();

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
        if (!in_array($this->transaction_type, $this->allowed_transaction_types)) {
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
            'service_charge',
            'biller_type'
        ]);
    }

    public function updatedTransferTo()
    {
        if (!in_array($this->transfer_to, $this->allowed_transfer_to)) {
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
        if (!in_array($this->send_via, $this->allowed_send_via)) {
            $this->send_via = 'instapay';
        }

        $this->selected_bank = '';
    }

    public function updatedAmount()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $this->available_balance
        ]);
    }


    // For Bill Payment
    #[Computed(persist: true, seconds: 3600, cache: true, key: 'ecpay_billers_list')]
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
            $this->bill_payment();
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
        $response = $this->generate_otp($this->user, 'transaction');

        if ($response == null) {
            Log::error('UserCashOutflowCreate.send_otp - Error generating OTP');
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
            'contact' => $this->user->phone_number,
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
            'phone_number' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    if (!$this->phonenumber_info($value, $this->phone_iso) && !in_array($this->phone_iso, array_keys($this->phone_isos))) {
                        $fail('Invalid phone number');
                    }
                    $phone_number = $this->phone_isos[$this->phone_iso] . $value;
                    $user = User::active()->where('phone_number', $phone_number)->first();
                    if (!$user) {
                        $fail('Number is not registered to repay.');
                    }
                }
            ],
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
            return $this->apiErrorMsg = 'Amount is beyond your balance';
        }

        if ($this->check_outbound_limit($this->user, $type_transfer, $this->amount) == true) {
            return $this->apiErrorMsg = 'You will exceed the transaction limit with this amount';
        }

        if ($this->check_inbound_limit($recipient, $type_transfer, $this->amount) == true) {
            return $this->apiErrorMsg = 'The recipient will exceed the transaction limit with this amount';
        }

        if ($this->is_balance_limit_reached($recipient, $this->amount) == true) {
            return $this->apiErrorMsg = 'The recipient will exceed the balance limit with this amount';
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

            $message = "You have received PHP" . number_format($transaction->amount, 2) . " from {$this->format_phone_number_for_display($this->user->phone_number, $this->user->phone_iso)}";

            if (!empty($this->message)) {
                $message = $message . " with message: " . $this->message . ".";
            } else {
                $message = $message . ".";
            }

            $message = $message . "\n\nTransaction No: " . $transaction->txn_no;

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
            Log::error("UserCashOutflowCreate.money_transfer_account: " . $th->getMessage());
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
        return $this->apiSuccessMsg = 'Successfully transferred to ' . $this->format_phone_number($this->user->phone_number, $this->user->phone_iso) . '. Ref no: ' . $transaction->ref_no;
    }

    private function money_transfer_bank()
    {
        $this->validate([
            'send_via' => 'required|in:' . implode(',', $this->allowed_send_via),
            'selected_bank' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_column($this->send_via === 'instapay' ? $this->instapay_banks_list : $this->pesonet_banks_list, 'code'))) {
                        $fail('The selected bank is invalid');
                    }
                }
            ],
            'account_number' => 'required',
            'account_name' => 'required|string',
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
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

                    if (!$this->is_sufficient($this->user, $amount)) {
                        $fail('Insufficient balance');
                    }
                }
            ],
            'phone_iso' => 'nullable|in:' . implode(',', array_keys($this->phone_isos)),
            'phone_number' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!isset($this->phone_isos[$this->phone_iso])) {
                        $fail('Invalid phone number');
                    }

                    $phone_number = $this->phone_isos[$this->phone_iso] . $value;
                    $check = $this->phonenumber_info($phone_number, $this->phone_iso);
                    if ($check == false) {
                        $fail('Invalid phone number');
                    }
                }
            ],
            'email' => 'nullable|email:rfc,dns',
            'message' => 'nullable|string|max:255',
        ]);

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
            Log::error('UserCashOutflowCreate:transfer_instapay: ' . $response->body());
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            Log::error('UserCashOutflowCreate:transfer_instapay: ' . $data['ErrorMsg']);
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }

        DB::beginTransaction();
        try {
            $transaction = new Transaction;

            $transaction->fill([
                'sender_id' => $this->user->id,
                'sender_type' => get_class($this->user),
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

            $this->credit($this->user, $transaction);

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
            Log::error('UserCashOutflowCreate:transfer_instapay: ' . $ex->getMessage());
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
            Log::error('UserCashOutflowCreate:transfer_pesonet: ' . $response->body());
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            Log::error('UserCashOutflowCreate:transfer_pesonet: ' . $data['ErrorMsg']);
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }

        DB::beginTransaction();
        try {
            $transaction = new Transaction;

            $transaction->fill([
                'sender_id' => $this->user->id,
                'sender_type' => get_class($this->user),
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

            $this->credit($this->user, $transaction);

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
            Log::error('UserCashOutflowCreate:transfer_pesonet: ' . $ex->getMessage());
            $this->closeModal();
            return $this->apiErrorMsg = "An error has occurred. Please try again later.";
        }
    }

    private function bill_payment()
    {
        $this->validate([
            'biller_type' => 'required|in:' . implode(',', array_column($this->billers_list, 'Description')),
            'bill_info' => 'required|size:' . count($this->get_biller_input_fields),
            'bill_info.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $key = substr($attribute, -1);

                    if (!isset($this->get_biller_input_fields[$key])) {
                        $fail('Invalid input');
                    }

                    if (empty($value)) {
                        $fail('The ' . $this->get_biller_input_fields[$key]['label'] . ' field is required.');
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
                }
            ],
            'amount' => 'required|numeric|min:0.01|max:' . $this->available_balance,
            'email' => 'nullable|email:rfc,dns',
        ], [
            'bill_info.0.required' => 'The ' . $this->get_biller_input_fields[0]['label'] . ' field is required.',
            'bill_info.1.required' => 'The ' . $this->get_biller_input_fields[1]['label'] . ' field is required.',
        ]);

        if (!$this->otp) {
            $this->send_otp();
            return $this->show_otp_modal = true;
        }

        $valid_otp = $this->verify_otp();
        if (!$valid_otp) {
            return $this->show_otp_modal = true;
        }
    }


    #[Layout('layouts.user')]
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
            }
        } else {
            $billers = [];
        }

        return view('user.cash-outflow.user-cash-outflow-create')->with([
            'billers' => $billers,
        ]);
    }
}
