<?php

namespace App\Http\Controllers\External;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AllBank\GenerateQrRequest;
use App\Http\Requests\AllBank\INQRequest;
use App\Http\Requests\AllBank\InstaTransferRequest;
use App\Http\Requests\AllBank\IntraStatusRequest;
use App\Http\Requests\AllBank\IntraTransferRequest;
use App\Http\Requests\AllBank\OPCStatusRequest;
use App\Http\Requests\AllBank\P2MCancelRequest;
use App\Http\Requests\AllBank\P2MCheckRequest;
use App\Http\Requests\AllBank\PesoTransferRequest;
use App\Http\Requests\AllBank\SOARequest;
use App\Models\AllbankWebhookData;
use App\Models\OTP;
use App\Models\QrGeneratedData;
use App\Models\SystemIssueLog;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Traits\WithAllBankFunctions;
use App\Traits\WithBalance;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use App\Traits\WithServiceFee;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AllBankController extends Controller
{
    use WithAllBankFunctions, WithHttpResponses, WithNumberGeneration, WithEntity, WithBalance, WithNotification, WithServiceFee;

    /**
     * Summary of validate_signature
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function validate_signature(Request $request): array
    {
        $validator = Validator::make($request->header(), [
            'x-alb-signature' => 'required',
        ]);

        if ($validator->fails()) {
            LogHelper::message(message: "AllBankController::validate_signature() : Fails");
            return [false, 'no signature'];
        }

        $verify = $request->header('x-alb-signature');

        if ($request->has('amount')) {
            $data = $request->all();
            $data['amount'] = number_format($data['amount'], 2, '.', '');
            $payload = str_replace("\n", '', json_encode($data));
            $payload = str_replace(
                '"amount":"' . $data['amount'] . '"',
                '"amount":' . $data['amount'],
                $payload,
            );
        } else {
            $payload = str_replace("\n", '', json_encode($request->all()));
        }

        $s = hash_hmac('sha256', $payload, config('services.alb.webhook_secret'));
        $signature = base64_encode($s);

        LogHelper::message(
            "AllBankController::validate_signature()\n" .
            "json encoded request: " . json_encode($request->all()) . "\n" .
            "payload: $payload \n" .
            "request signature: $verify \n" .
            "server signature: $signature"
        );
        return [$verify == $signature, $verify];
    }

    /**
     * Summary of is_otp_valid
     * @param array $data
     * @return bool
     */
    private function is_otp_valid(array $data): bool
    {
        $verification_id = $data['verification_id'];
        $code = $data['code'];

        $otp = OTP::where([
            'verification_id' => $verification_id,
            'code' => $code,
            'type' => 'transaction',
            'verified_at' => null
        ])->first();

        if (empty($otp)) {
            return false;
        }

        if (now()->isAfter($otp->expires_at)) {
            $otp->delete();
            return false;
        }

        return true;
    }

    /* -------------------------
    | WEBHOOK
    */

    /**
     * Summary of p2m
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function p2m(Request $request)
    {
        [$header_valid, $signature] = $this->validate_signature($request);

        $data = new AllbankWebhookData;
        $data->fill([
            'signature' => $signature,
            'data' => $request->all(),
            'type' => 'p2m',
            'env' => app()->environment(),
        ]);
        $data->save();

        if ($header_valid == false) {
            return $this->error('Invalid signature', 200);
        }

        $qrData = QrGeneratedData::where([
            'ref_no' => $request->payment_reference,
            'internal' => false,
        ])->with('client')->first();

        if (empty($qrData)) {
            SystemIssueLog::create([
                'module' => 'AllBank P2M Webhook',
                'description' => "Webhook was invoked but no information is present in `qr_generated_data` table. ref_no: $request->payment_reference",
                'resolution' => 'No transaction and balance data created.'
            ]);

            return $this->success();
        }

        $ref_no = "P2M-" . $request->instapay_reference_number;
        $transaction = Transaction::where('ref_no', $ref_no)->first();
        if (empty($transaction) == false) {
            SystemIssueLog::create([
                'module' => 'AllBank P2M Webhook',
                'description' => "Webhook was invoked but the transaction with reference number $ref_no already exists.",
                'resolution' => 'No transaction and balance data created.'
            ]);

            return $this->success();
        }

        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('code', 'EXI')->first();
        $type = TransactionType::where('slug', 'cash_in')->first();

        $service_fee = $this->get_service_fee($provider, $channel);

        /// Create transaction data
        $transaction = new Transaction;
        $transaction->fill([
            'sender_id' => $provider->id,
            'sender_type' => get_class($provider),
            'recipient_id' => $qrData->client_id,
            'recipient_type' => $qrData->client_type,
            'txn_no' => $this->generate_transaction_number(),
            // 'ref_no' => $qrData->merc_token,
            'ref_no' => $ref_no,
            'transaction_provider_id' => $provider->id,
            'transaction_channel_id' => $channel->id,
            'transaction_type_id' => $type->id,
            'transaction_status_id' => TransactionStatus::where('slug', 'successful')->first()->id,
            'service_fee' => $service_fee,
            'currency' => 'PHP',
            'amount' => $request->amount,
            'extras' => [
                'payment_channel' => $request->payment_channel,
            ],
        ]);

        /// Create balance data
        DB::beginTransaction();
        try {
            $transaction->save();
            $this->debit($qrData->client, $transaction);
            $this->alert(
                $qrData->client,
                "transaction",
                $transaction->txn_no,
                "Received " . $transaction->currency . " " . number_format($transaction->amount, 2) . " via QR.\n\nTransaction No: $transaction->txn_no",
            );

            $transaction->inbound = false;
            $transaction->type;

            DB::commit();
            return $this->success();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->successWithException($ex);
        }
    }

    /**
     * Summary of instapay
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function instapay(Request $request)
    {
        [$header_valid, $signature] = $this->validate_signature($request);
        $data = new AllbankWebhookData;
        $data->fill([
            'signature' => $signature,
            'data' => $request->all(),
            'type' => 'instapay',
            'env' => app()->environment(),
        ]);
        $data->save();

        if ($header_valid == false) {
            return $this->error('Invalid signature', 200);
        }

        // Expected value for Status:
        //   Status : Message
        //     ACTC : Successfull
        //     PNDG : Pending
        //     RJCT : Rvc Participant not available
        //          : TransactionForbidden
        //          : Narrative
        //          : AmountExceedsAgreedLimit
        //          : BankSystemProcessingError
        //          : TransmissonAborted
        //          : Invalid File Format
        //          : ClosedAccountNumber
        //          : OrderRejected
        //          : TransactionNotSupported
        //          : DuplicatePayment
        //          : Debtor bank is not registered
        //          : WaitingTimeExpired
        //          : IncorrectAccountNumber
        //          : BlockedAccount
        //          : InvalidCutOffTime
        //          : SyntaxError
        //          : InvalidCreditorAccountNumber
        //          : OfflineCreditorAgent
        //          : Receiving Bank - Logged Off
        $transaction = Transaction::where('ref_no', $request->sender_reference_id)->first();
        if (empty($transaction)) {
            SystemIssueLog::create([
                'module' => 'AllBank InstaPay Webhook',
                'description' => "Webhook was invoked but no information is present in `transactions` table. ref_no: $request->sender_reference_id",
                'resolution' => 'No further action executed',
            ]);

            return $this->success();
        }

        /// Transaction is already successful, but the webhook was called again.
        if ($transaction->status->slug !== 'pending') {
            SystemIssueLog::create([
                'module' => 'AllBank InstaPay Webhook',
                'description' => "Webhook was invoked but transaction has already been proccessed. ref_no: $request->sender_reference_id",
                'resolution' => 'No further action executed',
            ]);
            return $this->success();
        }

        if ($request->status === "ACTC") {
            $status = TransactionStatus::where('slug', 'successful')->first();
            $this->alert(
                $transaction->sender,
                'transaction',
                $transaction->txn_no,
                "Transaction successful. Transaction no: $transaction->txn_no.",
            );
            $transaction->transaction_status_id = $status->id;
        } else {
            $status = TransactionStatus::where('slug', 'failed')->first();
            /// Transaction amount is already creditted to the sender.
            /// When the transaction status update is not successful, debit the creditted amount to the sender.
            $this->refund($transaction->sender, $transaction);
            $this->alert(
                $transaction->sender,
                'transaction',
                $transaction->txn_no,
                "Transaction failed. $request->message. Transaction no: $transaction->txn_no.",
            );
            $transaction->transaction_status_id = $status->id;
            $transaction->extras['error_message'] = $request->message;
        }
        $transaction->save();
        return $this->success();
    }

    /**
     * Summary of pesonet
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function pesonet(Request $request)
    {
        [$header_valid, $signature] = $this->validate_signature($request);

        $data = new AllbankWebhookData;
        $data->fill([
            'signature' => $signature,
            'data' => $request->all(),
            'type' => 'pesonet',
            'env' => app()->environment(),
        ]);
        $data->save();

        if ($header_valid == false) {
            return $this->error('Invalid signature', 200);
        }

        // Expected value for:
        //   Status : Message
        //     DS07 : Successful
        //     AC03 : Invalid Creditor Account Number
        //     AC06 : Blocked Account
        //     BE01 : Inconsistent with End Customer
        //     CURR : Incorrect Currency
        //     AM21 : Limit Exceeded
        //     DS02 : Order Cancelled
        //     DS04 : Order Rejected
        //     RR04 : Regulatory Reasons
        //     DS06 : Transfer Order
        $transaction = Transaction::where('ref_no', $request->sender_reference_id)->first();
        if (empty($transaction)) {
            SystemIssueLog::create([
                'module' => 'AllBank PesoNet Webhook',
                'description' => "Webhook was invoked but no information is present in `transactions` table. ref_no: $request->sender_reference_id",
                'resolution' => 'No further action executed',
            ]);

            return $this->success();
        }

        /// Transaction is already successful, but the webhook was called again.
        if ($transaction->status->slug !== 'pending') {
            SystemIssueLog::create([
                'module' => 'AllBank InstaPay Webhook',
                'description' => "Webhook was invoked but transaction has already been proccessed. ref_no: $request->sender_reference_id",
                'resolution' => 'No further action executed',
            ]);
            return $this->success();
        }

        if ($request->status === "DS07") {
            $status = TransactionStatus::where('slug', 'successful')->first();
            $this->alert(
                $transaction->sender,
                'transaction',
                $transaction->txn_no,
                "Transaction successful. Transaction no: $transaction->txn_no.",
            );
            $transaction->transaction_status_id = $status->id;
        } else {
            $status = TransactionStatus::where('slug', 'failed')->first();
            /// Transaction amount is already creditted to the sender.
            /// When the transaction status update is not successful, debit the creditted amount to the sender.
            $this->refund($transaction->sender, $transaction);
            $this->alert(
                $transaction->sender,
                'transaction',
                $transaction->txn_no,
                "Transaction failed. $request->message. Transaction no: $transaction->txn_no.",
            );
            $transaction->transaction_status_id = $status->id;
            $transaction->extras['error_message'] = $request->message;
        }
        $transaction->save();
        return $this->success();
    }

    /**
     * Summary of intra
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function intra(Request $request)
    {
        [$header_valid, $signature] = $this->validate_signature($request);
        $data = new AllbankWebhookData;

        $data->fill([
            'signature' => $signature,
            'data' => $request->all(),
            'type' => 'intrabank',
            'env' => app()->environment(),
        ]);
        $data->save();

        if ($header_valid == false) {
            return $this->error('Invalid signature', 200);
        }

        $transaction = Transaction::where('ref_no', $request->sender_reference_id)->first();
        if (empty($transaction)) {
            SystemIssueLog::create([
                'module' => 'AllBank PesoNet Webhook',
                'description' => "Webhook was invoked but no information is present in `transactions` table. ref_no: $request->sender_reference_id",
                'resolution' => 'No further action executed',
            ]);

            return $this->success();
        }

        /// Transaction is already successful, but the webhook was called again.
        if ($transaction->status->slug !== 'pending') {
            SystemIssueLog::create([
                'module' => 'AllBank InstaPay Webhook',
                'description' => "Webhook was invoked but transaction has already been proccessed. ref_no: $request->sender_reference_id",
                'resolution' => 'No further action executed',
            ]);
            return $this->success();
        }

        if (strtolower($request->status) === "successful") {
            $status = TransactionStatus::where('slug', 'successful')->first();
            $this->alert(
                $transaction->sender,
                'transaction',
                $transaction->txn_no,
                "Transaction successful. Transaction no: $transaction->txn_no.",
            );
            $transaction->transaction_status_id = $status->id;
        } else {
            $status = TransactionStatus::where('slug', 'failed')->first();
            /// Transaction amount is already creditted to the sender.
            /// When the transaction status update is not successful, debit the creditted amount to the sender.
            $this->refund($transaction->sender, $transaction);
            $this->alert(
                $transaction->sender,
                'transaction',
                $transaction->txn_no,
                "Transaction failed. $request->message. Transaction no: $transaction->txn_no.",
            );
            $transaction->transaction_status_id = $status->id;
            $transaction->extras = [
                'alb_status_code' => $request->status,
                'alb_status_message' => $request->message,
            ];
        }
        $transaction->save();
        return $this->success();
    }

    /* =========================
    | API WRAPPER ENDPOINTS
    * ==========================
    *
    * --------------------------
    */
    public function dto()
    {
        $transaction_date = now()->format('c');
        $xml = "<Account.Info
            cmd='dto'
            tdt='$transaction_date'
        />";

        $response = Http::withHeaders([
            'Content-Type' => 'text/xml',
            'SoapAction' => 'http://tempuri.org/iWebInterface/wb_Get_Info',
        ])->send(
                'POST',
                config('services.alb.api_host'),
                [
                    'body' => $xml
                ]
            );

        $data = $this->get_xml_contents($response);
        return $this->success($data);
    }

    /* -------------------------
     * P2M 
     * - QR Generation
     * - Transaction Check
     */
    public function generate_qr(GenerateQrRequest $request)
    {
        $validated = $request->validated();
        $amount = (float) $validated['amount'] ?? null;
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);

        # static QR means it can be used over and over again.
        $is_static = empty($amount) ? true : false;

        /// If static, return existing generated static qr of user if there is any.
        if ($is_static) {
            $qr = $entity->generated_qrs()->where([
                'internal' => false,
            ])->first();
            if (empty($qr) == false) {
                return $this->success([
                    'token' => $qr->merc_token,
                    'is_static' => $qr->type == 'static' ? true : false,
                    'qr' => $qr->code,
                ]);
            }
        }

        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('slug', 'external_inbound')->first();
        $transaction_type = TransactionType::where('slug', 'cash_in')->first();
        $ref_no = $this->generate_transaction_reference_number($provider, $channel, $transaction_type);

        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'MERC-QR-REQ',
            'rf' => $ref_no,
            'amt' => $amount ?? '0',
            'merc_tid' => '0',
            'make_static_qr' => $is_static ? '1' : '0',
        ];
        $str_xml = $this->generate_xml_string($request_body);
        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                499,
            );
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            return $this->error($data['ErrorMsg'], 499);
        }

        DB::beginTransaction();
        try {
            $QrData = new QrGeneratedData;
            $QrData->fill([
                'client_id' => $entity->id,
                'client_type' => get_class($entity),
                'ref_no' => $ref_no,
                'merc_token' => $data['merc_token'],
                'type' => $is_static ? 'static' : 'dynamic',
                'internal' => false,
                'code' => $data['qrph'],
            ]);
            $QrData->save();

            DB::commit();
            return $this->success([
                'token' => $data['merc_token'],
                'is_static' => $is_static,
                'amount' => $amount,
                'qr' => $data['qrph'],
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of P2MCheck
     * @param \App\Http\Requests\AllBank\P2MCheckRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function P2MCheck(P2MCheckRequest $request)
    {
        $validated = $request->validated();

        $exists = auth()->user()->generated_qrs()
            ->where('merc_token', $validated['token'])->exists();

        if ($exists == false) {
            return $this->error('Invalid token', 401);
        }

        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'MERC-PAY-CHK',
            'merc_token' => $validated['token'],
        ];
        $str_xml = $this->generate_xml_string($request_body);
        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] == 0) {
            $status = $data['ErrorMsg'] == 'Not yet paid.' ? 'pending' : 'successful';
            return $this->success(['status' => $status]);
        } else {
            return $this->error($data['ErrorMsg'], 401);
        }
    }

    /**
     * Summary of P2MCancel
     * @param \App\Http\Requests\AllBank\P2MCancelRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function P2MCancel(P2MCancelRequest $request)
    {
        $validated = $request->validated();

        $exists = auth()->user()->generated_qrs()
            ->where('merc_token', $validated['token'])->exists();

        if ($exists == false) {
            return $this->error('Invalid token', 401);
        }

        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'MERC-CANCEL',
            'merc_token' => $validated['token'],
        ];
        $str_xml = $this->generate_xml_string($request_body);
        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            return $this->error($data['ErrorMsg'], 401);
        }

        return $this->success(['status' => 'cancelled']);
    }

    /* -------------------------
     * InstaPay
     * - List Banks
     * - Transfer
     * - Status
     */
    public function insta_banks()
    {
        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'I-LST-BNK',
        ];
        $str_xml = $this->generate_xml_string($request_body);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response, false);

        if (empty($data['@attributes']['ErrorMsg']) == false) {
            return $this->error('Service unavailable', 499);
        }

        $banks = array_map(function ($bank) {
            return $bank['@attributes'];
        }, $data['banks']['i']);

        return $this->success($banks);
    }

    /**
     * Summary of insta_transfer
     * @param \App\Http\Requests\AllBank\InstaTransferRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function insta_transfer(InstaTransferRequest $request)
    {
        $validated = $request->validated();

        $is_valid_otp = $this->is_otp_valid($validated);

        if ($is_valid_otp == false) {
            return $this->error(config('constants.messages.invalid_otp'), 422);
        }

        $sender = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($sender)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('slug', 'instapay')->first();
        $transaction_type = TransactionType::where('slug', 'cash_out')->first();
        $service_fee = $this->get_service_fee($provider, $channel);

        $is_sufficient = $this->is_sufficient($sender, ($validated['amount'] + $service_fee));
        if ($is_sufficient == false) {
            return $this->error(config('constants.messages.insufficient_bal'), 499);
        }

        $ref_no = $this->generate_transaction_reference_number($provider, $channel, $transaction_type);
        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'IPAYPTR',
            'acctno' => config('services.alb.p2m'), // Origination Account Number
            'acctno2' => $validated['account_number'], // Destination Account Number
            'ln' => $validated['account_name'], // Destination Account Name
            'amt' => $validated['amount'],
            'dbk' => $validated['bank_code'],
            'ref_id' => $ref_no,
        ];
        $str_xml = $this->generate_xml_string($request_body);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            return $this->error($data['ErrorMsg'], 499);
        }

        try {
            $data = $this->get_xml_contents($response);
            $transaction = new Transaction;

            $transaction->fill([
                'sender_id' => $sender->id,
                'sender_type' => get_class($sender),
                'recipient_id' => $channel->id,
                'recipient_type' => get_class($channel),
                'txn_no' => $this->generate_transaction_number(),
                'ref_no' => $ref_no,
                'transaction_provider_id' => $provider->id,
                'transaction_channel_id' => $channel->id,
                'transaction_type_id' => $transaction_type->id,
                'transaction_status_id' => TransactionStatus::where('slug', 'pending')->first()->id,
                'currency' => 'PHP',
                'amount' => $validated['amount'],
                'extras' => [
                    'inv' => $data['inv'],
                    'ibft_id_code' => $data['ibft_id_code'],
                    'info' => [
                        'account_number' => $validated['account_number'],
                        'account_name' => $validated['account_name'],
                        'bank_name' => $validated['bank_name'],
                    ]
                ],
                'service_fee' => $service_fee,
            ]);

            DB::transaction(function () use ($sender, $transaction) {
                $transaction->save();
                $this->credit($sender, $transaction);
            });

            $transaction->load(['provider', 'channel', 'status', 'type']);
            return $this->success($transaction);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of insta_status
     * @param \App\Http\Requests\AllBank\OPCStatusRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function insta_status(OPCStatusRequest $request)
    {
        $request->validated($request);

        $txn = auth()->user()
            ->outgoing_transactions()
            ->where('ref_no', $request->ref_no)
            ->first();

        if (empty($txn)) {
            return $this->error('Invalid reference number', 499);
        }

        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'IPAY-STATUS',
            'ref_id' => $request->ref_no,
        ];
        $str_xml = $this->generate_xml_string($request_body);
        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            return $this->error($data['ErrorMsg'], 401);
        }

        return $this->success($data);
    }

    /* -------------------------
     * PESONet
     * - List Banks
     * - Transfer
     * - Status
     */
    public function pesonet_banks()
    {
        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'P-LST-BNK',
        ];
        $str_xml = $this->generate_xml_string($request_body);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response, false);
        if (empty($data['@attributes']['ErrorMsg']) == false) {
            return $this->error('Service unavailable', 499);
        }

        $banks = array_map(function ($bank) {
            return $bank['@attributes'];
        }, $data['banks']['i']);

        return $this->success($banks);
    }

    /**
     * Summary of pesonet_transfer
     * @param \App\Http\Requests\AllBank\PesoTransferRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function pesonet_transfer(PesoTransferRequest $request)
    {
        $validated = $request->validated();

        $sender = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($sender)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $is_sufficient = $this->is_sufficient($sender, $validated['amount']);
        if ($is_sufficient == false) {
            return $this->error(config('constants.messages.insufficient_bal'), 499);
        }

        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('slug', 'pesonet')->first();
        $transaction_type = TransactionType::where('slug', 'cash_out')->first();
        $service_fee = $this->get_service_fee($provider, $channel);

        $is_sufficient = $this->is_sufficient($sender, ($validated['amount'] + $service_fee));
        if ($is_sufficient == false) {
            return $this->error(config('constants.messages.insufficient_bal'), 499);
        }

        [$token, $tdt] = $this->generate_token();
        $ref_no = $this->generate_transaction_reference_number($provider, $channel, $transaction_type);
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'PNETPTR',
            'acctno' => config('services.alb.p2m'), // Origination Account Number
            'acctno2' => $validated['account_number'], // Destination Account Number
            'ln' => $validated['account_name'], // Destination Account Name
            'amt' => $validated['amount'],
            'dbk' => $validated['bank_code'],
            'ref_id' => $ref_no,
        ];
        $str_xml = $this->generate_xml_string($request_body);
        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            return $this->error($data['ErrorMsg'], 499);
        }

        try {
            $data = $this->get_xml_contents($response);
            $transaction = new Transaction;

            $transaction->fill([
                'sender_id' => $sender->id,
                'sender_type' => get_class($sender),
                'recipient_id' => $channel->id,
                'recipient_type' => get_class($channel),
                'txn_no' => $this->generate_transaction_number(),
                'ref_no' => $ref_no,
                'transaction_provider_id' => $provider->id,
                'transaction_channel_id' => $channel->id,
                'transaction_type_id' => $transaction_type->id,
                'transaction_status_id' => TransactionStatus::where('slug', 'pending')->first()->id,
                'currency' => 'PHP',
                'amount' => $validated['amount'],
                'extras' => [
                    'peso_ref_id' => $data['peso_ref_id'],
                    'info' => [
                        'account_number' => $validated['account_number'],
                        'account_name' => $validated['account_name'],
                        'bank_name' => $validated['bank_name'],
                    ]
                ],
                'service_fee' => $service_fee,
            ]);

            DB::transaction(function () use ($transaction, $sender) {
                $transaction->save();
                $this->credit($sender, $transaction);
            });

            $transaction->load(['provider', 'channel', 'status', 'type']);
            return $this->success($transaction);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of pesonet_status
     * @param \App\Http\Requests\AllBank\OPCStatusRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function pesonet_status(OPCStatusRequest $request)
    {
        $request->validated($request);

        $txn = auth()->user()
            ->outgoing_transactions()
            ->where('ref_no', $request->ref_no)
            ->first();

        if (empty($txn)) {
            return $this->error('Invalid reference number', 499);
        }

        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'PNET-STATUS',
            'ref_id' => $request->ref_no,
        ];
        $str_xml = $this->generate_xml_string($request_body);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            return $this->error($data['ErrorMsg'], 401);
        }

        return $this->success($data);
    }

    /**
     * Summary of intra_transfer
     * @param \App\Http\Requests\AllBank\IntraTransferRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function intra_transfer(IntraTransferRequest $request)
    {
        $validated = $request->validated();

        $sender = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($sender)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $is_sufficient = $this->is_sufficient($sender, $validated['amount']);
        if ($is_sufficient == false) {
            return $this->error(config('constants.messages.insufficient_bal'), 499);
        }

        [$token, $tdt] = $this->generate_token();

        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('slug', 'external_outbound')->first();
        $transaction_type = TransactionType::where('slug', 'cash_out')->first();
        $ref_no = $this->generate_transaction_reference_number($provider, $channel, $transaction_type);

        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'INTRAPTR',
            'acctno' => config('services.alb.opc'), // Origination Account Number
            'acctno2' => $validated['account_number'], // Destination Account Number
            'amt' => $validated['amount'],
            'ref_id' => $ref_no,
        ];
        $str_xml = $this->generate_xml_string($request_body);
        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            return $this->error($data['ErrorMsg'], 499);
        }

        DB::beginTransaction();
        try {
            $data = $this->get_xml_contents($response);
            $transaction = new Transaction;
            $transaction->fill([
                'sender_id' => $sender->id,
                'sender_type' => get_class($sender),
                'recipient_id' => $provider->id,
                'recipient_type' => get_class($provider),
                'txn_no' => $this->generate_transaction_number(),
                'ref_no' => $ref_no,
                'transaction_provider_id' => $provider->id,
                'transaction_channel_id' => $channel->id,
                'transaction_type_id' => $transaction_type->id,
                'transaction_status_id' => TransactionStatus::where('slug', 'pending')->first()->id,
                'currency' => 'PHP',
                'amount' => $validated['amount'],
                'extras' => [
                    'intra_ref_id' => $data['IntraRefId'],
                ],
                'service_fee' => $this->get_service_fee($provider, $channel),
            ]);
            $transaction->save();
            $this->credit($sender, $transaction);
            DB::commit();

            $transaction->load(['provider', 'channel', 'status', 'type']);
            return $this->success($transaction);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of intra_status
     * @param \App\Http\Requests\AllBank\IntraStatusRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function intra_status(IntraStatusRequest $request)
    {
        $validated = $request->validated();

        $sender = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($sender)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $exists = $sender->transactions()->where('ref_no', $validated['ref_no'])->exists();
        if ($exists == false) {
            return $this->error('Invalid transaction ref no', 499);
        }

        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'INTRA-STATUS',
            'ref_id' => $validated['ref_no'],
        ];
        $str_xml = $this->generate_xml_string($request_body);
        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            return $this->error($data['ErrorMsg'], 401);
        }

        return $this->success($data);
    }

    /**
     * Balance INQUIRY
     * 
     * @param \App\Http\Requests\AllBank\INQRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function inq(INQRequest $request)
    {
        $validated = $request->validated();
        $acct_no = config("services.alb." . $validated['acct_type']);

        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'ACCOUNT-INQ',
            'acctno' => $acct_no,
        ];
        $str_xml = $this->generate_xml_string($request_body);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response, false);
        if (empty($data['@attributes']['ErrorMsg']) == false) {
            return $this->error('Service unavailable', 499);
        }

        return $this->success($data['@attributes']);
    }

    /**
     * TRANSACTION LIST
     * 
     * @param \App\Http\Requests\AllBank\SOARequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function soa(SOARequest $request)
    {
        $validated = $request->validated();

        $acct_no = config("services.alb." . $validated['acct_type']);

        $start_date = $validated['start_date'] ?? now()->subWeek()->format('m/d/y');
        $end_date = $validated['end_date'] ?? now()->format('m/d/y');
        $trans_idcode = $validated['trans_idcode'] ?? 0;

        [$token, $tdt] = $this->generate_token();

        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'ACCOUNT-SOA',
            'acctno' => $acct_no,
            'ds' => $start_date,
            'de' => $end_date,
            'trans_idcode' => $trans_idcode,
        ];

        $str_xml = $this->generate_xml_string($request_body);
        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                401,
            );
        }

        $data = $this->get_xml_contents($response, false);
        if (empty($data['@attributes']['ErrorMsg']) == false) {
            return $this->error('Service unavailable', 499);
        }

        if (empty($data['SOA'])) {
            $records = [];
        } else {
            $records = array_map(function ($record) {
                return $record['@attributes'];
            }, $data['SOA']['i']);
        }

        return $this->success($records);
    }
}
