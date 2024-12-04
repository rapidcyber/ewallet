<div class="p-5 bg-white rounded-1 mt-3">
    <div class="flex flex-row justify-between">
        <h2 class="font-bold text-lg">Business Insights</h2>
        <x-dropdown.select-date wire:model.live="dateFilter" class="h-[32px]">
            <x-dropdown.select-date.option value="past_year">Past Year</x-dropdown.select-date.option>
            <x-dropdown.select-date.option value="past_6_months">Past 6 Months</x-dropdown.select-date.option>
            <x-dropdown.select-date.option value="past_30_days">Past 30 days</x-dropdown.select-date.option>
            <x-dropdown.select-date.option value="past_week">Past Week</x-dropdown.select-date.option>
            <x-dropdown.select-date.option value="past_24_hours">Past 24 Hours</x-dropdown.select-date.option>
        </x-dropdown.select-date>
    </div>
    <div class="flex flex-row gap-3">
        {{-- Chart --}}
        <div class="basis-5/12">
            <canvas wire:ignore class="h-full max-w-full" id="businessInsightsChart"></canvas>
        </div>
        <div class="basis-7/12">
            <div class="flex flex-row">
                <button wire:click="$set('business_insights_tab', 'products')" class="px-3 py-2 w-[146px] text-center {{ $business_insights_tab == 'products' ? 'text-rp-red-500 font-bold border-b border-b-rp-red-500' : '' }}">Products</button>
                <button wire:click="$set('business_insights_tab', 'services')" class="px-3 py-2 w-[169px] text-center {{ $business_insights_tab == 'services' ? 'text-rp-red-500 font-bold border-b border-b-rp-red-500' : '' }}">Services</button>
            </div>
            @if ($business_insights_tab == 'products')
                {{-- Products --}}
                <div class="flex flex-col gap-2 pt-5">
                    <button wire:click="$set('product_insights_tab', 'sales')" class="w-full px-3 py-4 border rounded-1 text-left {{ $product_insights_tab == 'sales' ? 'text-rp-red-500 border-rp-red-500 bg-rp-red-100 ' : 'border-rp-neutral-600' }}">
                        <p class="font-normal">Sales</p>
                        <h2 class="font-bold text-3.5xl">₱{{ number_format($product_sales['present'], 2) }}</h2>
                        <div class="flex flex-row gap-3">
                            <p>vs. previous {{ $dateLabel }}</p>
                            <div class="flex flex-row gap-2 items-center">
                                <x-icon.solid-arrow-up fill="{{ $product_insights_tab == 'sales' ? '#FF3D8F' : '#647887' }}" class="{{ $product_sales['positive'] ? '' : 'rotate-180' }}" />
                                <p>₱{{ number_format($product_sales['vs_previous'], 2) }}</p>
                            </div>
                        </div>
                    </button>

                    <button wire:click="$set('product_insights_tab', 'orders')" class="w-full px-3 py-4 border rounded-1 text-left {{ $product_insights_tab == 'orders' ? 'text-rp-red-500 border-rp-red-500 bg-rp-red-100' : 'border-rp-neutral-600' }}">
                        <p class="font-normal">Orders</p>
                        <h2 class="font-bold text-3.5xl">{{ $product_orders['present'] }}</h2>
                        <div class="flex flex-row gap-3">
                            <p>vs. previous {{ $dateLabel }}</p>
                            <div class="flex flex-row gap-2 items-center">
                                <x-icon.solid-arrow-up fill="{{ $product_insights_tab == 'orders' ? '#FF3D8F' : '#647887' }}" class="{{ $product_sales['positive'] ? '' : 'rotate-180' }}" />
                                <p>{{ $product_orders['vs_previous'] }}</p>
                            </div>
                        </div>
                    </button>
                </div>
            @elseif ($business_insights_tab == 'services')
                {{-- Services --}}
                <div class="grid grid-cols-2 gap-2 pt-5">
                    <button wire:click="$set('service_insights_tab', 'sales')" class="w-full px-3 py-4 border rounded-1 text-left {{ $service_insights_tab == 'sales' ? 'text-rp-red-500 border-rp-red-500 bg-rp-red-100' : 'border-rp-neutral-600' }}">
                        <p class="font-normal">Sales</p>
                        <h2 class="font-bold text-3.5xl">₱{{ number_format($service_sales['present'], 2) }}</h2>
                        <div class="flex flex-row gap-3">
                            <p>vs. previous {{ $dateLabel }}</p>
                            <div class="flex flex-row gap-2 items-center">
                                <x-icon.solid-arrow-up fill="{{ $service_insights_tab == 'sales' ? '#FF3D8F' : '#647887' }}" class="{{ $service_sales['positive'] ? '' : 'rotate-180' }}" />
                                <p>₱{{ number_format($service_sales['vs_previous'], 2) }}</p>
                            </div>
                        </div>
                    </button>

                    <button wire:click="$set('service_insights_tab', 'inquiries')" class="w-full px-3 py-4 border rounded-1 text-left {{ $service_insights_tab == 'inquiries' ? 'text-rp-red-500 border-rp-red-500 bg-rp-red-100' : 'border-rp-neutral-600' }}">
                        <p class="font-normal">Inquiries</p>
                        <h2 class="font-bold text-3.5xl">{{ $service_inquiries['present'] }}</h2>
                        <div class="flex flex-row gap-3">
                            <p>vs. previous {{ $dateLabel }}</p>
                            <div class="flex flex-row gap-2 items-center">
                                <x-icon.solid-arrow-up fill="{{ $service_insights_tab == 'inquiries' ? '#FF3D8F' : '#647887' }}" class="{{ $service_inquiries['positive'] ? '' : 'rotate-180' }}" />
                                <p>{{ $service_inquiries['vs_previous'] }}</p>
                            </div>
                        </div>
                    </button>

                    <button wire:click="$set('service_insights_tab', 'bookings')" class="w-full px-3 py-4 border rounded-1 text-left {{ $service_insights_tab == 'bookings' ? 'text-rp-red-500 border-rp-red-500 bg-rp-red-100' : 'border-rp-neutral-600' }}">
                        <p class="font-normal">Bookings</p>
                        <h2 class="font-bold text-3.5xl">{{ $service_bookings['present'] }}</h2>
                        <div class="flex flex-row gap-3">
                            <p>vs. previous {{ $dateLabel }}</p>
                            <div class="flex flex-row gap-2 items-center">
                                <x-icon.solid-arrow-up fill="{{ $service_insights_tab == 'bookings' ? '#FF3D8F' : '#647887' }}" class="{{ $service_bookings['positive'] ? '' : 'rotate-180' }}" />
                                <p>{{ $service_bookings['vs_previous'] }}</p>
                            </div>
                        </div>
                    </button>
                </div>
            @endif

        </div>
    </div>
    @push('scripts')
        @script
        <script type="module">

            let business_chart = document.getElementById('businessInsightsChart').getContext('2d');

            let initialized_chart;

            $wire.on('update-chart', (info) => {
                if (typeof initialized_chart === 'object') {
                    initialized_chart.destroy();
                }

                const chart_config = {
                    type: 'line',
                    data: {
                        labels: info[0]['labels'],
                        datasets: [{
                            label: info[0]['data_label'],
                            data: info[0]['infos'],
                            borderWidth: 2,
                            borderColor: '#FF3D8F',
                            backgroundColor: '#FF3D8F',
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
                        animation: {
                            duration: 0
                        },
                    }
                };

                initialized_chart = new Chart(business_chart, chart_config);
            });
        </script>
        @endscript
    @endpush
</div>