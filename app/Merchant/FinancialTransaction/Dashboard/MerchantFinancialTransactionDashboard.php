<?php

namespace App\Merchant\FinancialTransaction\Dashboard;

use App\Models\Balance;
use App\Models\Merchant;
use App\Models\TransactionStatus;
use App\Models\User;
use App\Traits\WithValidPhoneNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MerchantFinancialTransactionDashboard extends Component
{
    use WithValidPhoneNumber;

    public Merchant $merchant;

    public $merchantId;

    public $dateFilter = 'past_year';

    public $cashflowChart = [];

    public $cashInflowChart = [];

    public $cashOutflowChart = [];

    public $invoiceChart = [];

    public $cashflow;

    public $vsCashflow = 0;

    public $cashInCount = 0;

    public $cashOutCount = 0;

    public $invoicesCount = 0;

    public $activeBox;

    public $chartData = [];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        $this->merchantId = $this->merchant->id;
    }

    #[Computed(persist: true)]
    public function balance_amount()
    {
        return $this->merchant->latest_balance()->first()->amount ?? 0;
    }

    #[Computed]
    public function transaction_status()
    {
        return TransactionStatus::where('slug', 'successful')->first()->id;
    }

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {

        $merchant = $this->merchant;

        $dateFilter = $this->dateFilter;

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $chartData = [
            'cashflow' => [],
            'cashInflow' => [],
            'cashOutflow' => [],
            'invoice' => [],
        ];

        $cashflowChart = [];
        $cashInflowChart = [];
        $cashOutflowChart = [];
        $invoiceChart = [];

        $cashInflowTopTransaction = [];
        $cashOutflowTopTransaction = [];
        $invoicesTopTransaction = [];

        $fromDate = null;
        $toDate = null;

        $vsFromDate = null;
        $vsToDate = null;

        switch ($dateFilter) {
            case 'past_year':
                $fromDate = Carbon::today()->subYear();
                $toDate = Carbon::now();

                $vsFromDate = Carbon::now()->subYear(2);
                $vsToDate = Carbon::now()->subYear(1)->subMinutes();
                for ($i = 12; $i >= 0; $i--) {
                    $month = Carbon::today()->startOfMonth()->subMonths($i);
                    $year = Carbon::today()->startOfMonth()->subMonths($i)->format('Y');

                    // if is on the current date
                    if ($i === 0) {
                        $cashInSum = $merchant->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [Carbon::today()->startOfMonth(), Carbon::now()])->sum('amount');
                        $cashOutSum = $merchant->outgoing_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [Carbon::today()->startOfMonth(), Carbon::now()])->sum(DB::raw('amount + service_fee'));
                        $invoiceSum = $merchant->outgoing_invoices()->where(function ($q) {
                            $q->where('status', 'paid');
                            $q->orWhere('status', 'partial');
                        })
                            ->whereBetween('invoices.created_at', [Carbon::today()->startOfMonth(), Carbon::now()])
                            ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                            ->leftJoin('invoice_inclusions', 'invoices.id', '=', 'invoice_inclusions.invoice_id')
                            ->selectRaw('COALESCE(SUM((invoice_items.price * invoice_items.quantity) +
                                COALESCE(CASE WHEN invoice_inclusions.deduct = 1 THEN invoice_inclusions.amount ELSE 0 END, 0) -
                                COALESCE(CASE WHEN invoice_inclusions.deduct = 0 THEN invoice_inclusions.amount ELSE 0 END, 0)), 0) as total_amt')
                            ->value('total_amt');
                    } else {
                        $cashInSum = $merchant->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereYear('created_at', $year)->whereMonth('created_at', $month->month)->sum('amount');
                        $cashOutSum = $merchant->outgoing_transactions()->where('transaction_status_id', $this->transaction_status)->whereYear('created_at', $year)->whereMonth('created_at', $month->month)->sum(DB::raw('amount + service_fee'));
                        $invoiceSum = $merchant->outgoing_invoices()
                            ->where(function ($q) {
                                $q->where('status', 'paid');
                                $q->orWhere('status', 'partial');
                            })
                            ->whereMonth('invoices.created_at', $month->month)->whereYear('invoices.created_at', $year)
                            ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                            ->leftJoin('invoice_inclusions', 'invoices.id', '=', 'invoice_inclusions.invoice_id')
                            ->selectRaw('COALESCE(SUM((invoice_items.price * invoice_items.quantity) +
                                COALESCE(CASE WHEN invoice_inclusions.deduct = 1 THEN invoice_inclusions.amount ELSE 0 END, 0) -
                                COALESCE(CASE WHEN invoice_inclusions.deduct = 0 THEN invoice_inclusions.amount ELSE 0 END, 0)), 0) as total_amt')
                            ->value('total_amt');
                    }

                    $cashflow = $cashInSum - $cashOutSum;

                    array_push($chartData['cashflow'], [
                        'cashflow' => $cashflow,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $month->shortMonthName . ' ' . $year,
                    ]);

                    array_push($chartData['cashInflow'], [
                        'cashInflow' => $cashInSum,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $month->shortMonthName . ' ' . $year,
                    ]);

                    array_push($chartData['cashOutflow'], [
                        'cashOutflow' => $cashOutSum,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $month->shortMonthName . ' ' . $year,
                    ]);

                    array_push($chartData['invoice'], [
                        'invoice' => $invoiceSum,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $month->shortMonthName . ' ' . $year,
                    ]);
                }
                break;
            case 'past_30_days':
                $fromDate = Carbon::now()->subDays(30);
                $toDate = Carbon::now();

                $vsFromDate = Carbon::now()->subDays(60);
                $vsToDate = Carbon::now()->subDays(30)->subMinutes();

                $oneDay = 1;
                $twentyNineDays = 29;
                $daysPerLabel = 3;

                $iterator = ($twentyNineDays + $oneDay) / $daysPerLabel;

                $daysTracker = $twentyNineDays;

                for ($i = $iterator; $i >= 1; $i--) {

                    $date = Carbon::today()->subDays($daysTracker);
                    $month = $date->copy()->month;
                    $day = $date->copy()->day;
                    $year = $date->copy()->year;

                    // if is on the current date
                    $invoicePaidStatus = $merchant->outgoing_invoices()->where(function ($q) {
                        $q->where('status', 'paid');
                        $q->orWhere('status', 'partial');
                    })->whereBetween('created_at', [Carbon::today()->startOfMonth(), Carbon::now()])->get();

                    $cashInSum = $merchant->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$date->copy(), $date->copy()->addDays($daysPerLabel - 1)->endOfDay()])->sum(DB::raw('amount + service_fee'));
                    $cashOutSum = $merchant->outgoing_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$date->copy(), $date->copy()->addDays($daysPerLabel - 1)->endOfDay()])->sum(DB::raw('amount + service_fee'));

                    $invoiceSum = $merchant->outgoing_invoices()
                        ->where(function ($q) {
                            $q->where('status', 'paid');
                            $q->orWhere('status', 'partial');
                        })
                        ->whereBetween('invoices.created_at', [$date->copy(), $date->copy()->addDays($daysPerLabel - 1)->endOfDay()])
                        ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                        ->leftJoin('invoice_inclusions', 'invoices.id', '=', 'invoice_inclusions.invoice_id')
                        ->selectRaw('COALESCE(SUM((invoice_items.price * invoice_items.quantity) +
                            COALESCE(CASE WHEN invoice_inclusions.deduct = 1 THEN invoice_inclusions.amount ELSE 0 END, 0) -
                            COALESCE(CASE WHEN invoice_inclusions.deduct = 0 THEN invoice_inclusions.amount ELSE 0 END, 0)), 0) as total_amt')
                        ->value('total_amt');
                    $cashflow = $cashInSum - $cashOutSum;

                    array_push($chartData['cashflow'], [
                        'cashflow' => $cashflow,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName . ' ' . $date->copy()->day . '-' . $date->copy()->addDays(2)->day . ' ' . $date->copy()->year,
                    ]);

                    array_push($chartData['cashInflow'], [
                        'cashInflow' => $cashInSum,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName . ' ' . $date->day . '-' . $date->copy()->addDays(2)->day . ' ' . $date->copy()->year,
                    ]);

                    array_push($chartData['cashOutflow'], [
                        'cashOutflow' => $cashOutSum,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName . ' ' . $date->day . '-' . $date->copy()->addDays(2)->day . ' ' . $date->copy()->year,
                    ]);

                    array_push($chartData['invoice'], [
                        'invoice' => $invoiceSum,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName . ' ' . $date->day . '-' . $date->copy()->addDays(2)->day . ' ' . $date->copy()->year,
                    ]);

                    $daysTracker = $daysTracker - $daysPerLabel;
                }
                break;
            case 'past_week':
                $sixDaysBack = 6;
                $oneDay = 1;
                $sevenDays = $sixDaysBack + $oneDay;

                $fromDate = Carbon::now()->subDays($sevenDays);
                $toDate = Carbon::now();

                $vsFromDate = Carbon::now()->subDays($sevenDays * 2);
                $vsToDate = Carbon::now()->subDays($sevenDays)->subMinutes();

                for ($i = $sevenDays; $i >= 1; $i--) {
                    $date = Carbon::today()->subDays($i - 1);
                    $month = $date->copy()->month;
                    $day = $date->copy()->day;
                    $year = $date->copy()->year;

                    $dateString = $date->copy()->toDateString();

                    $cashInSum = $merchant->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereDate('created_at', '=', $dateString)->sum(DB::raw('amount + service_fee'));
                    $cashOutSum = $merchant->outgoing_transactions()->where('transaction_status_id', $this->transaction_status)->whereDate('created_at', '=', $dateString)->sum(DB::raw('amount + service_fee'));
                    $invoiceSum = $merchant->outgoing_invoices()
                        ->where(function ($q) {
                            $q->where('status', 'paid');
                            $q->orWhere('status', 'partial');
                        })
                        ->whereDate('invoices.created_at', '=', $dateString)
                        ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                        ->leftJoin('invoice_inclusions', 'invoices.id', '=', 'invoice_inclusions.invoice_id')
                        ->selectRaw('COALESCE(SUM((invoice_items.price * invoice_items.quantity) +
                            COALESCE(CASE WHEN invoice_inclusions.deduct = 1 THEN invoice_inclusions.amount ELSE 0 END, 0) -
                            COALESCE(CASE WHEN invoice_inclusions.deduct = 0 THEN invoice_inclusions.amount ELSE 0 END, 0)), 0) as total_amt')
                        ->value('total_amt');
                    $cashflow = $cashInSum - $cashOutSum;

                    array_push($chartData['cashflow'], [
                        'cashflow' => $cashflow,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName . ' ' . $date->copy()->day,
                    ]);

                    array_push($chartData['cashInflow'], [
                        'cashInflow' => $cashInSum,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName . ' ' . $date->copy()->day,
                    ]);

                    array_push($chartData['cashOutflow'], [
                        'cashOutflow' => $cashOutSum,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName . ' ' . $date->copy()->day,
                    ]);

                    array_push($chartData['invoice'], [
                        'invoice' => $invoiceSum,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName . ' ' . $date->copy()->day,
                    ]);
                }
                break;
            case 'day':
                $fromDate = Carbon::now()->subHours(24);
                $toDate = Carbon::now();

                $vsFromDate = Carbon::now()->subDays(48);
                $vsToDate = Carbon::now()->subDays(24)->subMinutes();

                $twentyFourHours = 24;
                $timezone = 'Asia/Manila';
                for ($i = $twentyFourHours; $i >= 1; $i--) {
                    $date = Carbon::now($timezone)->subHours($i);
                    $addAnHourToDate = $date->copy()->addHours(1);
                    $month = $date->copy();
                    $year = $date->copy()->format('Y');

                    $cashInSum = $merchant->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$date->copy(), $addAnHourToDate->copy()])->sum(DB::raw('amount + service_fee'));
                    $cashOutSum = $merchant->outgoing_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$date->copy(), $addAnHourToDate->copy()])->sum(DB::raw('amount + service_fee'));
                    $invoiceSum = $merchant->outgoing_invoices()
                        ->where(function ($q) {
                            $q->where('status', 'paid');
                            $q->orWhere('status', 'partial');
                        })
                        ->whereBetween('invoices.created_at', [$date->copy(), $addAnHourToDate->copy()])
                        ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                        ->leftJoin('invoice_inclusions', 'invoices.id', '=', 'invoice_inclusions.invoice_id')
                        ->selectRaw('COALESCE(SUM((invoice_items.price * invoice_items.quantity) +
                            COALESCE(CASE WHEN invoice_inclusions.deduct = 1 THEN invoice_inclusions.amount ELSE 0 END, 0) -
                            COALESCE(CASE WHEN invoice_inclusions.deduct = 0 THEN invoice_inclusions.amount ELSE 0 END, 0)), 0) as total_amt')
                        ->value('total_amt');

                    $cashflow = $cashInSum - $cashOutSum;
                    // $hours = floor($date->copy()->diffInHours());
                    $hour = (int)$date->copy()->diffInHours();

                    array_push($chartData['cashflow'], [
                        'cashflow' => $cashflow,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $hour . 'h',
                    ]);

                    array_push($chartData['cashInflow'], [
                        'cashInflow' => $cashInSum,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $hour . 'h',
                    ]);

                    array_push($chartData['cashOutflow'], [
                        'cashOutflow' => $cashOutSum,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $hour . 'h',
                    ]);

                    array_push($chartData['invoice'], [
                        'invoice' => $invoiceSum,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $hour . 'h',
                    ]);
                }
            default:
        }
        $this->cashInCount = $merchant->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$fromDate, $toDate])->count();
        $this->cashOutCount = $merchant->outgoing_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$fromDate, $toDate])->count();
        $yearCashInSum = $merchant->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$fromDate, $toDate])->sum('amount');
        $yearCashOutSum = $merchant->outgoing_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$fromDate, $toDate])->sum(DB::raw('amount + service_fee'));
        $pastYearCashflow = $yearCashInSum - $yearCashOutSum;
        $this->cashflow = $pastYearCashflow;

        $prevPastYearCashInSum = $merchant->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$vsFromDate, $vsToDate])->sum('amount');
        $prevPastYearCashOutSum = $merchant->outgoing_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$vsFromDate, $vsToDate])->sum(DB::raw('amount + service_fee'));
        $this->vsCashflow = $prevPastYearCashInSum - $prevPastYearCashOutSum;

        $this->invoicesCount = $merchant->outgoing_invoices()->where(function ($q) {
            $q->where('status', 'paid');
            $q->orWhere('status', 'partial');
        })->whereBetween('created_at', [$fromDate, $toDate])->count();

        $this->chartData = $chartData;

        $top3CashIn = $merchant->incoming_transactions()
            ->where('transaction_status_id', $this->transaction_status)
            ->select('transaction_channel_id', 'sender_type', 'sender_id')
            ->selectRaw('SUM(amount) as total_amt')
            ->with(['sender'])
            ->groupBy('transaction_channel_id', 'sender_type', 'sender_id')
            ->orderByDesc('total_amt')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->take(3)
            ->get();

        foreach ($top3CashIn as $cashIn) {
            if (get_class($cashIn->sender) == User::class) {
                array_push($cashInflowTopTransaction, ['name' => $this->format_phone_number($cashIn->sender->phone_number, $cashIn->sender->phone_iso), 'amount' => $cashIn->total_amt]);
                continue;
            }
            array_push($cashInflowTopTransaction, ['name' => $cashIn->sender->name, 'amount' => $cashIn->total_amt]);
        }

        $top3CashOut = $merchant->outgoing_transactions()
            ->where('transaction_status_id', $this->transaction_status)
            ->select('recipient_type', 'recipient_id')
            ->selectRaw('SUM(amount + service_fee) as total_amt')
            ->with(['recipient'])
            ->groupBy('recipient_type', 'recipient_id')
            ->orderByDesc('total_amt')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->take(3)
            ->get();

        foreach ($top3CashOut as $cashOut) {
            if (get_class($cashOut->recipient) == User::class) {
                array_push($cashOutflowTopTransaction, ['name' => $this->format_phone_number($cashOut->recipient->phone_number, $cashOut->recipient->phone_iso), 'amount' => $cashOut->total_amt]);
                continue;
            }
            array_push($cashOutflowTopTransaction, ['name' => $cashOut->recipient->name, 'amount' => $cashOut->total_amt]);
        }

        $invoicePaidTransactions = $merchant->outgoing_invoices()
            ->where(function ($q) {
                $q->where('status', 'paid');
                $q->orWhere('status', 'partial');
            })
            ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('invoice_inclusions', 'invoices.id', '=', 'invoice_inclusions.invoice_id')
            ->select('invoices.recipient_id as recipient_id', 'invoices.recipient_type as recipient_type')
            ->selectRaw('COALESCE(SUM((invoice_items.price * invoice_items.quantity) +
        COALESCE(CASE WHEN invoice_inclusions.deduct = 1 THEN invoice_inclusions.amount ELSE 0 END, 0) -
        COALESCE(CASE WHEN invoice_inclusions.deduct = 0 THEN invoice_inclusions.amount ELSE 0 END, 0)), 0) as total_amt')
            ->groupBy('recipient_id', 'recipient_type')
            ->whereBetween('invoices.created_at', [$fromDate, $toDate])
            ->orderByDesc('total_amt')
            ->take(3)
            ->get();

        foreach ($invoicePaidTransactions as $transaction) {
            if (array_key_exists($transaction->recipient_id, $invoicesTopTransaction)) {
                $invoicesTopTransaction[$transaction->recipient_id]['amount'] += $transaction->total_amt;
            } else {
                $name = 'undefined';
                if ($transaction->recipient_type === User::class) {
                    $vUser = User::where('id', $transaction->recipient_id)->first();
                    $name = $this->format_phone_number($vUser->phone_number, $vUser->phone_iso);
                }
                if ($transaction->recipient_type === Merchant::class) {
                    $vMerchant = Merchant::where('id', $transaction->recipient_id)->first();
                    $name = $vMerchant->name;
                }
                $invoicesTopTransaction[$transaction->recipient_id] = [
                    'name' => $name,
                    'amount' => $transaction->total_amt,
                ];
            }
        }

        uasort($invoicesTopTransaction, function ($a, $b) {
            return $b['amount'] <=> $a['amount'];
        });

        return view('merchant.financial-transaction.dashboard.merchant-financial-transaction-dashboard', compact('cashInflowTopTransaction', 'cashOutflowTopTransaction', 'invoicesTopTransaction'));
    }
}
