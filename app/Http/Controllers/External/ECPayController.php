<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Http\Requests\ECPay\BillCreateRequest;
use App\Http\Requests\ECPay\BillPayRequest;
use App\Http\Requests\ECPay\BillValidateTransactionRequest;
use App\Http\Requests\ECPay\ECPayWebhookRequest;
use App\Models\Bill;
use App\Models\BillField;
use App\Models\EcpayWebhookData;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\WithBalance;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use App\Traits\WithTransactionLimit;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;


class ECPayController extends Controller
{
    use WithEntity, WithHttpResponses, WithNumberGeneration, WithBalance, WithNotification, WithTransactionLimit;

    /**
     * Summary of post
     * @param string $endpoint
     * @param mixed $data
     * @param string $token
     * @return Response
     */
    private function post_request(string $endpoint, $data = [], $token = '')
    {
        if (str_starts_with($endpoint, '/')) {
            $endpoint = substr($endpoint, 1);
        }

        $headers = [
            'accept' => '*/*',
            'content-type' => 'application/json'
        ];

        if (empty($token) == false) {
            $headers['authorization'] = "Bearer $token";
        }

        return Http::withHeaders($headers)->post(
            config('services.ecpay.hosts.onepay') . '/' . $endpoint,
            $data,
        );
    }


    /**
     * Summary of get
     * @param string $endpoint
     * @param mixed $data
     * @param string $token
     * @return Response
     */
    private function get_request(string $endpoint, $data = [], string $token = '')
    {
        if (str_starts_with($endpoint, '/')) {
            $endpoint = substr($endpoint, 1);
        }

        $headers = [
            'accept' => '*/*',
            'content-type' => 'application/json',
        ];

        if (empty($token) == false) {
            $headers['authorization'] = "Bearer $token";
        }

        return Http::withHeaders($headers)->get(
            config('services.ecpay.hosts.onepay') . '/' . $endpoint,
            $data,
        );
    }

    /**
     * Summary of generate_token
     * @param string $host
     * @throws \Exception
     * @return mixed
     */
    private function generate_token()
    {
        $response = $this->post_request(
            '/api/Authentication/Login',
            [
                'AccountId' => (int) config('services.ecpay.accounts.onepay.account_id'),
                'BranchId' => (int) config('services.ecpay.accounts.onepay.branch_id'),
                'UserName' => config('services.ecpay.accounts.onepay.username'),
                'Password' => config('services.ecpay.accounts.onepay.password'),
            ]
        );

        if ($response->failed()) {
            throw new Exception("[ECPAY] AUTHENTICATION FAILED : " . $response->body());
        }

        $data = json_decode($response->body(), true);
        return $data['Token'];
    }

    /**
     * Summary of webhook
     * @param \App\Http\Requests\ECPay\ECPayWebhookRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function webhook(ECPayWebhookRequest $request)
    {
        $is_test = $request->hasHeader('x-api-test');

        EcpayWebhookData::create([
            'data' => $request->all(),
            'env' => app()->environment(),
        ]);

        $validated = $request->validated();
        $account_number = $validated['account_number'];
        $amount = $validated['amount'];

        $recipient = User::where('phone_number', $account_number)
            ->whereHas('profile', function ($q) {
                $q->whereNotIn('status', ['deactivated']);
            })->first();

        if (empty($recipient)) {
            $recipient = Merchant::where('account_number', $account_number)
                ->whereNotIn('status', ['deactivated'])
                ->first();
        }

        if (empty($recipient) == true) {
            return response()->json([
                'error' => 'Not found',
                'message' => 'Invalid account number',
            ], 404);
        }

        /// Check balance Limit.
        $balance_limit_reached = $this->is_balance_limit_reached($recipient, $amount);
        if ($balance_limit_reached) {
            return response()->json([
                'error' => 'Limit reached',
                'message' => 'Wallet Limit Reached'
            ], 401);
        }

        /// Check Cash-in Limit.
        $type = TransactionType::where('code', 'CI')->first();
        $transaction_limit_reached = $this->check_inbound_limit($recipient, $type, $amount);
        if ($transaction_limit_reached) {
            return response()->json([
                'error' => 'Limit reached',
                'message' => 'Cash-in Limit Reached'
            ], 401);
        }

        $provider = TransactionProvider::where('slug', 'ecpay')->first();
        $channel = TransactionChannel::where('code', 'EXI')->first();

        $transaction = new Transaction;
        $transaction->fill([
            'sender_id' => $provider->id,
            'sender_type' => get_class($provider),
            'recipient_id' => $recipient->id,
            'recipient_type' => get_class($recipient),
            'txn_no' => $this->generate_transaction_number(),
            'ref_no' => $this->generate_transaction_reference_number($provider, $channel, $type),
            'transaction_provider_id' => $provider->id,
            'transaction_channel_id' => $channel->id,
            'transaction_type_id' => $type->id,
            'transaction_status_id' => TransactionStatus::where('slug', 'successful')->first()->id,
            'service_fee' => 0,
            'currency' => 'PHP',
            'amount' => $amount,
        ]);

        if ($is_test) {
            $transaction->extras = [
                'remarks' => 'Test transaction',
            ];
        }

        try {
            DB::transaction(function () use ($recipient, $transaction) {
                $transaction->save();
                $this->debit($recipient, $transaction);
                $this->alert(
                    $recipient,
                    'transaction',
                    $transaction->txn_no,
                    "Received funds from ECPay cash-in " . $transaction->currency . " " . number_format($transaction->amount, 2) . ".\n\n Transaction no $transaction->txn_no",
                );
            });

            return $this->success();
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**--------------------
     * BILLS
     */
    public function billers()
    {
        try {
            $token = $this->generate_token();
            $response = $this->get_request('/api/v1/Ecbills/Billers', [], $token);
            if ($response->failed()) {
                throw new Exception("[ECPAY] BILLER LIST GET REQUEST FAILED");
            }

            $billers = json_decode($response->body(), true);
            return $this->success($billers["Data"]);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of create
     * @param \App\Http\Requests\ECPay\BillCreateRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function create(BillCreateRequest $request)
    {
        $validated = $request->validated();
        $biller_code = $validated['biller_code'];
        $biller_name = $validated['biller_name'];
        $bill_info = $validated['infos'];
        $bill_amount = $validated['amount'];
        $due_date = $validated['due_date'];
        $service_charge = $validated['service_charge'] ?? 0;


        // nullables
        $receipt_email = $validated['receipt_email'] ?? null;
        $remind_date = $validated['remind_date'] ?? null;
        $currency = $validated['currency'] ?? 'PHP';

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $bill = new Bill;
        $bill->fill([
            'entity_id' => $entity->id,
            'entity_type' => get_class($entity),
            'ref_no' => $this->generate_bill_reference_number(),
            'biller_code' => $biller_code,
            'biller_name' => $biller_name,
            'amount' => $bill_amount,
            'due_date' => $due_date,

            'currency' => $currency,
            'remind_date' => $remind_date,
            'receipt_email' => $receipt_email,
            'service_charge' => $service_charge,
        ]);


        $secretKey = $bill->ref_no . Carbon::parse(now())->format('mdY');
        $secretKeyHashed = hash('sha256', $secretKey);

        $data = [
            'PartnerReferenceNumber' => $bill->ref_no,
            'BillerTag' => $biller_code,
            'FirstField' => $bill_info['First Field']['value'],
            'SecondField' => $bill_info['Second Field']['value'],
            'Amount' => (float) $bill_amount,
            'SecretKey' => $secretKeyHashed,
        ];

        $token = $this->generate_token();
        $response = $this->post_request(
            '/api/v1/Ecbills/ValidateTransaction',
            $data,
            $token,
        );

        $data = json_decode($response->body(), true);
        if ($data['Status'] != 200) {
            return $this->error($data['Message'], 499);
        }

        DB::beginTransaction();
        try {
            $bill->save();

            foreach ($bill_info as $key => $info) {
                $field = new BillField;
                $field->fill([
                    'bill_id' => $bill->id,
                    'tag' => $key,
                    ...$info,
                ]);
                $field->save();
            }

            DB::commit();
            $bill->load('fields');
            return $this->success($bill);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /**
     * Summary of bill_validate
     * @param \App\Http\Requests\ECPay\BillValidateTransactionRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function bill_validate(BillValidateTransactionRequest $request)
    {
        $validated = $request->validated();

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $ref_no = $validated['ref_no'];
        $bill = $entity->bills()->where('ref_no', $ref_no)->first();

        if (empty($bill) == true) {
            return $this->error('Invalid bill reference number', 499);
        }

        $biller_tag = $bill->biller_code;
        $first_field = $bill->fields()->where('tag', 'First Field')->first()->value;
        $second_field = $bill->fields()->where('tag', 'Second Field')->first()->value;
        $amount = $bill->amount;

        $secretKey = $bill->ref_no . Carbon::parse(now())->format('mdY');
        $secretKeyHashed = hash('sha256', $secretKey);

        DB::beginTransaction();
        try {
            $data = [
                'PartnerReferenceNumber' => $bill->ref_no,
                'BillerTag' => $biller_tag,
                'FirstField' => $first_field,
                'SecondField' => $second_field,
                'Amount' => (float) $amount,
                'SecretKey' => $secretKeyHashed,
            ];

            $token = $this->generate_token();
            $response = $this->post_request('/api/v1/Ecbills/ValidateTransaction', $data, $token);

            $data = json_decode($response->body(), true);
            if ($data['Status'] != 200) {
                return $this->error($data['Message'], 499);
            }

            return $this->success(true);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of pay
     * @param \App\Http\Requests\ECPay\BillPayRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function bill_pay(BillPayRequest $request)
    {
        $validated = $request->validated();
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $ref_no = $validated['ref_no'];
        $bill = $entity->bills()->where('ref_no', $ref_no)->first();
        if (empty($bill) == true) {
            return $this->error('Invalid bill reference number', 499);
        }


        $biller_tag = $bill->biller_code;
        $first_field = $bill->fields()->where('tag', 'First Field')->first()->value;
        $second_field = $bill->fields()->where('tag', 'Second Field')->first()->value;
        $amount = $bill->amount;

        $is_sufficient = $this->is_sufficient($entity, $bill->amount + $bill->service_charge ?? 0);
        if ($is_sufficient == false) {
            return $this->error('Insufficient balance', 499);
        }

        $secretKey = $bill->ref_no . Carbon::parse(now())->format('mdY');
        $secretKeyHashed = hash('sha256', $secretKey);

        DB::beginTransaction();
        try {
            $data = [
                'PartnerReferenceNumber' => $bill->ref_no,
                'BillerTag' => $biller_tag,
                'FirstField' => $first_field,
                'SecondField' => $second_field,
                'Amount' => (float) $amount,
                'SecretKey' => $secretKeyHashed,
            ];

            $token = $this->generate_token();
            $response = $this->post_request('/api/v1/Ecbills/ProcessTransaction', $data, $token);

            $data = json_decode($response->body(), true);
            if ($data['Status'] != 200) {
                return $this->error($data['Message'], 499);
            }

            $bill->trace_number = $data['Data']['TraceNumber'];
            $bill->payment_date = now();
            $bill->save();

            $provider = TransactionProvider::where('slug', 'ecpay')->first();
            $channel = TransactionChannel::firstOrCreate(['slug' => 'ecpay', 'name' => 'ECPay']);
            $type = TransactionType::where('slug', 'bill_payment')->first();

            $transaction = new Transaction;
            $transaction->fill([
                'sender_id' => $entity->id,
                'sender_type' => get_class($entity),
                'recipient_id' => $provider->id,
                'recipient_type' => get_class($provider),
                'txn_no' => $this->generate_transaction_number(),
                'ref_no' => $this->generate_transaction_reference_number($provider, $channel, $type),
                'transaction_provider_id' => $provider->id,
                'transaction_channel_id' => $channel->id,
                'transaction_type_id' => $type->id,
                'transaction_status_id' => TransactionStatus::where('slug', 'successful')->first()->id,
                'service_fee' => 0,
                'currency' => 'PHP',
                'amount' => $bill->amount,
            ]);
            $transaction->save();
            $this->credit($entity, $transaction, $bill->service_charge);
            DB::commit();

            $transaction->inbound = false;
            $transaction->load(['type']);
            return $this->success($transaction);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }
}
