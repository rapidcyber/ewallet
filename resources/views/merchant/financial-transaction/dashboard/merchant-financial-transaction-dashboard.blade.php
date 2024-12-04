<x-main.content x-data="data">
    <x-main.action-header>
        <x-slot:title>Dashboard</x-slot:title>
        <x-slot:actions>
            <div class="flex flex-row items-center gap-[10px]">
                <p>Sort by:</p>
                <x-dropdown.select-date class="h-[32px]" wire:model.live="dateFilter">
                    <x-dropdown.select-date.option value="past_year">Past Year</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="past_30_days">Past 30 days</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="past_week">Past Week</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="day">Day</x-dropdown.select-date.option>
                </x-dropdown.select-date>
            </div>
        </x-slot:actions>
    </x-main.action-header>
    <div class="flex flex-col gap-5">
        <x-card.display-balance :balance="$this->balance_amount" />

        <div class="grid grid-cols-4 gap-3 2xl:gap-[23px]">
            <x-card.money-comparison-card title="Cashflow" :date="$dateFilter" :present="$cashflow" :past="$vsCashflow" color="white" :is_full="false" />
            <div class="flex flex-col flex-1 justify-center px-6 py-4 rounded-2xl bg-white">
                <p class="text-[19.2px]">Cash Inflow</p>
                <span class="text-3.5xl font-bold">{{ $cashInCount }}</span>
            </div>
            <div class="flex flex-col flex-1 justify-center px-6 py-4 rounded-2xl bg-white">
                <p class="text-[19.2px]">Cash Outflow</p>
                <span class="text-3.5xl font-bold">{{ $cashOutCount }}</span>
            </div>
            <div class="flex flex-col flex-1 justify-center px-6 py-4 rounded-2xl bg-white">
                <p class="text-[19.2px]">Invoices</p>
                <span class="text-3.5xl font-bold">{{ $invoicesCount }}</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-lg">
            <p class="font-bold text-[19.2px]">Cashflow</p>
            <canvas id="cashflowChart"></canvas>
        </div>

        <div class="flex xl:flex-row flex-col gap-5">
            {{-- Cash inflow --}}
            <div class="flex-1 px-4 py-3 bg-white rounded-lg">
                <div class="flex flex-row justify-between">
                    <h1 class="text-lg font-bold">Cash Inflow</h1>
                    <a  href="{{ route('merchant.financial-transactions.cash-inflow', ['merchant' => $merchant->account_number]) }}"
                        class="flex flex-row items-center gap-1">
                        <span class="font-bold text-rp-red-500">See more</span>
                        <div>
                            <svg  width="6" height="8" viewBox="0 0 6 8"
                                fill="none">
                                <path
                                    d="M5.25192 3.27391C5.84566 3.66973 5.84566 4.54219 5.25192 4.93801L2.0547 7.06949C1.39014 7.51253 0.5 7.03614 0.5 6.23744L0.5 1.97447C0.5 1.17578 1.39015 0.699387 2.0547 1.14242L5.25192 3.27391Z"
                                    fill="#FF3D8F" />
                            </svg>
                        </div>
                    </a>
                </div>
                <div>
                    <canvas id="cashInflowChart"></canvas>
                    <p class="font-bold">Top transactions</p>
                    <div class="flex flex-col">
                        @if (count($cashInflowTopTransaction) > 0)
                            @foreach ($cashInflowTopTransaction as $transaction)
                                <div class="flex flex-row justify-between py-2 border-b">
                                    <p>{{ $transaction['name'] }}</p>
                                    <span class="font-bold">₱{{ number_format($transaction['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        @else
                            <span class="py-2 text-sm text-center">No transaction</span>
                        @endif
                    </div>
                </div>
            </div>
            {{-- Cash Outflow --}}
            <div class="flex-1 px-4 py-3 bg-white rounded-lg">
                <div class="flex flex-row justify-between">
                    <h1 class="text-lg font-bold">Cash Outflow</h1>
                    <a  href="{{ route('merchant.financial-transactions.cash-outflow.index', ['merchant' => $merchant->account_number]) }}"
                        class="flex flex-row items-center gap-1">
                        <span class="font-bold text-rp-red-500">See more</span>
                        <div>
                            <svg  width="6" height="8" viewBox="0 0 6 8"
                                fill="none">
                                <path
                                    d="M5.25192 3.27391C5.84566 3.66973 5.84566 4.54219 5.25192 4.93801L2.0547 7.06949C1.39014 7.51253 0.5 7.03614 0.5 6.23744L0.5 1.97447C0.5 1.17578 1.39015 0.699387 2.0547 1.14242L5.25192 3.27391Z"
                                    fill="#FF3D8F" />
                            </svg>
                        </div>
                    </a>
                </div>
                <div>
                    <canvas id="cashOutflowChart"></canvas>
                    <p class="font-bold">Top transactions</p>
                    <div class="flex flex-col">
                        @if (count($cashOutflowTopTransaction) > 0)
                            @foreach ($cashOutflowTopTransaction as $transaction)
                                <div class="flex flex-row justify-between py-2 border-b">
                                    <p>{{ $transaction['name'] }}</p>
                                    <span class="font-bold">₱{{ number_format($transaction['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        @else
                            <span class="py-2 text-sm text-center">No transaction</span>
                        @endif
                    </div>
                </div>
            </div>
            {{-- Invoices --}}
            <div class="flex-1 px-4 py-3 bg-white rounded-lg">
                <div class="flex flex-row justify-between">
                    <h1 class="text-lg font-bold">Invoices</h1>
                    <a  href="{{ route('merchant.financial-transactions.invoices.index', ['merchant' => $merchant->account_number]) }}"
                        class="flex flex-row items-center gap-1">
                        <span class="font-bold text-rp-red-500">See more</span>
                        <div>
                            <svg  width="6" height="8" viewBox="0 0 6 8"
                                fill="none">
                                <path
                                    d="M5.25192 3.27391C5.84566 3.66973 5.84566 4.54219 5.25192 4.93801L2.0547 7.06949C1.39014 7.51253 0.5 7.03614 0.5 6.23744L0.5 1.97447C0.5 1.17578 1.39015 0.699387 2.0547 1.14242L5.25192 3.27391Z"
                                    fill="#FF3D8F" />
                            </svg>
                        </div>
                    </a>
                </div>
                <div>
                    <canvas id="invoiceChart"></canvas>
                    <p class="font-bold">Top transactions</p>
                    <div class="flex flex-col">
                        @if (count($invoicesTopTransaction) > 0)
                            @foreach ($invoicesTopTransaction as $invoiceTransaction)
                                <div class="flex flex-row justify-between py-2 border-b">
                                    <p>{{ $invoiceTransaction['name'] }}</p>
                                    <span
                                        class="font-bold">₱{{ number_format($invoiceTransaction['amount'], 2) }}</span>
                                </div>
                            @endforeach
                        @else
                            <span class="py-2 text-sm text-center">No transaction</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
    {{-- Loader --}}
    <x-loader.black-screen wire:loading wire:target="dateFilter">
        <x-loader.clock />
    </x-loader.black-screen>
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
                        const cashflowLabels = chartData.cashflow.map(({
                            shortMonthName,
                            year,
                            dateText
                        }) => dateText);
                        const cashflowData = chartData.cashflow.map(({
                            cashflow
                        }) => cashflow);
                        const cashFlowChartCanvasEl = document.getElementById('cashflowChart');
                        if (cashFlowChartCanvasEl?.getContext('2d')) {
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
                        const cashInflowLabels = chartData.cashInflow.map(({
                            shortMonthName,
                            year,
                            dateText
                        }) => dateText);
                        const cashInflowData = chartData.cashInflow.map(({
                            cashInflow
                        }) => cashInflow);
                        const cashInflowChartCanvas = document.getElementById('cashInflowChart');
                        if (cashInflowChartCanvas?.getContext('2d')) {
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
                        const cashOutflowLabels = chartData.cashOutflow.map(({
                            shortMonthName,
                            year,
                            dateText
                        }) => dateText);
                        const cashOutflowData = chartData.cashOutflow.map(({
                            cashOutflow
                        }) => cashOutflow);
                        const cashOutflowChartCanvas = document.getElementById('cashOutflowChart');
                        if (cashOutflowChartCanvas?.getContext('2d')) {
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
                        const invoiceChartLabels = chartData.invoice.map(({
                            shortMonthName,
                            year,
                            dateText
                        }) => dateText);
                        const invoiceChartData = chartData.invoice.map(({
                            invoice
                        }) => invoice);
                        const invoiceChartCanvas = document.getElementById('invoiceChart');

                        if (invoiceChartCanvas?.getContext('2d')) {
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
                    const cashflowLabels = this.chartData.cashflow.map(({
                        shortMonthName,
                        year,
                        dateText
                    }) => dateText);
                    const cashflowData = this.chartData.cashflow.map(({
                        cashflow
                    }) => cashflow);
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
                    const cashInflowLabels = this.chartData.cashInflow.map(({
                        shortMonthName,
                        year,
                        dateText
                    }) => dateText);
                    const cashInflowData = this.chartData.cashInflow.map(({
                        cashInflow
                    }) => cashInflow);
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
                    const cashOutflowLabels = this.chartData.cashOutflow.map(({
                        shortMonthName,
                        year,
                        dateText
                    }) => dateText);
                    const cashOutflowData = this.chartData.cashOutflow.map(({
                        cashOutflow
                    }) => cashOutflow);
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

                    // Invoice
                    const invoiceChartLabels = this.chartData.invoice.map(({
                        shortMonthName,
                        year,
                        dateText
                    }) => dateText);
                    const invoiceChartData = this.chartData.invoice.map(({
                        invoice
                    }) => invoice);
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
            };
        }
    </script>
@endpush
