<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnionBank\BillerPreferenceRequest;
use App\Http\Requests\UnionBank\BillPaymentRequest;
use App\Http\Requests\UnionBank\CustomerTokenRequest;
use App\Http\Requests\UnionBank\PartnerTransferRequest;
use App\Http\Requests\UnionBank\TopUpOTPRequest;
use App\Http\Requests\UnionBank\TopUpRequest;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\UnionbankLinkedAccount;
use App\Traits\WithBalance;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UnionBankController extends Controller
{

    use WithHttpResponses, WithEntity, WithNumberGeneration, WithBalance, WithNotification;

    /**
     * Summary of generate_partner_token
     * @param string $scope
     * @return \Illuminate\Http\Client\Response
     */
    private function generate_partner_token(string $scope): Response
    {
        $data = [
            'grant_type' => 'password',
            'client_id' => config('services.unionbank.client_id'),
            'username' => config('services.unionbank.partner_username'),
            'password' => config('services.unionbank.partner_password'),
            'scope' => $scope,
        ];

        return Http::asForm()->withHeaders([
            'accept' => 'application/json',
            'content-type' => 'application/x-www-form-urlencoded',
        ])->post(config('services.unionbank.host_api_url') . '/' . 'partners/v1/oauth2/token', $data);
    }


    /**
     * Summary of generate_customer_token
     * @param string $code
     * @return Response
     */
    private function generate_customer_token(string $code)
    {
        $data = [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.unionbank.client_id'),
            'redirect_url' => config('services.unionbank.redirect_url'),
            'code' => $code,
        ];

        return Http::asForm()->withHeaders([
            'accept' => 'application/json',
            'content-type' => 'application/x-www-form-urlencoded',
        ])->post(config('services.unionbank.host_api_url') . '/' . 'customers/v1/oauth2/token', $data);
    }

    /**
     * Summary of handleError
     * @param \Illuminate\Http\Client\Response $response
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    private function handleError(Response $response)
    {
        $data = json_decode($response->body());

        if (!empty($data->errors)) {
            $data = $data->errors[0];
        }

        return $this->error($data, 499);
    }

    /**
     * Summary of get
     * @param string $endpoint
     * @param string $token
     * @param mixed $data
     * @param string $pid
     * @return Response
     */
    private function get_request(
        string $endpoint,
        string $token = '',
        $data = [],
    ) {
        if (str_starts_with($endpoint, '/')) {
            $endpoint = ltrim($endpoint);
        }

        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'x-ibm-client-id' => config('services.unionbank.client_id'),
            'x-ibm-client-secret' => config('services.unionbank.client_secret'),
            'x-partner-id' => config('services.unionbank.partner_id'),
        ];

        if (!empty($token)) {
            $headers['authorization'] = "Bearer $token";
        }

        return Http::withHeaders($headers)->get(
            config('services.unionbank.host_api_url') . '/' . $endpoint,
            $data,
        );
    }

    /**
     * Summary of post
     * @param string $endpoint
     * @param string $token
     * @param mixed $data
     * @return Response
     */
    private function post_request(string $endpoint, string $token, $data = [])
    {
        if (str_starts_with($endpoint, '/')) {
            $endpoint = ltrim($endpoint);
        }

        return Http::withHeaders([
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'x-ibm-client-id' => config('services.unionbank.client_id'),
            'x-ibm-client-secret' => config('services.unionbank.client_secret'),
            'x-partner-id' => config('services.unionbank.partner_id'),
            'authorization' => "Bearer $token",
        ])->post(
                config('services.unionbank.host_api_url') . '/' . $endpoint,
                $data,
            );
    }


    /**----------------------
     * ACCOUNT LINKING
     * ----------------------
     */

    /**
     * Summary of generate_link_signin
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function link_url()
    {
        $url = config('services.unionbank.host_api_url') . '/customers/v1/oauth2/authorize';
        $url .= '?response_type=code';
        $url .= '&scope=payments';
        $url .= '&type=linking';
        $url .= '&redirect_uri=' . config('services.unionbank.redirect_url');
        $url .= '&client_id=' . config('services.unionbank.client_id');
        $url .= '&partnerId=' . config('services.unionbank.partner_id');
        return $this->success($url);
    }

    /**
     * Summary of customer_token
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function link_token(CustomerTokenRequest $request)
    {
        $validated = $request->validated();
        $auth_code = $validated['code'];
        $name = $validated['name'] ?? '';

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $response = $this->generate_customer_token($auth_code);
        if ($response->failed()) {

            return $this->handleError($response);
        }

        DB::beginTransaction();
        try {
            $response_body = json_decode($response->body(), true);
            $account = new UnionbankLinkedAccount;
            $account->fill([
                'owner_id' => $entity->id,
                'owner_type' => get_class($entity),
                'name' => empty($name) ? $entity->name : $name,
                'scope' => $response_body['scope'],
                'access_token' => $response_body['access_token'],
                'refresh_token' => $response_body['refresh_token'],
            ]);
            $account->save();

            DB::commit();
            return $this->success($account);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**----------------------
     * BILLER AND PAYMENTS
     * ----------------------
     */

    /**
     * Summary of billers
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function billers()
    {
        try {
            $response = $this->get_request('billers/v1/list');

            if ($response->failed()) {
                return $this->handleError($response);
            }

            $data = json_decode($response->body());
            return $this->success($data);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of biller_preferences
     * @param \App\Http\Requests\UnionBank\BillerPreferenceRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function biller_preferences(BillerPreferenceRequest $request)
    {
        $validated = $request->validated();
        $biller_code = $validated['biller_code'];
        $code = str_pad($biller_code, 4, '0', STR_PAD_LEFT);

        try {
            $response = $this->get_request("billers/v1/$code/references");
            if ($response->failed()) {
                return $this->handleError($response);
            }

            $data = json_decode($response->body());
            return $this->success($data);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of bills_payment
     * @param \App\Http\Requests\UnionBank\BillPaymentRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function bill_payment(BillPaymentRequest $request)
    {
        $validated = $request->validated();
        $reference_number = $validated['reference_number'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $bill = $entity->bills()->where('ref_no', $reference_number)->first();
        if (empty($bill)) {
            return $this->error('bill not found', 499);
        }

        if (empty($bill->payment_date) == false) {
            return $this->error('bill is already paid', 499);
        }

        $balance = $entity->balances()->first();
        if ($balance->amount < $bill->amount) {
            return $this->error('insufficient balance', 499);
        }

        try {
            $partner = $this->generate_partner_token('payments');
            if ($partner->successful() == false) {
                return $this->handleError($partner);
            }

            $partnerToken = json_decode($partner->body());
            $request_date = now()->format('Y-m-d\TH:i:s') . '.000';
            $refs = json_decode($bill->infos, true);
            $data = [
                "senderRefId" => $bill->ref_no,
                "tranRequestDate" => $request_date,
                "biller" => [
                    "id" => str_pad($bill->biller_code, 4, "0", STR_PAD_LEFT),
                    "name" => $bill->biller_name,
                ],
                "amount" => [
                    "currency" => "PHP",
                    "value" => $bill->amount
                ],
                "references" => $refs,
            ];

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'x-ibm-client-id' => config('services.ub.client_id'),
                'x-ibm-client-secret' => config('services.ub.client_secret'),
                'x-partner-id' => config('services.ub.partner_id'),
                'authorization' => "Bearer $partnerToken->access_token",
            ])->post("https://api-uat.unionbankph.com/partners/sb/partners/v3/payments/single", $data);
            if ($response->successful() == false) {
                return $this->handleError($response);
            }

            $data = json_decode($response->body());

            $provider = TransactionProvider::where('slug', 'unionbank')->first();
            $channel = TransactionChannel::where('code', 'UBP')->first();
            $transaction_type = TransactionType::where('slug', 'bill_payment')->first();

            $transaction = new Transaction;
            $transaction->fill([
                'sender_id' => $entity->id,
                'sender_type' => get_class($entity),
                'recipient_id' => $provider->id,
                'recipient_type' => get_class($provider),
                'txn_no' => $this->generate_transaction_number(),
                'ref_no' => $this->generate_transaction_reference_number(
                    $provider,
                    $channel,
                    $transaction_type
                ),
                'transaction_provider_id' => $provider->id,
                'transaction_channel_id' => $channel->id,
                'transaction_type_id' => $transaction_type->id,
                'transaction_status_id' => TransactionStatus::where('slug', 'successful')->first()->id,
                'service_fee' => 0,
                'currency' => 'PHP',
                'amount' => $bill->amount,
            ]);

            DB::transaction(function () use ($bill, $transaction, $entity) {
                $bill->payment_date = now();
                $bill->save();
                $transaction->save(); /// Save transaction

                $this->credit($entity, $transaction);

                $this->alert(
                    $entity,
                    'transaction',
                    $transaction->txn_no,
                    "You have successfully paid PHP $bill->amount to " . $bill->biller_name . ".\n\nTransaction no : {$transaction->txn_no}."
                );
            });

            $transaction->load(['provider', 'channel', 'type', 'status']);
            return $this->success($transaction);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**----------------------
     * TOPUP or CASH-IN FACILITY
     * ----------------------
     */

    public function topup_otp(TopUpOTPRequest $request)
    {
        $validated = $request->validated();
        $token = $validated['token'];

        try {
            $response = $this->get_request("merchants/v5/payments/otp/single", $token);

            if ($response->successful() == false) {
                return $this->handleError($response);
            }

            $resBody = json_decode($response->body());
            return response()->json(['request_id' => $resBody->requestId], 200);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    public function topup_payment(TopUpRequest $request)
    {
        $validated = $request->validated();

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $amount = [
            'currency' => $validated['currency'],
            'value' => $validated['amount'],
        ];

        $info = [
            [
                'index' => 1,
                'name' => "payor",
                'value' => $entity->name,
            ]
        ];

        $extra = $validated['extra'];
        $extraJson = null;
        if (!empty($extra)) {
            $extraJson = json_decode($extra);
            foreach ($extraJson as $key => $value) {
                array_push($info, [
                    'index' => (count($info) + 1),
                    'name' => $key,
                    'value' => $value,
                ]);
            }
        }

        $request_date = now()->timezone('Asia/Manila')->format('Y-m-d\TH:i:s') . '.000';

        $provider = TransactionProvider::where('code', 'UBN')->first();
        $channel = TransactionChannel::where('code', 'EXI')->first();
        $type = TransactionType::where('code', 'CI')->first();
        $ref_no = $this->generate_transaction_reference_number($provider, $channel, $type);
        $data = [
            'senderRefId' => $ref_no,
            'tranRequestDate' => $request_date,
            'requestId' => $validated['request_id'],
            'otp' => $validated['otp_code'],
            'amount' => $amount,
            'remarks' => 'Repay Cashin',
            'particulars' => $validated['currency'] . ' ' . $validated['amount'],
            'info' => $info,
        ];

        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'x-ibm-client-id' => config('services.unionbank.client_id'),
            'x-ibm-client-secret' => config('services.unionbank.client_secret'),
            'x-partner-id' => config('services.unionbank.partner_id'),
            'authorization' => 'Bearer ' . $validated['token'],
        ];

        DB::beginTransaction();
        try {
            $response = Http::withHeaders($headers)->post(config('services.unionbank.host_api_url') . '/merchants/v5/payments/single', $data);

            if ($response->successful() == false) {
                return $this->handleError($response);
            }

            $transaction = new Transaction;
            $transaction->recipient_id = $entity->id;
            $transaction->recipient_type = get_class($entity);
            $transaction->sender_id = $provider->id;
            $transaction->sender_type = get_class($provider);
            $transaction->transaction_provider_id = $provider->id;
            $transaction->transaction_channel_id = $channel->id;
            $transaction->transaction_type_id = $type->id;
            $transaction->txn_no = $this->generate_transaction_number();
            $transaction->ref_no = $ref_no;
            $transaction->amount = $validated['amount'];
            $transaction->currency = $validated['currency'];
            $transaction->rate = 1;
            $transaction->service_fee = 0;
            $transaction->transaction_status_id = TransactionStatus::where('slug', 'successful')->first()->id;
            $transaction->save();

            $this->debit($entity, $transaction);

            DB::commit();

            return $this->success($transaction);
        } catch (Exception $ex) {
            DB::rollBack();
            $this->exception($ex);
        }
    }

    public function partner_transfer(PartnerTransferRequest $request)
    {
        $validated = $request->validated();

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $amount_format = [
            'currency' => $validated['currency'],
            'value' => $validated['amount'],
        ];

        $amount = $validated['amount'];

        if ($this->is_sufficient($entity, $amount) == false) {
            return $this->error(config('constants.messages.insufficient_balance'), 499);
        }

        $provider = TransactionProvider::where('code', 'UBN')->first();
        $channel = TransactionChannel::where('code', 'EXO')->first();
        $type = TransactionType::where('code', 'CO')->first();

        $ref_no = $this->generate_transaction_reference_number($provider, $channel, $type);
        $request_date = now()->timezone('Asia/Manila')->format('Y-m-d\TH:i:s') . '.000';

        $info = [];

        $data = [
            'senderRefId' => $ref_no,
            'tranRequestDate' => $request_date,
            'accountNo' => $validated['account_number'],
            'remarks' => 'Repay Transfer',
            'particulars' => 'NA',
            'amount' => $amount_format,
            'info' => $info,
        ];

        $partner = $this->generate_partner_token('transfers');
        if ($partner->successful() != true) {
            return $this->handleError($partner);
        }
        $partner = json_decode($partner->body());

        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'x-ibm-client-id' => config('services.unionbank.client_id'),
            'x-ibm-client-secret' => config('services.unionbank.client_secret'),
            'x-partner-id' => config('services.unionbank.partner_id'),
            'authorization' => 'Bearer ' . $partner->access_token,
        ];

        DB::beginTransaction();
        try {
            $response = Http::withHeaders($headers)->post(config('services.unionbank.host_api_url') . '/partners/v3/transfers/single', $data);
            if ($response->failed()) {
                return $this->handleError($response);
            }

            $transaction = new Transaction;
            $transaction->sender_id = $entity->id;
            $transaction->sender_type = get_class($entity);
            $transaction->recipient_id = $provider->id;
            $transaction->recipient_type = get_class($provider);
            $transaction->txn_no = $this->generate_transaction_number();
            $transaction->ref_no = $ref_no;
            $transaction->transaction_provider_id = $provider->id;
            $transaction->transaction_channel_id = $channel->id;
            $transaction->transaction_type_id = $type->id;
            $transaction->rate = 1;
            $transaction->currency = 'PHP';
            $transaction->amount = $amount;
            $transaction->service_fee = 0;
            $transaction->transaction_status_id = TransactionStatus::where('slug', 'successful')->first()->id;
            $transaction->save();

            $this->debit($entity, $transaction);
            DB::commit();

            return $this->success($transaction);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    public function test()
    {
        $partner = $this->generate_partner_token('account_info	');
        if ($partner->successful() != true) {
            return $this->handleError($partner);
        }
        $partner = json_decode($partner->body());

        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'x-ibm-client-id' => config('services.unionbank.client_id'),
            'x-ibm-client-secret' => config('services.unionbank.client_secret'),
            // 'x-partner-id' => config('services.unionbank.partner_id'),
            'authorization' => 'Bearer ' . $partner->access_token,
        ];

        $response = Http::withHeaders($headers)->get(config('services.unionbank.host_api_url') . '/ubp/v1/accounts/balance');

        dd($response->body());
    }
}
