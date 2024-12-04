<div class="relative bg-white w-[90vw] max-w-[700px] z-30 rounded-2xl px-7 py-3 shadow-md max-h-[95%] overflow-y-auto">
    <h1 class="font-bold">
        <span x-text="`${$wire.entity_type.charAt(0).toUpperCase()}${$wire.entity_type.slice(1)}`"></span> Details
    </h1>
    <button @click="$dispatch('closeEntityDetailsModal')" class="absolute top-4 right-3">
        <x-icon.close />
    </button>
    <div class="flex flex-col mx-auto items-center gap-1">
        <div class="w-[140px] h-[140px] 2xl:w-[179px] 2xl:h-[179px]">
            @if ($entity_model->media->isNotEmpty())
                <img src="{{ $this->get_media_url($entity_model->media->first(), 'thumbnail') }}" alt="Profile Image" class="w-full h-full object-cover rounded-full">
            @else
                <img src="{{ url('images/user/default-avatar.png') }}" alt="Profile Image" class="w-full h-full object-cover rounded-full"> 
            @endif
        </div>
        <p class="font-bold text-2xl">
            {{ $entity_model->name }}
        </p>
        <p><span x-text="`${$wire.entity_type.charAt(0).toUpperCase()}${$wire.entity_type.slice(1)}`"></span> since: {{ $entity_model->created_at->format('Y-m-d') }}</p>
        @php
            $href = "#";
            if($entity_type === 'user') {
                $href = route('admin.manage-users.show.basic-details', $entity_id);
            } else if($entity_type === 'merchant') {
                $href = route('admin.manage-merchants.show.basic-details', $entity_id);
            }
        @endphp
        <a href="{{ $href }}">
            <x-button.filled-button size="sm" color="primary" class="w-40">
                view more details
            </x-button.filled-button>
        </a>
    </div>

    <x-card.display-balance :balance="$this->balance_amount" color="primary" />

    <div class="flex flex-row justify-between gap-4 mt-5">
        <div class="flex-1">
            <p>Cashflow</p>
            <h1 class="text-2xl font-bold">₱ {{ number_format($cashflow['present'], 2) }}</h1>
            @if ($this->dateFilter !== 'none')
                <div class="flex flex-row justify-between text-xs">
                    <span>vs. previous {{$dateLabel}}</span>
                    <div class="flex items-center">
                        @if ($cashflow['positive'])
                            <x-icon.solid-arrow-up />
                        @else
                            <x-icon.solid-arrow-down />
                        @endif
                        <p>₱ {{ number_format($cashflow['previous'], 2) }}</p>
                    </div>
                </div>
            @endif
        </div>
        <div class="flex-1">
            <p>Cash Inflow</p>
            <h1 class="text-2xl font-bold">₱ {{ number_format($cashInflow['present'], 2) }}</h1>
            @if ($this->dateFilter !== 'none')
                <div class="flex flex-row justify-between text-xs">
                    <span>vs. previous {{$dateLabel}}</span>
                    <div class="flex items-center">
                        @if ($cashInflow['positive'])
                            <x-icon.solid-arrow-up />
                        @else
                            <x-icon.solid-arrow-down />
                        @endif
                        <p>₱ {{ number_format($cashInflow['previous'], 2) }}</p>
                    </div>
                </div>
            @endif
        </div>
        <div class="flex-1">
            <p>Cash Outflow</p>
            <h1 class="text-2xl font-bold">₱ {{ number_format($cashOutflow['present'], 2) }}</h1>
            @if ($this->dateFilter !== 'none')
                <div class="flex flex-row justify-between text-xs">
                    <span>vs. previous {{$dateLabel}}</span>
                    <div class="flex items-center">
                        @if ($cashOutflow['positive'])
                            <x-icon.solid-arrow-up />
                        @else
                            <x-icon.solid-arrow-down />
                        @endif
                        <p>₱ {{ number_format($cashOutflow['previous'], 2) }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="mt-5" x-data="{
        cashflowOverTimeChartCanvas: null,
        cashflowChartData: @js($cashflowChartData)
    }">
        <p class="font-bold">Cashflow Over Time</p>
        <canvas id="cashflowOverTime" wire:ignore x-init="cashflowOverTimeCanvas = new Chart(document.getElementById('cashflowOverTime'), {
            type: 'line',
            data: {
                labels: cashflowChartData.labels,
                datasets: [{
                    label: 'Cashflow',
                    data: cashflowChartData.infos,
                    borderWidth: 1,
                    borderColor: '#7F56D9',
                    backgroundColor: '#7F56D9'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
            }
        })"></canvas>
    </div>
</div>


{{-- @script
    <script>
        
        let cashflowChartCanvas = document.getElementById('cashflowOverTime').getContext('2d');

        let cashflowChart;


        $wire.on('update-cash-flow-chart', (chart) => {
            if(typeof cashflowChart === 'object') {
                cashflowChart.destory();
            }

            const chart_config = {
                type: 'line',
                data: {
                    labels: chart[0]['labels'],
                    datasets: [{
                        label: chart[0]['data_label'],
                        data: chart[0]['infos'],
                        borderWidth: 1,
                        borderColor: '#7F56D9',
                        backgroundColor: '#7F56D9'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                }
            }

            cashflowChart = new Chart(cashflowChartCanvas, chart_config);
        });

    </script>
@endscript --}}