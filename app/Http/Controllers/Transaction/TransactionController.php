<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\GenerateOTPRequest;
use App\Http\Requests\Transaction\GenerateQrRequest;
use App\Http\Requests\Transaction\QrTransferRequest;
use App\Http\Requests\Transaction\ReportsRequest;
use App\Http\Requests\Transaction\TransactionListRequest;
use App\Http\Requests\Transaction\TransactionViewRequest;
use App\Http\Requests\Transaction\ValidateQrRequest;
use App\Models\Merchant;
use App\Models\OTP;
use App\Models\QrGeneratedData;
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
use App\Traits\WithSMS;
use ArrayObject;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Str;

class TransactionController extends Controller
{
    use WithHttpResponses, WithEntity, WithNumberGeneration, WithBalance, WithNotification, WithSMS;

    /**
     * Summary of list
     * @param \App\Http\Requests\Transaction\TransactionListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function list(TransactionListRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;
        $start = $validated['start'] ?? 'week';

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $id = $entity->id;
        $type = get_class($entity);

        $end = now();
        $start_date = now();
        switch ($start) {
            case 'year':
                $start_date = $start_date->startOfYear();
                break;

            case 'quarter':
                $start_date = $start_date->subMonths(3)->startOfMonth();
                break;

            case 'month':
                $start_date = $start_date->startOfMonth();
                break;

            case 'week':
                $start_date = $start_date->subDays(7);
                break;

            default:
                $start_date = $start_date->startOfDay();
                break;
        }
        
        // dd($end->format('Y-m-d H:i:s'), $start_date->format('Y-m-d H:i:s'));

        $page = Transaction::where(function ($q) use ($id, $type) {
            $q->where(['sender_id' => $id, 'sender_type' => $type])
                ->orWhere(function($q2) use ($id, $type) {
                    $q2->where(['recipient_id' => $id, 'recipient_type' => $type]);
                });
            })
            ->whereBetween('created_at', [
                $start_date,
                $end,
            ])
            ->with('type')
            ->orderByDesc('created_at')
            ->paginate(
                $per_page,
                ['*'],
                'transactions',
                $page
            );

        $transactions = array_map(function ($transaction) use ($entity) {
            $transaction->inbound = $this->is_inbound_transaction($transaction, $entity);
            return $transaction;
        }, $page->items());

        return $this->success([
            'transactions' => $transactions,
            'last_page' => $page->lastPage(),
            'total_item' => $page->total(),
        ]);
    }

    /**
     * Summary of details
     * @param \App\Http\Requests\Transaction\TransactionViewRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function details(TransactionViewRequest $request)
    {
        $validated = $request->validated();
        $txn_no = $validated['txn_no'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $id = $entity->id;
        $type = get_class($entity);
        $transaction = Transaction::where(['sender_id' => $id, 'sender_type' => $type, 'txn_no' => $txn_no])
            ->orWhere(function ($q) use ($id, $type, $txn_no) {
                $q->where(['recipient_id' => $id, 'recipient_type' => $type, 'txn_no' => $txn_no]);
            })
            ->with(['sender', 'recipient', 'type', 'provider', 'channel'])
            ->first();


        if (empty($transaction)) {
            return $this->error(config('constants.messages.invalid_txn_ref'), 499);
        }


        $transaction->inbound = $this->is_inbound_transaction($transaction, $entity);
        $transaction = $transaction->toArray();
        unset(
            $transaction['sender']['profile'],
            $transaction['sender']['status'],
            $transaction['recipient']['profile'],
            $transaction['recipient']['status'],
        );
        return $this->success($transaction);
    }


    /**
     * Summary of generate_qr
     * @param \App\Http\Requests\Transaction\GenerateQrRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function generate_qr(GenerateQrRequest $request) {
        $validated = $request->validated();

        $amount = (float) $validated['amount'] ??= 0;
        $is_static = empty($amount);

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        if ($is_static) {
            $qr = $entity->generated_qrs()->where([
                'internal' => true,
            ])->first();
            if (empty($qr) == false) {
                return $this->success([
                    'is_static' => $qr->type == 'static' ? true : false,
                    'qr' => $qr->code,
                ]);
            }
        }

        $class = get_class($entity);
        $name = '';
        $acc_no = '';
        if ($class == Merchant::class) {
            $name = $entity->name;
            $acc_no = $entity->account_number;
        } else {
            $parts = explode(' ', $entity->name);
            foreach ($parts as &$part) {
                $obs = str_repeat('*', 3);
                $part = str_replace('.', '', substr_replace($part, $obs, 1));
            }
            $name = implode(' ', $parts);
            $acc_no = substr_replace($entity->phone_number, str_repeat('*', 4), 3, -3); 
        }

        DB::beginTransaction();
        try {
            $ref_no = Str::orderedUuid();
            $qrData = new QrGeneratedData;
            $qrData->fill([
                'client_id' => $entity->id,
                'client_type' => get_class($entity),
                'ref_no' => $ref_no,
                'type' => $is_static ? 'static' : 'dynamic',
                'internal' => true,
                'code' => $ref_no . '.' . $name . '.' . $acc_no . '.XAMT' . number_format($amount, 2, '.', ''),
            ]);
            $qrData->save();
            DB::commit();
        return $this->success([
            'is_static' => $qrData->type == 'static' ? true : false,
            'qr' => $qrData->code,
        ]);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of validate_qr
     * @param \App\Http\Requests\Transaction\ValidateQrRequest $request
     * @return void
     */
    public function validate_qr(ValidateQrRequest $request) {
        $validated = $request->validated();
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $ref_no = $validated['ref_no'];

        $qrData = QrGeneratedData::where('ref_no', $ref_no)
        ->whereDoesntHave('client', function ($query) use ($entity) {
            $query->where([
                'client_id' => $entity->id,
                'client_type' => get_class($entity)
            ]);
        })->first();

        if (empty($qrData)) {
            return $this->error('Invalid QR Code', 499);
        }

        return $this->success();
    }

    /**
     * Summary of qr_transfer
     * @param \App\Http\Requests\Transaction\QrTransferRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function qr_transfer(QrTransferRequest $request) {
        $validated = $request->validated();
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $ref_no = $validated['ref_no'];
        $amount = number_format((float) $validated['amount'] ??= 0, 2, '.', '');

        $is_sufficient = $this->is_sufficient($entity, $amount);
        if ($is_sufficient == false) {
            return $this->error(config('constants.messages.insufficient_bal'), 499);
        }

        $qrData = QrGeneratedData::where('ref_no', $ref_no)
            ->whereDoesntHave('client', function ($query) use ($entity) {
                $query->where([
                    'client_id' => $entity->id,
                    'client_type' => get_class($entity)
                ]);
            })
            ->first();

        if (empty($qrData)) {
            return $this->error('Invalid QR Code', 499);
        }

        $qrAmount = (float) explode('XAMT', $qrData->code)[1];

        if ($qrData->type == 'dynamic') {
            $amount = $qrAmount;
        }

        if ($qrData->type == 'static' && $amount < 1) {
            return $this->error("Invalid amount ($amount)", 499);
        }
        
        $provider = TransactionProvider::where('slug', 'repay')->first();
        $channel = TransactionChannel::where('slug', 'repay')->first();
        $type = TransactionType::where('slug', 'transfer')->first();
        
        $transaction = new Transaction;
        $transaction->fill([
            'sender_id' => $entity->id,
            'sender_type' => get_class($entity),
            'recipient_id' => $qrData->client_id,
            'recipient_type' => $qrData->client_type,
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

        try {

            
            DB::transaction(function () use ($entity, $qrData, $transaction) {
                $sender_name = get_class($entity) == User::class ? "+$entity->phone_number" : $entity->name;
                $transaction->save();
                $this->credit($entity, $transaction);
                $this->debit($qrData->client, $transaction);
                $this->alert(
                    $qrData->client,
                    'transaction',
                    $transaction->txn_no,
                    "Received " . $transaction->currency . " " . number_format($transaction->amount, 2) . " from " . $sender_name . ".\n\nTransaction No: {$transaction->txn_no}." ,
                );
            });

            $transaction->inbound = false;
            $transaction->type;
            return $this->success($transaction);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of generate_otp
     * @param \App\Http\Requests\Transaction\GenerateOTPRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function generate_otp(GenerateOTPRequest $request) {
        $validated = $request->validated();

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $otp = OTP::where([
            'contact' => $entity->phone_number,
            'type' => 'transaction',
        ])->first();

        DB::beginTransaction();
        try {
            if (empty($otp) == false) {
                $otp->delete();
            }

            $otp = $this->generate_otp_code($entity->phone_number, 'transaction');
            $this->sendSMS("Repay OTP \n\n$otp->code is your transaction OTP code\n\nUse this code to authorize your transaction.", $otp->contact, 'transaction');
            DB::commit();

            return $this->success([
                'verification_id' => $otp->verification_id,
                'code' => config('app.debug') ? $otp->code : '',
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /**
     * Summary of reports
     * @param \App\Http\Requests\Transaction\ReportsRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function reports(ReportsRequest $request) {
        $validated = $request->validated();
        $merc_ac = $validated['merc_ac'] ?? null;
        $from = $validated['filter'] ?? '1d';

        $entity = $this->get(auth()->user(), $merc_ac);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        
        $toDate = Carbon::now();
        $fromDate = $toDate->clone()->addDay(); // default
        
        /// Filterable by date range
        /// start of day, today - until now
        /// start of day, 1 week - until now
        /// start of day, 1 month - until now
        /// start of day, 3 month - until now
        /// start of day, start of year - now
        switch ($from) {
            case '1y':
                $fromDate = $toDate->clone()->startOfYear()->startOfDay();
                break;
            case '3m':
                $fromDate = $toDate->clone()->subMonths(3)->startOfDay();
                break;
            case '1m':
                $fromDate = $toDate->clone()->subMonth()->startOfDay();
                break;
            case '1w':
                $fromDate = $toDate->clone()->subWeek()->startOfDay();
                break;
            case '1d':
                $fromDate = $toDate->clone()->startOfDay();
                break;
            default:
                $fromDate = $toDate->clone()->startOfDay();
                break;
        }

        $add_select = [
            DB::raw('MONTH(created_at) month'),
            DB::raw('WEEK(created_at) week'),
            DB::raw('DAY(created_at) day'),
            DB::raw('HOUR(created_at) hour'),
            DB::raw("DATE_FORMAT(created_at, '%m.%d') md")
        ];

        $inbound_codes = ['CI', 'TR', 'IV', 'OR'];
        $inflow_types = TransactionType::whereIn('code', $inbound_codes)->toBase()->get();

        $outbound_codes = ['CO', 'TR', 'IV', 'OR', 'BP', 'PS'];
        $outflow_types = TransactionType::whereIn('code', $outbound_codes)->toBase()->get();

        //// INFLOWS
        $inflow_q = $entity->incoming_transactions()
        ->select('id', 'amount', 'service_fee', 'transaction_type_id', 'created_at', 'txn_no')
        ->whereHas('status', fn ($q) => $q->where('slug', 'successful'))
        ->whereHas('type', function($q) use ($inbound_codes) {
            $q->whereIn('code', $inbound_codes);
        })
        ->whereBetween('created_at', [$fromDate, $toDate]);
        $inflow_sum = $inflow_q->clone()->sum(DB::raw('amount + service_fee'));
        $inflow_plot = $this->getPlots($inflow_q->clone()->addSelect($add_select)->get(), $from);

        $cash_in = $inflow_q->clone()
            ->where('transaction_type_id', $inflow_types->where('code', 'CI')->first()->id);
        $inflow_transfers = $inflow_q->clone()
            ->where('transaction_type_id', $inflow_types->where('code', 'TR')->first()->id);
        $inflow_invoices = $inflow_q->clone()
            ->where('transaction_type_id', $inflow_types->where('code', 'IV')->first()->id);
        $inflow_orders = $inflow_q->clone()
            ->where('transaction_type_id', $inflow_types->where('code', 'OR')->first()->id);

        //// OUTFLOW
        $outflow_q = $entity->outgoing_transactions()
        ->select('id', 'amount', 'service_fee', 'transaction_type_id','created_at', 'txn_no')
        ->whereHas('status', fn ($q) => $q->where('slug', 'successful'))
        ->whereHas('type', function($q) use ($outbound_codes) {
            $q->whereIn('code', $outbound_codes);
        })
        ->whereBetween('created_at', [$fromDate, $toDate]);
        $outflow_sum = $outflow_q->clone()->sum(DB::raw('amount + service_fee'));
        $outflow_plot = $this->getPlots($outflow_q->clone()->addSelect($add_select)->get(), $from);

        $cash_out = $outflow_q->clone()
            ->where('transaction_type_id', $outflow_types->where('code', 'CO')->first()->id);
        $outflow_transfers = $outflow_q->clone()
            ->where('transaction_type_id', $outflow_types->where('code', 'TR')->first()->id);
        $outflow_orders = $outflow_q->clone()
            ->where('transaction_type_id', $outflow_types->where('code', 'OR')->first()->id);
        $outflow_invoice = $outflow_q->clone()
            ->where('transaction_type_id', $outflow_types->where('code', 'IV')->first()->id);
        $outflow_bills = $outflow_q->clone()
            ->where('transaction_type_id', $outflow_types->where('code', 'BP')->first()->id);
        $outflow_payroll = $outflow_q->clone()
            ->where('transaction_type_id', $outflow_types->where('code', 'PS')->first()->id);

        return $this->success([
            'cashflow' => (float) $inflow_sum - $outflow_sum,
            'inflow' => [
                'total' => $inflow_sum,
                'plot' => $inflow_plot,
                'transfer' => [
                    'total' => $inflow_transfers->clone()->sum(DB::raw('amount + service_fee')),
                    'list' => $inflow_transfers->clone()->take(3)->get(),
                ],
                'invoice' => [
                    'total' => $inflow_invoices->clone()->sum(DB::raw('amount + service_fee')),
                    'list' => $inflow_invoices->clone()->take(3)->get(),
                ],
                'order' => [
                    'total' => $inflow_orders->clone()->sum(DB::raw('amount + service_fee')),
                    'list' => $inflow_orders->clone()->take(3)->get(),
                ],
                'cash_in' => [
                    'total' => $cash_in->clone()->sum(DB::raw('amount + service_fee')),
                    'list' => $cash_in->clone()->get(),
                ]
            ],
            'outflow' => [
                'total' => $outflow_sum,
                'plot' => $outflow_plot,
                'cash_out' => [
                    'total' =>  $cash_out->clone()->sum(DB::raw('amount + service_fee')),
                    'list' => $cash_out->clone()->take(3)->get(),
                ],
                'transfer' => [
                    'total' =>  $outflow_transfers->clone()->sum(DB::raw('amount + service_fee')),
                    'list' => $outflow_transfers->clone()->take(3)->get(),
                ],
                'order' => [
                    'total' =>  $outflow_orders->clone()->sum(DB::raw('amount + service_fee')),
                    'list' => $outflow_orders->clone()->take(3)->get(),
                ],
                'invoice' => [
                    'total' =>  $outflow_invoice->clone()->sum(DB::raw('amount + service_fee')),
                    'list' => $outflow_invoice->clone()->take(3)->get(),
                ],
                'bill' => [
                    'total' =>  $outflow_bills->clone()->sum(DB::raw('amount + service_fee')),
                    'list' => $outflow_bills->clone()->take(3)->get(),
                ],
                'payroll' => [
                    'total' =>  $outflow_payroll->clone()->sum(DB::raw('amount + service_fee')),
                    'list' => $outflow_payroll->clone()->take(3)->get(),
                ],
            ],
        ]);
    }


    /**
     * Get plot data by filter
     * @param \Illuminate\Support\Collection $collection
     * @param string $filter
     */
    private function getPlots(Collection $collection, string $filter) {
        $map = new ArrayObject();

        if ($collection->isEmpty()) {
            return $map;
        }
        
        switch ($filter) {
            case '1y':
            $map = $collection->groupBy(['month'])->map(fn ($i) => $this->get_amount($i));
            break;

            case '3m':
            $map = $collection->groupBy(['week'])->map(fn ($i) => $this->get_amount($i));
            break;

            case '1m':
            $map = $collection->groupBy(['md'])->map(fn ($i) => $this->get_amount($i));
            break;

            case '1w':
            $map = $collection->groupBy(['md'])->map(fn ($i) => $this->get_amount($i));
            break;

            case '1d':
            $map = $collection->groupBy(['hour'])->map(fn ($i) => $this->get_amount($i));
            break;

            default:
            $map = new ArrayObject();
            break;
        }

        return empty($map) ? new ArrayObject() : $map;
    }

    /**
     * Summary of getAmount
     * @param \Illuminate\Support\Collection $items
     * @return mixed
     */
    private function get_amount(Collection $items) {
        return $items->sum(function ($item) {
            return $this->get_txn_amount($item);
        });
    }

    /**
     * Summary of get_txn_amount
     * @param \App\Models\Transaction $transaction
     * @return float|int
     */
    private function get_txn_amount(Transaction $transaction) {
        return $transaction->amount + $transaction->service_fee;
    }
}
