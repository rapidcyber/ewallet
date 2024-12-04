<?php

namespace App\User\Dashboard;

use App\Models\TransactionStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class UserDashboard extends Component
{
    public $dateFilter = 'past_year';

    public $balance;

    public $chartData = [];

    #[Computed]
    public function balance_amount()
    {
        return auth()->user()->latest_balance()->first()->amount ?? 0;
    }

    #[Computed]
    public function transaction_status()
    {
        return TransactionStatus::where('slug', 'successful')->first()->id;
    }

    #[Layout('layouts.user')]
    public function render()
    {
        $user = auth()->user();
        $dateFilter = $this->dateFilter;

        $chartData = [
            'cashflow' => [],
            'cashInflow' => [],
            'cashOutflow' => [],
        ];

        $cashInflowToptransaction = [];
        $cashOutflowToptransaction = [];

        $fromDate = null;
        $toDate = null;

        $vsFromDate = null;
        $vsToDate = null;

        switch ($dateFilter) {
            case 'past_year':
                $fromDate = Carbon::now()->subYear();
                $toDate = Carbon::now();

                $vsFromDate = Carbon::now()->subYears(2);
                $vsToDate = Carbon::now()->subYear();

                for ($i = 12; $i >= 0; $i--) {
                    $month = Carbon::today()->startOfMonth()->subMonths($i);
                    $year = Carbon::today()->startOfMonth()->subMonths($i)->format('Y');

                    // if is on the current date
                    if ($i === 0) {
                        $cashInSum = $user->incoming_transactions()
                            ->where('transaction_status_id', $this->transaction_status)
                            ->whereBetween('created_at', [Carbon::today()->startOfMonth(), Carbon::now()])
                            ->sum('amount');
                        $cashOutSum = $user->outgoing_transactions()
                            ->where('transaction_status_id', $this->transaction_status)
                            ->whereBetween('created_at', [Carbon::today()->startOfMonth(), Carbon::now()])
                            ->sum(DB::raw('amount + service_fee'));
                    } else {
                        $cashInSum = $user->incoming_transactions()
                            ->where('transaction_status_id', $this->transaction_status)
                            ->whereYear('created_at', $year)
                            ->whereMonth('created_at', $month->month)
                            ->sum('amount');
                        $cashOutSum = $user->outgoing_transactions()
                            ->where('transaction_status_id', $this->transaction_status)
                            ->whereYear('created_at', $year)
                            ->whereMonth('created_at', $month->month)
                            ->sum(DB::raw('amount + service_fee'));
                    }

                    $cashflow = $cashInSum - $cashOutSum;

                    array_push($chartData['cashflow'], [
                        'cashflow' => $cashflow,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $month->shortMonthName.' '.$year,
                    ]);

                    array_push($chartData['cashInflow'], [
                        'cashInflow' => $cashInSum,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $month->shortMonthName.' '.$year,
                    ]);

                    array_push($chartData['cashOutflow'], [
                        'cashOutflow' => $cashOutSum,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $month->shortMonthName.' '.$year,
                    ]);
                }
                break;

            case 'past_30_days':
                $oneDay = 1;
                $twentyNineDays = 29;
                $daysPerLabel = 3;

                $iterator = ($twentyNineDays + $oneDay) / $daysPerLabel;

                $fromDate = Carbon::now()->subDays(30);
                $toDate = Carbon::now();

                $vsFromDate = Carbon::now()->subDays(60);
                $vsToDate = Carbon::now()->subDays(30)->subMinutes();

                $daysTracker = $twentyNineDays;

                for ($i = $iterator; $i >= 1; $i--) {

                    $date = Carbon::today()->subDays($daysTracker);
                    $month = $date->copy()->month;
                    $day = $date->copy()->day;
                    $year = $date->copy()->year;

                    $cashInSum = $user->incoming_transactions()
                        ->where('transaction_status_id', $this->transaction_status)
                        ->whereBetween('created_at', [$date->copy(), $date->copy()->addDays($daysPerLabel - 1)->endOfDay()])
                        ->sum('amount');
                    $cashOutSum = $user->outgoing_transactions()
                        ->where('transaction_status_id', $this->transaction_status)
                        ->whereBetween('created_at', [$date->copy(), $date->copy()->addDays($daysPerLabel - 1)->endOfDay()])
                        ->sum(DB::raw('amount + service_fee'));

                    $cashflow = $cashInSum - $cashOutSum;

                    array_push($chartData['cashflow'], [
                        'cashflow' => $cashflow,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName.' '.$date->copy()->day.'-'.$date->copy()->addDays(2)->day.' '.$date->copy()->year,
                    ]);

                    array_push($chartData['cashInflow'], [
                        'cashInflow' => $cashInSum,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName.' '.$date->day.'-'.$date->copy()->addDays(2)->day.' '.$date->copy()->year,
                    ]);

                    array_push($chartData['cashOutflow'], [
                        'cashOutflow' => $cashOutSum,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName.' '.$date->day.'-'.$date->copy()->addDays(2)->day.' '.$date->copy()->year,
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

                    $cashInSum = $user->incoming_transactions()
                        ->where('transaction_status_id', $this->transaction_status)
                        ->whereDate('created_at', '=', $dateString)
                        ->sum('amount');
                    $cashOutSum = $user->outgoing_transactions()
                        ->where('transaction_status_id', $this->transaction_status)
                        ->whereDate('created_at', '=', $dateString)
                        ->sum('amount');
                    $cashflow = $cashInSum - $cashOutSum;

                    array_push($chartData['cashflow'], [
                        'cashflow' => $cashflow,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName.' '.$date->copy()->day,
                    ]);

                    array_push($chartData['cashInflow'], [
                        'cashInflow' => $cashInSum,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName.' '.$date->copy()->day,
                    ]);

                    array_push($chartData['cashOutflow'], [
                        'cashOutflow' => $cashOutSum,
                        'shortMonthName' => $date->shortMonthName,
                        'year' => $date->year,
                        'dateText' => $date->copy()->shortMonthName.' '.$date->copy()->day,
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

                    $cashInSum = $user->incoming_transactions()
                        ->where('transaction_status_id', $this->transaction_status)
                        ->whereBetween('created_at', [$date->copy(), $addAnHourToDate->copy()])
                        ->sum('amount');
                    $cashOutSum = $user->outgoing_transactions()
                        ->where('transaction_status_id', $this->transaction_status)
                        ->whereBetween('created_at', [$date->copy(), $addAnHourToDate->copy()])
                        ->sum(DB::raw('amount + service_fee'));

                    $cashflow = $cashInSum - $cashOutSum;
                    $hour = (int)$date->copy()->diffInHours();

                    array_push($chartData['cashflow'], [
                        'cashflow' => $cashflow,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $hour.'h',
                    ]);

                    array_push($chartData['cashInflow'], [
                        'cashInflow' => $cashInSum,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $hour.'h',
                    ]);

                    array_push($chartData['cashOutflow'], [
                        'cashOutflow' => $cashOutSum,
                        'shortMonthName' => $month->shortMonthName,
                        'year' => $year,
                        'dateText' => $hour.'h',
                    ]);
                }
                break;
            default:

                break;
        }

        $this->chartData = $chartData;

        $cashInCount = $user->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$fromDate, $toDate])->count();
        $cashOutCount = $user->outgoing_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$fromDate, $toDate])->count();
        $yearCashInSum = $user->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$fromDate, $toDate])->sum('amount');
        $yearCashOutSum = $user->outgoing_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$fromDate, $toDate])->sum(DB::raw('amount + service_fee'));
        $pastYearCashflow = $yearCashInSum - $yearCashOutSum;
        $cashflow = $pastYearCashflow;

        $prevPastYearCashInSum = $user->incoming_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$vsFromDate, $vsToDate])->sum('amount');
        $prevPastYearCashOutSum = $user->outgoing_transactions()->where('transaction_status_id', $this->transaction_status)->whereBetween('created_at', [$vsFromDate, $vsToDate])->sum(DB::raw('amount + service_fee'));
        $vsCashflow = $prevPastYearCashInSum - $prevPastYearCashOutSum;

        $top3CashIn = $user->incoming_transactions()
            ->where('transaction_status_id', $this->transaction_status)
            ->select('transaction_channel_id', 'sender_type', 'sender_id')
            ->selectRaw('SUM(amount) as total_amt')
            ->with(['sender' => function (MorphTo $query) {
                $query->morphWith([
                    User::class => ['profile'],
                ]);
            }])
            ->groupBy('transaction_channel_id', 'sender_type', 'sender_id')
            ->orderByDesc('total_amt')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->limit(3)
            ->get();

        foreach ($top3CashIn as $cashIn) {
            array_push($cashInflowToptransaction, ['name' => $cashIn->sender->name, 'amount' => $cashIn->total_amt]);
        }

        $top3CashOut = $user->outgoing_transactions()
            ->where('transaction_status_id', $this->transaction_status)
            ->select('transaction_channel_id', 'recipient_type', 'recipient_id')
            ->selectRaw('SUM(amount + service_fee) as total_amt')
            ->with(['recipient' => function (MorphTo $query) {
                $query->morphWith([
                    User::class => ['profile'],
                ]);
            }])
            ->groupBy('transaction_channel_id', 'recipient_type', 'recipient_id')
            ->orderByDesc('total_amt')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->limit(3)
            ->get();

        foreach ($top3CashOut as $cashOut) {
            array_push($cashOutflowToptransaction, ['name' => $cashOut->recipient->name, 'amount' => $cashOut->total_amt]);
        }

        return view('user.dashboard.user-dashboard')->with([
            'cashflow' => $cashflow,
            'vsCashflow' => $vsCashflow,
            'cashInCount' => $cashInCount,
            'cashOutCount' => $cashOutCount,
            'cashInflowToptransaction' => $cashInflowToptransaction,
            'cashOutflowToptransaction' => $cashOutflowToptransaction,
        ]);
    }
}
