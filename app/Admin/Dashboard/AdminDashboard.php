<?php

namespace App\Admin\Dashboard;

use Livewire\Attributes\Layout;
use App\Models\Balance;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Psy\Command\WhereamiCommand;
use Termwind\Components\BreakLine;

class AdminDashboard extends Component
{
    public $dateFilter = 'none', $fromDate = null, $toDate = null, $vsFromDate = null, $vsToDate = null;

    public $topEntityDetailsModal = [
        'visible' => false,
        'data' => [],
        'type' => '',
    ];

    public $slide = 1;
    public $maxSlideNum = 2;

    // enum: 'merchants' || 'users';
    public $topTableActiveTab = 'merchants';

    // Merchant table
    public $topMerchantTableOrderBy = [
        'column_name' => 'cashflow',
        'sort_direction' => 'desc',
    ];

    // User table
    public $topUserTableOrderBy = [
        'column_name' => 'cashflow',
        'sort_direction' => 'desc',
    ];

    // Pagination
    public $pagination = [
        'merchants' => [
            'dataPerPage' => 10,
            'numberOfPageToShowEllipsis' => 11,
            'maxPageBeforeEllipsis' => 7,
            'currentPageNumber' => 1,
            'totalPages' => 0,
            'threshold' => 5,
            'hasPages' => false,
        ],
        'users' => [
            'dataPerPage' => 10,
            'numberOfPageToShowEllipsis' => 11,
            'maxPageBeforeEllipsis' => 7,
            'currentPageNumber' => 1,
            'totalPages' => 0,
            'threshold' => 5,
            'hasPages' => false,
        ]
    ];

    // enum: 'by_country' || 'by_state' || 'by_city'
    public $topTransactionByLocation = 'by_country', $topTransactionByType = [
        'cash_in' => 0,
        'cash_out' => 0,
        'transfer' => 0,
        'bill_payment' => 0,
        'invoice_payment' => 0,
        'order_payment' => 0,
        'payroll' => 0
    ];

    public $poolOfFunds = 0;
    public $overallCashInflowSum = 0;
    public $overallCashOutflowSum = 0;
    public $overallIncomeSum = 0;

    public $merchantsAggregatedFunds = 0;
    public $usersAggregatedFunds = 0;

    public string $dateLabel = 'month';

    public $aggregatedFundsChart = [
        'merchant' => 0,
        'user' => 0
    ];

    public $chartData = [
        'cashInflow' => [],
        'cashOutflow' => [],
        'income' => [],
    ];


    public $fullscreenChartModal = [
        'isVisible' => false,
        'chartTransactionType' => ''
    ];

    protected $listeners = ['closeEntityDetailsModal'];

    public function mount()
    {
        $this->updateCharts();

        $this->updateAggregatedFundsChart();

        $this->updateTopList();
    }

    public function closeEntityDetailsModal()
    {
        $this->topEntityDetailsModal = [
            'visible' => false,
            'data' => [],
            'type' => '',
        ];
    }

    #[Computed(persist: true)]
    public function get_pool_of_funds()
    {
        $inbound_sum = Transaction::whereHas('type', function ($q) {
            $q->where('code', 'CI');
        })
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->sum('amount');

        $outbound_sum = Transaction::whereHas('type', function ($q) {
            $q->whereIn('code', ['CO', 'BP']);
        })
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->sum('amount');


        return $inbound_sum - $outbound_sum;
    }

    #[Computed(persist: true)]
    public function get_specified_pool_of_funds()
    {
        $transactions_sum = TransactionProvider::whereNot('code', 'RPY')
            ->withSum('transactions_made', 'amount')
            ->withSum('transactions_outflow', 'amount')
            ->get();

        $providers = [];

        foreach ($transactions_sum as $transaction) {
            $providers[] = [
                'name' => $transaction->name,
                'cashflow' => $transaction->transactions_made_sum_amount - $transaction->transactions_outflow_sum_amount
            ];
        }

        return $providers;
    }

    public function handleChartFullscreen($param)
    {
        $this->fullscreenChartModal['isVisible'] = true;
        $this->fullscreenChartModal['chartTransactionType'] = $param['chartTransactionType'];
    }

    public function updateCharts()
    {
        $date = $this->get_date_range();
        // cash inflow chart data insertion 
        $cash_inflow_chart_data = $this->updateCashInflowChart();
        $this->overallCashInflowSum = Transaction::whereHas('type', function ($q) {
            $q->where('code', 'CI');
        })
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->whereBetween('created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->sum('amount');
        $this->dispatch('update-cash-inflow-chart', $cash_inflow_chart_data);

        // cash outflow chart data insertion
        $cash_outflow_chart_data = $this->updateCashOutflowChart();
        $this->overallCashOutflowSum = Transaction::whereHas('type', function ($q) {
            $q->where('code', 'CO');
        })
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->whereBetween('created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->sum('amount');
        $this->dispatch('update-cash-outflow-chart', $cash_outflow_chart_data);

        // income chart data insertion
        $income_chart_data = $this->updateIncomeChart();
        $this->overallIncomeSum = Transaction::whereBetween('created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->sum('service_fee');
        $this->dispatch('update-income-chart', $income_chart_data);

        $top_transaction_by_type = $this->updateTopTransactionByTypeChart();
        $this->dispatch('update-top-transaction-chart', $top_transaction_by_type);
    }

    public function cashInflowChartFullscreen()
    {
        $cash_inflow_chart_data = $this->updateCashInflowChart();

        $this->dispatch('cash-inflow-chart-fullscreen', $cash_inflow_chart_data);
        $this->fullscreenChartModal['isVisible'] = true;
        $this->fullscreenChartModal['chartTransactionType'] = 'cashInflow';
    }

    public function updateCashInflowChart()
    {
        $date = $this->get_chart_date_range();
        $format = $this->get_date_format();
        $labels = $this->get_date_labels();

        $infos = [];

        foreach ($date[$this->dateFilter] as $range) {
            $cash_inflow_sum = Transaction::whereHas('type', function ($q) {
                return $q->where('code', 'CI');
            })
                ->whereHas('status', function ($q) {
                    $q->where('slug', 'successful');
                })
                ->whereBetween('created_at', [$range[0], $range[1]])
                ->sum('amount');
            $infos[] = $cash_inflow_sum;
        }

        $infos = array_combine($labels, array_values($infos));

        return [
            'labels' => $labels,
            'infos' => $infos,
            'data_label' => 'Cash Inflow',
        ];
    }

    public function updateCashOutflowChart()
    {
        $date = $this->get_chart_date_range();
        $format = $this->get_date_format();
        $labels = $this->get_date_labels();

        $infos = [];

        foreach ($date[$this->dateFilter] as $range) {
            $cash_outflow_sum = Transaction::whereHas('type', function ($q) {
                $q->where('code', 'CO');
            })
                ->whereHas('status', function ($q) {
                    $q->where('slug', 'successful');
                })
                ->whereBetween('created_at', [$range[0], $range[1]])
                ->sum('amount');

            $infos[] = $cash_outflow_sum;
        }

        $infos = array_combine($labels, array_values($infos));

        return [
            'labels' => $labels,
            'infos' => $infos,
            'data_label' => 'Cash Outflow',
        ];
    }

    public function updateIncomeChart()
    {
        $date = $this->get_chart_date_range();
        $format = $this->get_date_format();
        $labels = $this->get_date_labels();

        $infos = [];

        foreach ($date[$this->dateFilter] as $range) {
            // dd($range[0], $range[1]);
            $income_sum = Transaction::whereBetween('created_at', [$range[0], $range[1]])
                ->whereHas('status', function ($q) {
                    $q->where('slug', 'successful');
                })
                ->sum('service_fee');
            $infos[] = $income_sum;
        }

        $infos = array_combine($labels, array_values($infos));

        return [
            'labels' => $labels,
            'infos' => $infos,
            'data_label' => 'Income',
        ];
    }

    public function updateTopTransactionByTypeChart()
    {
        $date = $this->get_date_range();

        $topTransactionByType = [];

        $topTransactionByType['cash_in'] = $this->overallCashInflowSum;

        $topTransactionByType['cash_out'] = $this->overallCashOutflowSum;

        $topTransactionByType['transfer'] = Transaction::whereHas('type', function ($q) {
            $q->where('code', 'TR');
        })
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->whereBetween('created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->sum('amount');

        $topTransactionByType['bill_payment'] = Transaction::whereHas('type', function ($q) {
            $q->where('code', 'BP');
        })
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->whereBetween('created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->sum('amount');

        $topTransactionByType['invoice_payment'] = Transaction::whereHas('type', function ($q) {
            $q->where('code', 'IV');
        })
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->whereBetween('created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->sum('amount');

        $topTransactionByType['order_payment'] = Transaction::whereHas('type', function ($q) {
            $q->where('code', 'OR');
        })
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->whereBetween('created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->sum('amount');

        $topTransactionByType['payroll'] = Transaction::whereHas('type', function ($q) {
            $q->whereIn('code', ['PS', 'PG']);
        })
            ->whereHas('status', function ($q) {
                $q->where('slug', 'successful');
            })
            ->whereBetween('created_at', [$date['present'][$this->dateFilter][0], $date['present'][$this->dateFilter][1]])
            ->sum('amount');

        return [
            'labels' => array_keys($topTransactionByType),
            'infos' => array_values($topTransactionByType),
            'data_label' => 'Transaction by Type'
        ];
    }

    #[Computed(persist: true)]
    public function getUserAggregatedFunds()
    {
        $users = User::select('id')->with(['latest_balance', 'profile'])->get()->toArray();

        $usersAggregatedFunds = 0;
        foreach ($users as $user) {
            $usersAggregatedFunds += $user["latest_balance"]["amount"] ?? 0;
        }

        return $usersAggregatedFunds;
    }

    #[Computed(persist: true)]
    public function getMerchantAggregatedFunds()
    {
        $merchants = Merchant::select('id')->with(['latest_balance'])->get()->toArray();

        $merchantsAggregatedFunds = 0;
        foreach ($merchants as $merchant) {
            $merchantsAggregatedFunds += $merchant["latest_balance"]["amount"] ?? 0;
        }

        return $merchantsAggregatedFunds;
    }

    public function updateAggregatedFundsChart()
    {
        $usersAggregatedFunds = $this->getUserAggregatedFunds();

        $merchantsAggregatedFunds = $this->getMerchantAggregatedFunds();

        $this->dispatch('update-agrregated-funds-chart', ['usersAggregatedFunds' => $usersAggregatedFunds, 'merchantsAggregatedFunds' => $merchantsAggregatedFunds]);
    }

    #[Computed]
    public function earliest_transaction()
    {
        return Transaction::orderBy('created_at', 'asc')->first();
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
    #[Computed(persist: true)]
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
    public function updatedDateFilter()
    {
        if (!in_array($this->dateFilter, ['none', 'past_24_hours', 'past_week', 'past_30_days', 'past_6_months', 'past_year'])) {
            $this->dateFilter = 'past_year';
        }

        $this->resetPagination();

        $this->updateCharts();
    }
    public function resetPagination()
    {
        $this->pagination['merchants']['currentPageNumber'] = 1;
        $this->pagination['users']['currentPageNumber'] = 1;
    }

    #[Computed]
    public function successful_status()
    {
        return TransactionStatus::where('slug', 'successful')->first()->id;
    }

    #[Computed]
    public function updateTopList()
    {
        $topList = collect();
        $date = $this->get_date_range();

        $fromDate = $date['present'][$this->dateFilter][0];
        $toDate = $date['present'][$this->dateFilter][1];

        switch ($this->topTableActiveTab) {
            case ('merchants'):
                $topMerchants = Merchant::select(
                    '*',
                    DB::raw('COALESCE((SELECT SUM(amount) from transactions WHERE transactions.recipient_id = merchants.id AND transactions.recipient_type = \'App\\\Models\\\Merchant\' AND transactions.transaction_status_id = \'' . $this->successful_status . '\' AND transactions.created_at BETWEEN \'' . $fromDate . '\' AND \'' . $toDate . '\'), 0) - COALESCE((SELECT SUM(amount) from transactions WHERE transactions.sender_id = merchants.id AND transactions.sender_type = \'App\\\Models\\\Merchant\' AND transactions.transaction_status_id = \'' . $this->successful_status . '\' AND transactions.created_at BETWEEN \'' . $fromDate . '\' AND \'' . $toDate . '\'), 0) as cashflow'),
                    DB::raw('COALESCE((SELECT SUM(service_fee) from transactions WHERE transactions.sender_id = merchants.id AND transactions.sender_type = \'App\\\Models\\\Merchant\' AND transactions.transaction_status_id = \'' . $this->successful_status . '\' AND transactions.created_at BETWEEN \'' . $fromDate . '\' AND \'' . $toDate . '\'), 0) as incomeFromThisEntity')
                )
                    ->leftjoin('merchant_details', 'merchant_details.merchant_id', '=', 'merchants.id')
                    ->orderBy($this->topMerchantTableOrderBy['column_name'], $this->topMerchantTableOrderBy['sort_direction'])
                    ->paginate($this->pagination['merchants']['dataPerPage'], ['*'], 'page', $this->pagination['merchants']['currentPageNumber']);

                $topList = $topMerchants;

                $this->pagination['merchants']['hasPages'] = $topMerchants->hasPages();

                $this->pagination['merchants']['totalPages'] = $topMerchants->lastPage();

                break;
            case ('users'):
                $topUsers = User::select(
                    '*',
                    DB::raw('COALESCE((SELECT SUM(amount) from transactions WHERE transactions.recipient_id = users.id AND transactions.recipient_type = \'App\\\Models\\\User\' AND transactions.transaction_status_id = \'' . $this->successful_status . '\' AND transactions.created_at BETWEEN \'' . $fromDate . '\' AND \'' . $toDate . '\'), 0) - COALESCE((SELECT SUM(amount) from transactions WHERE transactions.sender_id = users.id AND transactions.sender_type = \'App\\\Models\\\User\' AND transactions.transaction_status_id = \'' . $this->successful_status . '\' AND transactions.created_at BETWEEN \'' . $fromDate . '\' AND \'' . $toDate . '\'), 0) as cashflow'),
                    DB::raw('COALESCE((SELECT SUM(service_fee) from transactions WHERE transactions.sender_id = users.id AND transactions.sender_type = \'App\\\Models\\\User\' AND transactions.transaction_status_id = \'' . $this->successful_status . '\'AND transactions.created_at BETWEEN \'' . $fromDate . '\' AND \'' . $toDate . '\'), 0) as incomeFromThisEntity')
                )
                    ->with('profile:id,user_id,first_name,surname,status')
                    ->orderBy($this->topUserTableOrderBy['column_name'], $this->topUserTableOrderBy['sort_direction'])
                    ->paginate($this->pagination['users']['dataPerPage'], ['*'], 'page', $this->pagination['users']['currentPageNumber']);

                $topList = $topUsers;

                $this->pagination['users']['hasPages'] = $topUsers->hasPages();

                $this->pagination['users']['totalPages'] = $topUsers->lastPage();

                break;
            default:
                break;
        }

        return $topList;
    }
    public function handleTopUserTabClick($val)
    {
        $this->topTableActiveTab = $val;
    }
    public function handleTableRowClick($entity_id)
    {

        $entity = null;

        $availableUserType = ['merchants', 'users'];

        if (!in_array($this->topTableActiveTab, $availableUserType)) {
            return;
        }

        $type = '';

        if ($this->topTableActiveTab === 'merchants') {
            $merchantExists = Merchant::where('id', $entity_id)->exists();
            if ($merchantExists) {
                $entity = $entity_id;
                $type = 'merchant';
            }
        } elseif ($this->topTableActiveTab === 'users') {
            $userExists = User::where('id', $entity_id)->exists();
            if ($userExists) {
                $entity = $entity_id;
                $type = 'user';
            }
        }


        if (!is_null($entity)) {
            $this->topEntityDetailsModal['visible'] = true;
            $this->topEntityDetailsModal['entity_id'] = $entity;
            $this->topEntityDetailsModal['type'] = $type;
        }
    }
    public function handleArrowClick($direction)
    {

        $currentSlideNumber = $this->slide;

        if ($direction === 'left') {
            if ($currentSlideNumber !== 1) {
                $this->slide = $this->slide - 1;
            }
        }

        if ($direction === 'right') {
            if ($currentSlideNumber !== $this->maxSlideNum) {
                $this->slide = $this->slide + 1;
            }
        }
    }
    public function handleTopTransactionTabClick($val)
    {
        $this->topTransactionByLocation = $val;
    }
    public function handleDetailsModal($boolVal)
    {
        $this->modalDetails['visible'] = $boolVal;
        $this->modalDetails['data'] = [];
        $this->modalDetails['type'] = '';
    }
    public function sortTable($fieldName)
    {
        $topTableActiveTab = $this->topTableActiveTab;

        if ($topTableActiveTab === 'merchants') {
            $this->topMerchantTableOrderBy['column_name'] = $fieldName;
            $this->topMerchantTableOrderBy['sort_direction'] = $this->topMerchantTableOrderBy['sort_direction'] === 'desc' ? 'asc' : 'desc';
        } else if ($topTableActiveTab === 'users') {
            $this->topUserTableOrderBy['column_name'] = $fieldName;
            $this->topUserTableOrderBy['sort_direction'] = $this->topUserTableOrderBy['sort_direction'] === 'desc' ? 'asc' : 'desc';
        }
    }
    public function handlePageNumberClick($value)
    {
        $topTableActiveTab = $this->topTableActiveTab;
        $this->pagination[$topTableActiveTab]['currentPageNumber'] = $value;
    }
    public function handlePageArrow($direction)
    {
        $topTableActiveTab = $this->topTableActiveTab;
        if ($direction === 'left') {
            if ($this->pagination[$topTableActiveTab]['currentPageNumber'] !== 1) {
                $this->pagination[$topTableActiveTab]['currentPageNumber'] = $this->pagination[$topTableActiveTab]['currentPageNumber'] - 1;
            }
        } else if ($direction === 'right') {
            if ($this->pagination[$topTableActiveTab]['currentPageNumber'] !== $this->pagination[$topTableActiveTab]['totalPages']) {
                $this->pagination[$topTableActiveTab]['currentPageNumber'] = $this->pagination[$topTableActiveTab]['currentPageNumber'] + 1;
            }
        }
    }
    #[Layout('layouts.admin')]
    public function render()
    {
        return view('admin.dashboard.admin-dashboard');
    }
}
