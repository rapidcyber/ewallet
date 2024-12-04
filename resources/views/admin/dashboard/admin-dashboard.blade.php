<div x-data="{ 
        handleChartFullscreen({ transactionType }) {
            @this.handleChartFullscreen({ chartTransactionType: transactionType });
        } 
    }">
    <div class="w-full 2xl:px-16 px-12 py-10">
        {{-- Dashboard Main Content --}}
        <div class="flex flex-col gap-5">
            <div class="flex flex-row gap-5 w-full">
                <div class="flex flex-col w-[42%] gap-5">
                    {{-- Title and Filter --}}
                    <div class="flex flex-row justify-between gap-4 items-center">
                        <x-main.title>Dashboard</x-main.title>
                        <div class="flex flex-row items-center gap-1">
                            <p class="text-sm">Sort by:</p>
                            <x-dropdown.select-date wire:model.live="dateFilter">
                                <x-dropdown.select-date.option value="none">All transactions</x-dropdown.select-date.option>
                                <x-dropdown.select-date.option value="past_year">Past Year</x-dropdown.select-date.option>
                                <x-dropdown.select-date.option value="past_6_months">Past 6 months</x-dropdown.select-date.option>
                                <x-dropdown.select-date.option value="past_30_days">Past 30 days</x-dropdown.select-date.option>
                                <x-dropdown.select-date.option value="past_week">Past Week</x-dropdown.select-date.option>
                                <x-dropdown.select-date.option value="past_24_hours">Past 24 Hours</x-dropdown.select-date.option>
                            </x-dropdown.select-date>
                        </div>
                    </div>
                    {{-- Pool of Funds --}}
                    <div
                        class="bg-purple-gradient-to-right flex justify-center text-white shadow-lg z-10 flex-1 flex-col px-6 bg-white text-lg rounded-xl">
                        <p class="text-2xl ">Pool of Funds</p>   
                        <h1 class="text-5xl font-bold">{{ \Number::currency($this->get_pool_of_funds, 'PHP') }}</h1>
                    </div>
                </div>
                {{-- Cash Inflow Chart --}}
                <div class="rounded-xl bg-white px-6 py-5 flex-1 relative">
                    {{-- Fullscreen Button --}}
                    <button class="cursor-pointer absolute top-5 right-4"
                        @click="handleChartFullscreen({ transactionType: 'cashInflow' });">
                        <x-icon.fullscreen />
                    </button>
                    <p>Overall Cash Inflow Transactions</p>
                    <h1 class="text-2xl font-bold">{{ \Number::currency($overallCashInflowSum, 'PHP') }}</h1>
                    <canvas id="overallCashInflowTransactions" wire:ignore class="w-full"></canvas>
                </div>
                {{-- Cash Outflow Chart --}}
                <div class="rounded-xl bg-white px-6 py-5 flex-1 relative">
                    {{-- Fullscreen Button --}}
                    <button class="cursor-pointer absolute top-5 right-4"
                        @click="handleChartFullscreen({ transactionType: 'cashOutflow' });">
                        <x-icon.fullscreen />
                    </button>
                    <p>Overall Cash Outflow Transactions</p>
                    <h1 class="text-2xl font-bold">{{ \Number::currency($overallCashOutflowSum, 'PHP') }} </h1>
                    <canvas id="overallCashOutflowTransactions" wire:ignore class="w-full"></canvas>
                </div>
            </div>
            <div class="w-full flex flex-row gap-5">
                <div class="w-1/3 space-y-5">
                    <div class="rounded-xl bg-white px-5 py-4 relative w-full">
                        {{-- Fullscreen Button --}}
                        <button class="cursor-pointer absolute top-5 right-4"
                            @click="handleChartFullscreen({ transactionType: 'income' })">
                            <x-icon.fullscreen />
                        </button>
                        <p>Overall Income from transactions</p>
                        <h1 class="text-xl font-bold">{{ \Number::currency($overallIncomeSum, 'PHP') }}</h1>
                        <canvas id="overallIncomeTransactions" wire:ignore class="mt-3 w-full"></canvas>
                    </div>
                    <div class="rounded-xl bg-white px-5 py-4 w-full">
                        <p>Aggregated Funds</p>
                        <canvas id="aggregatedFundsChart" wire:ignore class="mt-3 w-full"></canvas>
                        <div class="flex flex-row justify-between items-center w-full">
                            <div class="w-1/2 flex flex-row gap-1">
                                <div class="w-7 h-7 min-w-7 min-h-7">
                                    <div class="w-full h-full bg-[#F70068] rounded-full"></div>
                                </div>
                                <div class="w-[calc(100%-28px)]">
                                    <p>Merchants</p>
                                    <p class="font-semibold truncate">{{ \Number::currency($this->getMerchantAggregatedFunds, 'PHP') }}</p>
                                </div>
                            </div>
                            {{-- <div class="h-7 w-[1px] bg-slate-400 mx-2"></div> --}}
                            <div class="w-1/2 flex flex-row gap-1">
                                <div class="w-7 h-7 min-w-7 min-h-7">
                                    <div class="w-full h-full bg-rp-red-300 rounded-full"></div>
                                </div>
                                <div class="w-[calc(100%-28px)]">
                                    <p>Users</p>
                                    <p class="font-semibold truncate">{{ \Number::currency($this->getUserAggregatedFunds, 'PHP') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex-1 w-2/3 rounded-xl bg-white px-5 py-4 overflow-auto">
                    <div class="flex flex-row justify-between">
                        <p class="w-full">
                            @switch($this->slide)
                                @case(1)
                                    @if ($this->topTableActiveTab === 'merchants')
                                        Top Merchants
                                    @elseif ($this->topTableActiveTab === 'users')
                                        Top Users
                                    @endif
                                @break

                                @case(2)
                                    Top Transactions by Type
                                @break

                                @default
                            @endswitch
                        </p>
                        <div class="flex flex-row gap-6">
                            <button {{ $this->slide === 1 ? 'disabled' : '' }}  class="{{ $this->slide === 1 ? 'opacity-50' : 'cursor-pointer' }}" wire:click="handleArrowClick('left')">
                                <x-icon.solid-arrow-left/>
                            </button>
                            <button {{ $this->slide === $this->maxSlideNum ? 'disabled' : '' }} class="{{ $this->slide === $this->maxSlideNum ? 'opacity-50' : 'cursor-pointer' }}" wire:click="handleArrowClick('right')">
                                <x-icon.solid-arrow-right />
                            </button>
                        </div>

                    </div>

                    <div x-cloak x-show="$wire.slide === 1">
                        <div class="flex flex-row mt-4">
                            <!-- Merchant Tab -->
                            <button wire:click="handleTopUserTabClick('merchants')"
                                class="cursor-pointer w-40 text-center py-2 {{ $this->topTableActiveTab === 'merchants' ? 'text-primary-600 font-bold border-b border-b-primary-600' : '' }}">
                                <p>Merchants</p>
                            </button>
    
                            <!-- Users Tab -->
                            <button wire:click="handleTopUserTabClick('users')"
                                class="cursor-pointer w-40 text-center py-2 {{ $this->topTableActiveTab === 'users' ? 'text-primary-600 font-bold border-b border-b-purple-600' : '' }}">
                                <p>Users</p>
                            </button>   
                        </div>  
    
                        <!-- Table Container -->
                        <div class="w-full overflow-auto mt-4">
                            <table class="w-full text-sm table-auto">
                                <!-- Table Header -->
                                <thead class="bg-white">
                                    <tr class="text-left mb-7 border-b border-rp-neutral-300">
                                        <th class="px-3 py-4">Name</th>
                                        <th class="text-left pr-3 py-4">Country</th>
                                        <th class="text-left pr-3 py-4">
                                            <div class="flex flex-row items-center">
                                                <span>Cashflow</span>
                                                <button class="ml-2" wire:click="sortTable('cashflow')">
                                                    <x-icon.sort />
                                                </button>
                                            </div>
                                        </th>
                                        <th class="text-left pr-3 py-4">
                                            <div class="flex flex-row items-center">
                                                <span>Income from this 
                                                    @switch($this->topTableActiveTab)
                                                        @case('merchants')
                                                            merchant
                                                            @break
                                                        @case('users')
                                                            user
                                                            @break
                                                        @default
                                                    @endswitch
                                                </span>
                                                <button class="ml-2"
                                                    wire:click="sortTable('incomeFromThisEntity')">
                                                    <x-icon.sort />
                                                </button>
                                            </div>
                                        </th>
                                        <th class="text-left pr-3 py-4">Status</th>
                                    </tr>
                                </thead>
                                <!-- Table Body -->
                                <tbody>
                                    @foreach ($this->updateTopList as $top)
                                        <tr class="even:bg-rp-neutral-50 odd:bg-white">
                                            <td class="px-3 py-4 first:rounded-l-lg last:rounded-r-lg">{{ $top->name }}</td>
                                            <td class="pr-3 py-4">{{ $top->phone_iso ?? '---' }}</td>
                                            <td class="pr-3 py-4 {{ $top->cashflow >= 0 ? 'text-rp-green-600' : 'text-rp-red-600'}} font-bold">
                                                <div class="flex items-center">
                                                    <p class="truncate">₱ {{ number_format($top->cashflow, 2) }}</p>
                                                    @if ($top->cashflow >=0)
                                                        <x-icon.solid-arrow-up fill="#149d8c" />
                                                    @else
                                                        <x-icon.solid-arrow-down fill="#f70068" />
                                                    @endif
                                                </div>
                                            </td>
    
                                            <td class="pr-3 py-4 truncate">₱ {{ number_format($top->incomeFromThisEntity, 2) }}</td>
                                            <td class="pr-3 py-4">
                                                <div class="flex flex-row items-center gap-4">
                                                    @switch($top->status)
                                                        @case('pending')
                                                            <x-status color="primary" class="w-28">Pending</x-status>
                                                            @break
                                                        @case('verified')
                                                            <x-status color="green" class="w-28">Active</x-status>
                                                            @break
                                                        @case('rejected')
                                                            <x-status color="red" class="w-28">Denied</x-status>
                                                            @break
                                                        @case('deactivated')
                                                            <x-status color="red" class="w-28">Deactivated</x-status>
                                                            @break
                                                        @default
                                                    @endswitch
                                                    <button
                                                        wire:click="handleTableRowClick({{ $top->id }})"
                                                        class="flex items-center p-2 bg-transparent border-none cursor-pointer">
                                                        <x-icon.chevron-right />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
    
                        {{-- Pagination --}}
                        @if($this->pagination[$topTableActiveTab]['hasPages'])
                            <div class="flex flex-row items-center h-10 gap-0 mt-12 w-max mx-auto border rounded-md overflow-hidden">
                                <div class="{{1 === $this->pagination[$topTableActiveTab]['currentPageNumber'] ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'}} px-4 h-full py-2  border-r bg-white flex items-center" wire:click="handlePageArrow('left')">
                                    <svg  width="7" height="13" viewBox="0 0 7 13" fill="none">
                                        <path d="M6 11.5001L1 6.50012L6 1.50012" stroke="#647887" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                @if($this->pagination[$topTableActiveTab]['totalPages'] < $this->pagination[$topTableActiveTab]['numberOfPageToShowEllipsis'])
                                    @for ($i = 1; $i <= $this->pagination[$topTableActiveTab]['totalPages']; $i++)
                                        <div class="{{$i === $this->pagination[$topTableActiveTab]['currentPageNumber'] ? 'bg-gray-200' : 'bg-white hover:bg-slate-100'}} h-full border-r px-4 py-2  cursor-pointer" wire:click="handlePageNumberClick({{$i}})">{{$i}}</div>
                                    @endfor
                                @elseif ($this->pagination[$topTableActiveTab]['totalPages'] >= $this->pagination[$topTableActiveTab]['numberOfPageToShowEllipsis'])
                                    @if ($this->pagination[$topTableActiveTab]['currentPageNumber'] > $this->pagination[$topTableActiveTab]['threshold'] && $this->pagination[$topTableActiveTab]['currentPageNumber'] < $this->pagination[$topTableActiveTab]['totalPages'] - $this->pagination[$topTableActiveTab]['threshold'] + 1)
                                        <div wire:key="{{1}}" class="{{1 === $this->pagination[$topTableActiveTab]['currentPageNumber'] ? 'bg-gray-200' : 'bg-white hover:bg-slate-100'}} h-full border-r px-4 py-2  cursor-pointer" wire:click="handlePageNumberClick(1)">
                                            1
                                        </div> 
                                        <div class="h-full border bg-white border-r px-4 py-2 cursor-not-allowed">
                                            ...
                                        </div>
                                        @for($i = $this->pagination[$topTableActiveTab]['currentPageNumber'] - 2; $i <= $this->pagination[$topTableActiveTab]['currentPageNumber'] + 2; $i++)
                                            <div wire:key="{{$i}}" class="{{$i === $this->pagination[$topTableActiveTab]['currentPageNumber'] ? 'bg-gray-200' : 'bg-white hover:bg-slate-100'}} h-full border-r px-4 py-2  cursor-pointer" wire:click="handlePageNumberClick({{$i}})">
                                                {{$i}}
                                            </div> 
                                        @endfor
                                        <div class="h-full bg-white border-r px-4 py-2 cursor-not-allowed">
                                            ...
                                        </div>
                                        <div wire:key="{{$this->pagination[$topTableActiveTab]['totalPages']}}" class="{{$this->pagination[$topTableActiveTab]['totalPages'] === $this->pagination[$topTableActiveTab]['currentPageNumber'] ? 'bg-gray-200' : 'bg-white hover:bg-slate-100'}} h-full border-r px-4 py-2  cursor-pointer" wire:click="handlePageNumberClick({{$this->pagination[$topTableActiveTab]['totalPages']}})">
                                            {{$this->pagination[$topTableActiveTab]['totalPages']}}
                                        </div>
                                    @elseif ($this->pagination[$topTableActiveTab]['currentPageNumber'] <= $this->pagination[$topTableActiveTab]['threshold'])
                                        @for ($i = 1; $i <= $this->pagination[$topTableActiveTab]['maxPageBeforeEllipsis']; $i++)
                                            <div wire:key="{{$i}}" class="{{$i === $this->pagination[$topTableActiveTab]['currentPageNumber'] ? 'bg-gray-200' : 'bg-white hover:bg-slate-100'}} h-full border-r px-4 py-2  cursor-pointer" wire:click="handlePageNumberClick({{$i}})">{{$i}}</div>
                                        @endfor
                                        <div class="h-full border-r bg-white px-4 py-2 cursor-not-allowed">
                                            ...
                                        </div>
                                        <div wire:key="{{$this->pagination[$topTableActiveTab]['totalPages']}}" class="{{$this->pagination[$topTableActiveTab]['totalPages'] === $this->pagination[$topTableActiveTab]['currentPageNumber'] ? 'bg-gray-200' : 'bg-white hover:bg-slate-100'}} h-full border-r px-4 py-2  cursor-pointer" wire:click="handlePageNumberClick({{$this->pagination[$topTableActiveTab]['totalPages']}})">
                                            {{$this->pagination[$topTableActiveTab]['totalPages']}}
                                        </div>
                                    @elseif ($this->pagination[$topTableActiveTab]['currentPageNumber'] > $this->pagination[$topTableActiveTab]['threshold']) 
                                        <div wire:key="{{1}}" class="{{1 === $this->pagination[$topTableActiveTab]['currentPageNumber'] ? 'bg-gray-200' : 'bg-white hover:bg-slate-100'}} h-full border-r px-4 py-2  cursor-pointer" wire:click="handlePageNumberClick(1)">
                                            1
                                        </div> 
                                        <div class="h-full border-r bg-white px-4 py-2 cursor-not-allowed">
                                            ...
                                        </div>
                                        @for ($i = $this->pagination[$topTableActiveTab]['totalPages'] - $this->pagination[$topTableActiveTab]['maxPageBeforeEllipsis'] + 1; $i <= $this->pagination[$topTableActiveTab]['totalPages']; $i++)
                                            <div wire:key="{{$i}}" class="{{$i === $this->pagination[$topTableActiveTab]['currentPageNumber'] ? 'bg-gray-200' : 'bg-white hover:bg-slate-100'}} h-full border-r px-4 py-2 cursor-pointer" wire:click="handlePageNumberClick({{$i}})">{{$i}}</div>
                                        @endfor
                                    @endif                      
                                @endif
    
                                <div class="{{$this->pagination[$topTableActiveTab]['totalPages'] === $this->pagination[$topTableActiveTab]['currentPageNumber'] ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'}} px-4 h-full py-2 border-r  bg-white flex items-center" wire:click="handlePageArrow('right')">
                                    <svg  width="7" height="13" viewBox="0 0 7 13" fill="none">
                                        <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        @endif

                    </div>
                    
                    <div x-cloak x-show="$wire.slide === 2">
                        <canvas id="topTransactionsByType" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fullscreen Chart Modal --}}
    <x-modal x-model="$wire.fullscreenChartModal.isVisible">
        <div class="relative bg-white w-[90vw] max-w-[850px] z-30 rounded-lg px-7 h-max py-6 shadow-md">
            {{-- Close button --}}
            <button @click="$wire.fullscreenChartModal.isVisible=false;$wire.fullscreenChartModal.chartTransactionType='';"
                class="absolute cursor-pointer top-4 right-3">
                <x-icon.close />
            </button>
            {{-- Cash Inflow Chart --}}
            <div x-cloak x-show="$wire.fullscreenChartModal.chartTransactionType === 'cashInflow'" class="w-full">
                <div class="w-full">
                    <h1>Overall Cash Inflow Transactions</h1>
                    <canvas id="overallCashInflowTransactionsFullscreen" wire:ignore class="w-full"></canvas>
                </div>
            </div>
            {{-- Cash Outflow Chart --}}
            <div x-cloak x-show="$wire.fullscreenChartModal.chartTransactionType === 'cashOutflow'">
                <div>
                    <h1>Overall Cash Outflow Transactions</h1>
                    <canvas id="overallCashOutflowTransactionsFullscreen" wire:ignore class="w-full">
                    </canvas>
                </div>
            </div>
            {{-- Income Chart --}}
            <div x-cloak x-show="$wire.fullscreenChartModal.chartTransactionType === 'income'">
                <div>
                    <h1>Overall Income from transactions</h1>
                    <canvas id="overallIncomeTransactionsFullscreen" wire:ignore class="w-full">
                    </canvas>
                </div>
            </div>
        </div>
    </x-modal>

    <x-modal x-model="$wire.topEntityDetailsModal.visible">
        @if ($this->topEntityDetailsModal['visible'] && $this->topEntityDetailsModal['entity_id'])
            <livewire:admin.components.entity-details-modal :entity_id="$this->topEntityDetailsModal['entity_id']" :entity_type="$this->topEntityDetailsModal['type']" :dateFilter="$this->dateFilter" :dateLabel="$this->dateLabel" />
        @endif
    </x-modal>

    {{-- --------------- Black Screen Overlay Loader --------------- --}}

    <x-loader.black-screen wire:loading.block wire:target="dateFilter,handleChartFullscreen,handleTableRowClick,handleArrowClick" class="z-10"/>


</div>  

@script
    <script>       

        let cashInflowChartCanvas = document.getElementById('overallCashInflowTransactions').getContext('2d');
        
        let cashInflowChartCanvasFullscreen = document.getElementById('overallCashInflowTransactionsFullscreen').getContext('2d');

        let cashInflowChart;

        let cashInflowChartFullscreen;

        $wire.on('update-cash-inflow-chart',  (chart) => {

            if(typeof cashInflowChart === 'object') {
                cashInflowChart.destroy();
            }

            if(typeof cashInflowChartFullscreen === 'object') {
                cashInflowChartFullscreen.destroy();
            }

            const chart_config = {
                type: 'line',
                data: {
                    labels: chart[0]['labels'],
                    datasets: [{
                        label: chart[0]['data_label'],
                        data: chart[0]['infos'],
                        borderWidth: 2,
                        borderColor: '#7F56D9',
                        backgroundColor: '#7F56D9',
                        tension: 0,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHitRadius: 10,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                }
            };
            
            cashInflowChart = new Chart(cashInflowChartCanvasFullscreen, chart_config);
            
            cashInflowChartFullscreen = new Chart(cashInflowChartCanvas, chart_config);
        });

        let cashOutflowChartCanvas = document.getElementById('overallCashOutflowTransactions').getContext('2d');
        
        let cashOutflowChartCanvasFullscreen = document.getElementById('overallCashOutflowTransactionsFullscreen').getContext('2d');

        let cashOutflowChart;

        let cashOutflowChartFullscreen;

        $wire.on('update-cash-outflow-chart', function (chart) {

            if(typeof cashOutflowChart === 'object') {
                cashOutflowChart.destroy();
            }

            if(typeof cashOutflowChartFullscreen === 'object') {
                cashOutflowChartFullscreen.destroy();
            }

            const chart_config = {
                type: 'line',
                data: {
                    labels: chart[0]['labels'],
                    datasets: [{
                        label: chart[0]['data_label'],
                        data: chart[0]['infos'],
                        borderWidth: 2,
                        borderColor: '#7F56D9',
                        backgroundColor: '#7F56D9',
                        tension: 0,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHitRadius: 10,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                }
            };

            cashOutflowChart = new Chart(cashOutflowChartCanvas, chart_config);

            cashOutflowChartFullscreen = new Chart(cashOutflowChartCanvasFullscreen, chart_config);
        });


        let incomeChartCanvas = document.getElementById('overallIncomeTransactions').getContext('2d');

        let incomeChartCanvasFullscreen = document.getElementById('overallIncomeTransactionsFullscreen').getContext('2d');

        let incomeChart;

        let incomeChartFullscreen;

        $wire.on('update-income-chart', function (chart) {

            if(typeof incomeChart === 'object') {
                incomeChart.destroy();
            }

            if(typeof incomeChartFullscreen === 'object') {
                incomeChartFullscreen.destroy();
            }

            const chart_config = {
                type: 'line',
                data: {
                    labels: chart[0]['labels'],
                    datasets: [{
                        label: chart[0]['data_label'],
                        data: chart[0]['infos'],
                        borderWidth: 2,
                        borderColor: '#7F56D9',
                        backgroundColor: '#7F56D9',
                        tension: 0,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        pointHitRadius: 10,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                }
            };

            incomeChart = new Chart(incomeChartCanvas, chart_config);

            incomeChartFullscreen = new Chart(incomeChartCanvasFullscreen, chart_config);
        });

        let aggregatedFundsChartCanvas = document.getElementById('aggregatedFundsChart').getContext('2d');

        let aggregatedFundsChart;

        $wire.on('update-agrregated-funds-chart', function (chart) {      
            const [{ merchantsAggregatedFunds, usersAggregatedFunds }] = chart;

            if(typeof aggregatedFundsChart === 'object') {
                aggregatedFundsChart.destroy();
            }

            const chart_config = {
                type: 'doughnut',
                data: {
                    labels: [
                        'Merchants',
                        'User',
                    ],
                    datasets: [{
                        label: '',
                        data: [merchantsAggregatedFunds,usersAggregatedFunds],
                        backgroundColor: [
                        'rgb(219, 39, 119)',
                        'rgb(249, 168, 212)',
                        ],
                        hoverOffset: 4
                    }]
                },
            };

            aggregatedFundsChart = new Chart(aggregatedFundsChartCanvas, chart_config);

        });
        
        let topTransactionsByTypeChartCanvas = document.getElementById('topTransactionsByType').getContext('2d');

        let topTransactionsByTypeChart;

        $wire.on('update-top-transaction-chart', (chart) => {

            if(typeof topTransactionsByTypeChart === 'object') {
                topTransactionsByTypeChart.destroy();
            }

            topTransactionsByTypeChart = new Chart(topTransactionsByTypeChartCanvas, {
                type: 'bar',
                data: {
                    labels: chart[0]['labels'].map(label => label.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ') ),
                    datasets: [{
                        label: chart[0]['data_label'],
                        data: chart[0]['infos'],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(255, 159, 64, 0.2)',
                            'rgba(255, 205, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(201, 203, 207, 0.2)'
                        ],
                        borderColor: [
                            'rgb(255, 99, 132)',
                            'rgb(255, 159, 64)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(54, 162, 235)',
                            'rgb(153, 102, 255)',
                            'rgb(201, 203, 207)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                },
            });
        });
        

    </script>
@endscript
