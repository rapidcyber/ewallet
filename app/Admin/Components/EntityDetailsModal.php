<?php

namespace App\Admin\Components;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User;
use App\Traits\WithImage;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;


class EntityDetailsModal extends Component
{
    use WithImage;

    public $dateFilter = '';

    public $dateLabel = '';

    public $entity_id;

    public $entity_type;

    public $entity_model;

    public $entity_name;

    public $cashflowChartData;

    public $cashflow = [
        'present' => 0,
        'previous' => 0,
        'positive' => false
    ];

    public $cashInflow = [
        'present' => 0,
        'previous' => 0,
        'positive' => false
    ];

    public $cashOutflow = [
        'present' => 0,
        'previous' => 0,
        'positive' => false
    ];

    public function mount($entity_id, $entity_type, $dateFilter, $dateLabel)
    {

        $availableEntityTypes = ['user', 'merchant'];

        if (!in_array($entity_type, $availableEntityTypes)) {
            return abort(404);
        }

        $availableDateFilters = ['none', 'past_year', 'past_6_months', 'past_30_days', 'past_week', 'past_24_hours'];

        if (!in_array($dateFilter, $availableDateFilters)) {
            $this->dateFilter = 'past_year';
        } else {
            $this->dateFilter = $dateFilter;
        }

        if ($entity_type === 'user') {
            $user = User::with(['profile', 'media' => function ($q) {
                $q->where('collection_name', 'profile_picture');
            }])
                ->where('id', $entity_id)
                ->firstOrFail();

            $this->entity_model = $user;
        } else if ($entity_type === 'merchant') {
            $merchant = Merchant::with(['media' => function ($q) {
                $q->where('collection_name', 'merchant_logo');
            }])
                ->where('id', $entity_id)
                ->firstOrFail();

            $this->entity_model = $merchant;
        }

        $this->dateLabel = $dateLabel;

        $this->startChart();
    }

    #[Computed]
    public function earliest_transaction()
    {
        return Transaction::orderBy('created_at', 'asc')->first();
    }

    #[Computed]
    public function successful_status()
    {
        return TransactionStatus::where('slug', 'successful')->first()->id;
    }

    private function get_all_transactions_date_range()
    {
        if ($this->earliest_transaction) {
            return [Carbon::parse($this->earliest_transaction->created_at), Carbon::now()];
        } else {
            $date = Carbon::now();
            return [$date->copy()->subYear(), $date];
        }
    }

    private function get_all_transactions_chart_date_range()
    {
        [$startDate, $endDate] = $this->get_all_transactions_date_range();


        $yearsDiff = $startDate->diffInYears($endDate);

        if ($yearsDiff >= 1) {
            return array_map(function ($year) {
                return [Carbon::now()->subYears($year)->startOfYear(), Carbon::now()->subYears($year)->endOfYear()];
            }, range(intval($yearsDiff), 0, -1));
        } else {
            $monthsDiff = $startDate->diffInMonths($endDate);
            return array_map(function ($month) {
                return [Carbon::now()->subMonths($month)->startOfMonth(), Carbon::now()->subMonths($month)->endOfMonth()];
            }, range(intval(ceil($monthsDiff)), 0, -1));
        }
    }

    private function get_chart_date_range()
    {
        return [
            'past_24_hours' => array_map(function ($hour) {
                return [
                    Carbon::now()->subHours($hour)->startOfHour(),
                    Carbon::now()->subHours($hour)->endOfHour(),
                ];
            }, range(23, 0, -1)),
            'past_week' => array_map(function ($day) {
                return [
                    Carbon::now()->subDays($day)->startOfDay(),
                    Carbon::now()->subDays($day)->endOfDay(),
                ];
            }, range(6, 0, -1)),
            'past_30_days' => array_map(function ($day) {
                return [
                    Carbon::now()->subDays($day)->startOfDay(),
                    Carbon::now()->subDays($day)->endOfDay(),
                ];
            }, range(29, 0, -1)),
            'past_6_months' => array_map(function ($month) {
                return [
                    Carbon::now()->subMonths($month)->startOfMonth(),
                    Carbon::now()->subMonths($month)->endOfMonth(),
                ];
            }, range(5, 0, -1)),
            'past_year' => array_map(function ($month) {
                return [
                    Carbon::now()->subMonths($month)->startOfMonth(),
                    Carbon::now()->subMonths($month)->endOfMonth(),
                ];
            }, range(11, 0, -1)),
            'none' => $this->get_all_transactions_chart_date_range()
        ];
    }

    public function get_date_range()
    {
        $transaction_created_date = $this->earliest_transaction ? Carbon::parse($this->earliest_transaction->created_at) : Carbon::now()->subYear();

        return [
            'present' => [
                'past_24_hours' => [Carbon::now()->subDay(), Carbon::now()],
                'past_week' => [Carbon::now()->subWeek(), Carbon::now()],
                'past_30_days' => [Carbon::now()->subDays(30), Carbon::now()],
                'past_6_months' => [Carbon::now()->subMonths(6), Carbon::now()],
                'past_year' => [Carbon::now()->subYear(), Carbon::now()],
                'none' => [$transaction_created_date, Carbon::now()]
            ],
            'previous' => [
                'past_24_hours' => [Carbon::now()->subDay()->subDay(), Carbon::now()->subDay()],
                'past_week' => [Carbon::now()->subWeek()->subWeek(), Carbon::now()->subWeek()],
                'past_30_days' => [Carbon::now()->subDays(30)->subDays(30), Carbon::now()->subDays(30)],
                'past_6_months' => [Carbon::now()->subMonths(6)->subMonths(6), Carbon::now()->subMonths(6)],
                'past_year' => [Carbon::now()->subYear()->subYear(), Carbon::now()->subYear()],
            ]
        ];
    }

    public function get_date_format()
    {
        $format = 'Y-m-d H:i:s';
        switch ($this->dateFilter) {
            case 'none':
                $format = 'Y';
                $this->dateLabel = '1 year';
                break;
            case 'past_24_hours':
                $format = 'H';
                $this->dateLabel = '24 hours';
                break;
            case 'past_week':
                $format = 'l';
                $this->dateLabel = 'week';
                break;
            case 'past_30_days':
                $format = 'M d';
                $this->dateLabel = '30 days';
                break;
            case 'past_6_months':
                $format = 'F';
                $this->dateLabel = '6 months';
                break;
            case 'past_year':
                $format = 'Y-m';
                $this->dateLabel = 'year';
                break;
        }

        return $format;
    }

    public function get_date_labels()
    {
        $labels = [];
        switch ($this->dateFilter) {
            case 'past_24_hours':
                $now = Carbon::now();

                for ($i = 23; $i >= 0; $i--) {
                    if ($i === 0) {
                        $labels[] = 'now';
                        continue;
                    }

                    // $startTime = $now->copy()->subHours($i);

                    // $hoursPassed = $startTime->diffInHours($now);

                    $labels[] = $i . 'h';
                }
                break;
            case 'past_week':
                $now = Carbon::now();
                for ($i = 6; $i >= 0; $i--) {
                    $labels[] = $now->copy()->subDays($i)->format('l');
                }
                break;
            case 'past_30_days':
                $now = Carbon::now();
                for ($i = 29; $i >= 0; $i--) {
                    $labels[] = $now->copy()->subDays($i)->format('M d');
                }
                break;
            case 'past_6_months':
                $now = Carbon::now();
                for ($i = 5; $i >= 0; $i--) {
                    $labels[] = $now->copy()->subMonths($i)->format('F');
                }
                break;
            case 'past_year':
                $now = Carbon::now();
                for ($i = 11; $i >= 0; $i--) {
                    $labels[] = $now->copy()->subMonths($i)->format('Y-m');
                }
                break;
            case 'none':
                $now = Carbon::now();

                [$startDate, $endDate] = $this->get_all_transactions_date_range();

                if ($startDate->diffInYears($endDate) >= 1) {
                    for ($i = intval($startDate->diffInYears($endDate)); $i >= 0; $i--) {
                        $labels[] = $now->copy()->subYears($i)->format('Y');
                    }
                } else {
                    for ($i = intval(ceil($startDate->diffInMonths($endDate))); $i >= 0; $i--) {
                        $labels[] = $now->copy()->subMonths($i)->format('Y-m');
                    }
                }
                break;
        }

        return $labels;
    }

    public function startChart()
    {
        $data = $this->updateCashFlowChart();

        $this->cashflowChartData = $data;
        // $this->dispatch('update-cash-flow-chart', $data);
    }
    public function updateCashFlowChart()
    {
        $date = $this->get_chart_date_range();
        $labels = $this->get_date_labels();

        $infos = [];

        foreach ($date[$this->dateFilter] as $range) {
            $cashInflowSum = $this->entity_model->incoming_transactions()->where('transactions.transaction_status_id', $this->successful_status)->whereBetween('transactions.created_at', [$range[0], $range[1]])->sum('amount');

            $cashOutflowSum = $this->entity_model->outgoing_transactions()->where('transactions.transaction_status_id', $this->successful_status)->whereBetween('transactions.created_at', [$range[0], $range[1]])->sum('amount');

            $cashflow = $cashInflowSum - $cashOutflowSum;

            $infos[] = $cashflow;
        }

        $infos = array_combine($labels, array_values($infos));


        return [
            'labels' => $labels,
            'infos' => $infos,
            'data_label' => 'Cashflow',
        ];
    }

    #[Computed]
    public function balance_amount()
    {
        return $this->entity_model->latest_balance()->first()->amount ?? 0;
    }

    public function render()
    {
        $date = $this->get_date_range();

        $cashInflowTotal = $this->entity_model->incoming_transactions()->where('transactions.transaction_status_id', $this->successful_status)->whereBetween('created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])->sum('amount');

        $vsCashInflowTotal = 0;
        // No need to get the vs. cash inflow sum if the date filter `All transactions` is selected
        if ($this->dateFilter !== 'none') {
            $vsCashInflowTotal = $this->entity_model->incoming_transactions()->where('transactions.transaction_status_id', $this->successful_status)->whereBetween('created_at', [$date['previous'][$this->dateFilter][0], $date['previous'][$this->dateFilter][1]])->sum('amount');
        }

        $cashOutflowTotal = $this->entity_model->outgoing_transactions()->where('transactions.transaction_status_id', $this->successful_status)->whereBetween('created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])->sum('amount');

        $vsCashOutTotal = 0;
        // No need to get the vs. cash outflow sum if the date filter `All transactions` is selected
        if ($this->dateFilter !== 'none') {
            $vsCashOutTotal =  $this->entity_model->outgoing_transactions()->where('transactions.transaction_status_id', $this->successful_status)->whereBetween('created_at', [$date['previous'][$this->dateFilter][0], $date['previous'][$this->dateFilter][1]])->sum('amount');
        }

        $cashflowTotal = $cashInflowTotal - $cashOutflowTotal;

        $vsCashflowTotal = 0;
        $cashflow_difference = 0;
        // No need to get the vs. cashflow sum if the date filter `All transactions` is selected
        if ($this->dateFilter !== 'none') {
            $vsCashflowTotal = $vsCashInflowTotal - $vsCashOutTotal;
            $cashflow_difference = $cashflowTotal - $vsCashflowTotal;
        }

        $this->cashflow = [
            'present' => $cashflowTotal,
            'previous' => abs($cashflow_difference),
            'positive' => $cashflow_difference >= 0,
        ];

        $cashinflow_difference = 0;
        // No need to get the vs. cash inflow difference if the date filter `All transactions` is selected
        if ($this->dateFilter !== 'none') {
            $cashinflow_difference = $cashInflowTotal - $vsCashInflowTotal;
        }

        $this->cashInflow = [
            'present' => $cashInflowTotal,
            'previous' => abs($cashinflow_difference),
            'positive' => $cashinflow_difference >= 0
        ];

        $cashoutflow_difference = 0;
        // No need to get the vs. cash outflow difference if the date filter `All transactions` is selected
        if ($this->dateFilter !== 'none') {
            $cashoutflow_difference = $cashOutflowTotal - $vsCashOutTotal;
        }

        $this->cashOutflow = [
            'present' => $cashOutflowTotal,
            'previous' => abs($cashoutflow_difference),
            'positive' => $cashoutflow_difference >= 0
        ];

        return view('admin.components.entity-details-modal');
    }
}
