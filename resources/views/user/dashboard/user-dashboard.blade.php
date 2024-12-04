<x-main.content x-data="data">
   <x-main.action-header>
        <x-slot:title>Dashboard</x-slot:title>
        <x-slot:actions>
            <div class="flex flex-row items-center gap-[10px]">
                <p>Sort by:</p>
                <x-dropdown.select-date wire:model.live='dateFilter'>
                    <x-dropdown.select-date.option value="past_year">Past Year</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="past_30_days">Past 30 days</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="past_week">Past Week</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="day">Day</x-dropdown.select-date.option>
                </x-dropdown.select-date>
            </div>
        </x-slot:actions>
   </x-main.action-header>
   <div class="flex flex-col gap-5">
        <x-card.display-balance :balance="$this->balance_amount"/>

        <div class="grid grid-cols-3 gap-3 2xl:gap-[23px]">
            <x-card.money-comparison-card title="Cashflow" :date="$dateFilter" :present="$cashflow" :past="$vsCashflow" color="white" />
            <div class="flex flex-col flex-1 justify-center px-6 py-4 rounded-2xl bg-white">
                <p class="text-[19.2px]">Cash Inflow</p>
                <span class="font-bold text-3.5xl">{{ number_format($cashInCount) }}</span>
            </div>
            <div class="flex flex-col flex-1 justify-center px-6 py-4 rounded-2xl bg-white">
                <p class="text-[19.2px]">Cash Outflow</p>
                <span class="font-bold text-3.5xl">{{ number_format($cashOutCount) }}</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-lg">
            <p class="font-bold text-[19.2px]">Cashflow</p>
            <canvas id="cashflowChart"></canvas>
        </div>
        
        <div class="flex max-w-full xl:flex-row flex-col gap-5">
            {{-- Cash inflow --}}
            <div class="w-full bg-white p-5 rounded-lg">
                <div class="flex flex-row justify-between">
                    <h1 class="font-bold text-[19.2px]">Cash Inflow</h1>
                    <a  href="{{route('user.cash-inflow')}}" class="flex flex-row gap-1 items-center">
                        <span class="text-rp-red-500 font-bold">See more</span>
                        <div>
                            <svg width="6" height="8" viewBox="0 0 6 8" fill="none">
                                <path d="M5.25192 3.27391C5.84566 3.66973 5.84566 4.54219 5.25192 4.93801L2.0547 7.06949C1.39014 7.51253 0.5 7.03614 0.5 6.23744L0.5 1.97447C0.5 1.17578 1.39015 0.699387 2.0547 1.14242L5.25192 3.27391Z" fill="#FF3D8F"/>
                            </svg>
                        </div>
                    </a>
                </div>
                <div>
                    <canvas class="my-4" id="cashInflowChart"></canvas>
                    <p class="font-bold">Top transactions</p>
                    <div class="flex flex-col">
                        @if (count($cashInflowToptransaction) > 0)
                            @foreach ($cashInflowToptransaction as $transaction)
                                <div class="flex flex-row justify-between py-2 border-b">
                                    <p>{{$transaction["name"]}}</p>
                                    <span class="font-bold">{{ \Number::currency($transaction["amount"], 'PHP') }}</span>
                                </div>
                            @endforeach
                        @else 
                            <span class="text-sm py-2 text-center">No transaction</span>
                        @endif
                    </div>
                </div>
            </div>
            {{-- Cash Outflow --}}
            <div class="w-full bg-white p-5 rounded-lg">
                <div class="flex flex-row justify-between">
                    <h1 class="font-bold text-[19.2px]">Cash Outflow</h1>
                    <a  href="{{route('user.cash-outflow.index')}}" class="flex flex-row gap-1 items-center">
                        <span class="text-rp-red-500 font-bold">See more</span>
                        <div>
                            <svg width="6" height="8" viewBox="0 0 6 8" fill="none">
                                <path d="M5.25192 3.27391C5.84566 3.66973 5.84566 4.54219 5.25192 4.93801L2.0547 7.06949C1.39014 7.51253 0.5 7.03614 0.5 6.23744L0.5 1.97447C0.5 1.17578 1.39015 0.699387 2.0547 1.14242L5.25192 3.27391Z" fill="#FF3D8F"/>
                            </svg>
                        </div>
                    </a>
                    </div>
                <div>
                    <canvas class="my-4" id="cashOutflowChart"></canvas>
                    <p class="font-bold">Top transactions</p>
                    <div class="flex flex-col">
                        @if (count($cashOutflowToptransaction) > 0)
                            @foreach ($cashOutflowToptransaction as $transaction)
                                <div class="flex flex-row justify-between py-2 border-b">
                                    <p>{{$transaction["name"]}}</p>
                                    <span class="font-bold">{{ \Number::currency($transaction["amount"], 'PHP') }}</span>
                                </div>
                            @endforeach
                        @else
                            <span class="text-sm py-2 text-center">No transaction</span>
                        @endif
                    </div>
                </div>
            </div>
                {{-- Invoices --}}
                {{-- <div class="flex-1 bg-white px-4 py-3 rounded-lg">
                <div class="flex flex-row justify-between">
                    <h1 class="font-bold text-[19.2px]">Invoices</h1>
                    <a  href="{{route('user.invoices')}}" class="flex flex-row gap-1 items-center">
                        <span class="text-rp-red-500 font-bold">See more</span>
                        <div>
                            <svg width="6" height="8" viewBox="0 0 6 8" fill="none">
                                <path d="M5.25192 3.27391C5.84566 3.66973 5.84566 4.54219 5.25192 4.93801L2.0547 7.06949C1.39014 7.51253 0.5 7.03614 0.5 6.23744L0.5 1.97447C0.5 1.17578 1.39015 0.699387 2.0547 1.14242L5.25192 3.27391Z" fill="#FF3D8F"/>
                            </svg>
                        </div>
                    </a>
                </div>
                <div>
                    <canvas id="invoiceChart"></canvas>
                    <p class="font-bold">Top transactions</p>
                    <div class="flex flex-col">
                        @if (count($invoicesToptransaction) > 0)
                            @foreach ($invoicesToptransaction as $invoiceTransaction)
                                <div class="flex flex-row justify-between py-2 border-b">
                                    <p>{{$invoiceTransaction["name"]}}</p>
                                    <span class="font-bold">₱{{number_format($invoiceTransaction["amount"], 2)}}</span>
                                </div>
                            @endforeach 
                        @else
                            <span class="text-sm py-2 text-center">No transaction</span>
                        @endif
                    </div>
                </div> --}}
            </div>
        </div>

        {{-- Toast Notification --}}
        @if (session()->has('success'))
            <x-toasts.success />
        @endif

        @if (session()->has('error'))
            <x-toasts.error />
        @endif

        @if (session()->has('warning'))
            <x-toasts.warning />
        @endif
        
        <x-loader.black-screen wire:loading wire:target='dateFilter'>
            <x-loader.clock />
        </x-loader.black-screen>
    </div>
</x-main.content>


@push('scripts')
    <script>
        function data() {
            return {
                chartData: @entangle('chartData'),
                cashflowChartCanvas: null,
                cashInflowChartCanvas: null,
                cashOutflowChartCanvas: null,
                invoiceChartCanvas: null,
                init() {
                    this.chartDataSetup();
                },
                chartDataSetup() {
                    this.$watch('chartData', chartData => {
                        // Cashflow
                        const cashflowLabels = chartData.cashflow.map(({shortMonthName, year, dateText}) => dateText);
                        const cashflowData = chartData.cashflow.map(({cashflow}) => cashflow);
                        const cashFlowChartCanvasEl = document.getElementById('cashflowChart');
                        if(cashFlowChartCanvasEl?.getContext('2d')) {   
                            this.cashflowChartCanvas.destroy();
                            this.cashflowChartCanvas = new Chart(document.getElementById('cashflowChart'), {
                                type: 'line',
                                data: {
                                    labels: cashflowLabels,
                                    datasets: [{
                                        label: 'Cashflow',
                                        data: cashflowData,
                                        borderWidth: 1,
                                        borderColor: '#FF3D8F',
                                        backgroundColor: '#FF3D8F'
                                    }]
                                },
                                options: {
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    },
                                    animation: {
                                        duration: 0 // general animation time
                                    }
                                }
                            });   
                        }

                        // CashInflow
                        const cashInflowLabels = chartData.cashInflow.map(({shortMonthName, year, dateText}) => dateText);
                        const cashInflowData = chartData.cashInflow.map(({cashInflow}) => cashInflow);
                        const cashInflowChartCanvas = document.getElementById('cashInflowChart');
                        if(cashInflowChartCanvas?.getContext('2d')) {
                            this.cashInflowChartCanvas.destroy();
                            this.cashInflowChartCanvas = new Chart(document.getElementById('cashInflowChart'), {
                                type: 'line',
                                data: {
                                    labels: cashInflowLabels,
                                    datasets: [{
                                        label: 'Cash Inflow',
                                        data: cashInflowData,
                                        borderWidth: 1,
                                        borderColor: '#FF3D8F',
                                        backgroundColor: '#FF3D8F'
                                    }]
                                },
                                options: {
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    },
                                    animation: {
                                        duration: 0 // general animation time
                                    }
                                }
                            });
                        }

                        // CashOutflow
                        const cashOutflowLabels = chartData.cashOutflow.map(({shortMonthName, year, dateText}) => dateText);
                        const cashOutflowData = chartData.cashOutflow.map(({cashOutflow}) => cashOutflow);
                        const cashOutflowChartCanvas = document.getElementById('cashOutflowChart');
                        if(cashOutflowChartCanvas?.getContext('2d')) {
                            this.cashOutflowChartCanvas.destroy();
                            this.cashOutflowChartCanvas = new Chart(document.getElementById('cashOutflowChart'), {
                                type: 'line',
                                data: {
                                    labels: cashOutflowLabels,
                                    datasets: [{
                                        label: 'Cash Outflow',
                                        data: cashOutflowData,
                                        borderWidth: 1,
                                        borderColor: '#FF3D8F',
                                        backgroundColor: '#FF3D8F'
                                    }]
                                },
                                options: {
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    },
                                    animation: {
                                        duration: 0 // general animation time
                                    }
                                }
                            });
                        }

                        // Invoices
                        const invoiceChartLabels = chartData.invoice.map(({shortMonthName, year, dateText}) => dateText);
                        const invoiceChartData = chartData.invoice.map(({invoice}) => invoice);
                        const invoiceChartCanvas = document.getElementById('invoiceChart');

                        if(invoiceChartCanvas?.getContext('2d')) {
                            this.invoiceChartCanvas.destroy();
                            this.invoiceChartCanvas = new Chart(document.getElementById('invoiceChart'), {
                                type: 'line',
                                data: {
                                    labels: invoiceChartLabels,
                                    datasets: [{
                                        label: 'Invoice',
                                        data: invoiceChartData,
                                        borderWidth: 1,
                                        borderColor: '#FF3D8F',
                                        backgroundColor: '#FF3D8F'
                                    }]
                                },
                                options: {
                                    scales: {
                                        y: {
                                            beginAtZero: true
                                        }
                                    },
                                    animation: {
                                        duration: 0 // general animation time
                                    }
                                }
                            }); 
                        }
                    });
                    
                    // Cashflow
                    const cashflowLabels = this.chartData.cashflow.map(({shortMonthName, year, dateText}) => dateText);
                    const cashflowData = this.chartData.cashflow.map(({cashflow}) => cashflow);
                    this.cashflowChartCanvas = new Chart(document.getElementById('cashflowChart'), {
                        type: 'line',
                        data: {
                            labels: cashflowLabels, 
                            datasets: [{
                                label: 'Cashflow',
                                data: cashflowData,
                                borderWidth: 1,
                                borderColor: '#FF3D8F',
                                backgroundColor: '#FF3D8F'
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            animation: {
                                duration: 0 // general animation time
                            }
                        }
                    });

                    // CashInflow
                    const cashInflowLabels = this.chartData.cashInflow.map(({shortMonthName, year, dateText}) => dateText);
                    const cashInflowData = this.chartData.cashInflow.map(({cashInflow}) => cashInflow);
                    this.cashInflowChartCanvas = new Chart(document.getElementById('cashInflowChart'), {
                        type: 'line',
                        data: {
                            labels: cashInflowLabels,
                            datasets: [{
                                label: 'Cash Inflow',
                                data: cashInflowData,
                                borderWidth: 1,
                                borderColor: '#FF3D8F',
                                backgroundColor: '#FF3D8F'
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            animation: {
                                duration: 0 // general animation time
                            }
                        }
                    });

                    // CashOutflow
                    const cashOutflowLabels = this.chartData.cashOutflow.map(({shortMonthName, year, dateText}) => dateText);
                    const cashOutflowData = this.chartData.cashOutflow.map(({cashOutflow}) => cashOutflow);
                    this.cashOutflowChartCanvas = new Chart(document.getElementById('cashOutflowChart'), {
                        type: 'line',
                        data: {
                            labels: cashOutflowLabels,
                            datasets: [{
                                label: 'Cash Outflow',
                                data: cashOutflowData,
                                borderWidth: 1,
                                borderColor: '#FF3D8F',
                                backgroundColor: '#FF3D8F'
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            animation: {
                                duration: 0 // general animation time
                            }
                        }
                    });
                }
            };
        }
    </script>
@endpush