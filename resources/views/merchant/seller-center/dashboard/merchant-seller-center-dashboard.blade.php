<x-main.content>
    <div class="flex flex-row justify-between">
        <div>
            <x-main.title>Dashboard</x-main.title>
            <p class="text-sm">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div class="flex flex-col items-end text-xs">
            <div class="flex flex-row items-center gap-[10px]">
                <p class="italic">Last logged in:</p>
                <p>{{ \Carbon\Carbon::parse(auth()->user()->last_login_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}</p>
            </div>
            <div class="flex flex-row items-center gap-[10px]">
                <p class="italic">IP Address:</p>
                <p class="font-bold">{{ auth()->user()->last_login_ip }}</p>
            </div>
        </div>
    </div>

    <div class="flex flex-row gap-8 my-8">
        {{-- 1st col --}}
        <div class="w-7/12 space-y-8 flex flex-col">
            {{-- To-Do --}}
            <div class="px-4 py-5 bg-white rounded-1">
                <h2 class="font-bold text-lg mb-6">To-Do</h2>
                <div class="grid grid-cols-4 gap-2 mb-9">
                    {{-- Pending Orders --}}
                    <div class="flex flex-row gap-2 px-1 py-2 items-center">
                        <div>
                            <x-icon.cart />
                        </div>
                        <div>
                            <h3 class="font-bold text-[19.2px] text-rp-red-500">{{ $this->count_pending_orders }}</h3>
                            <p class="text-[11.11px]">Pending Orders</p>
                        </div>
                    </div>
                    {{-- Shipping Products --}}
                    <div class="flex flex-row gap-2 px-1 py-2 items-center">
                        <div>
                            <x-icon.ship-products />
                        </div>
                        <div>
                            <h3 class="font-bold text-[19.2px] text-rp-red-500">{{ $this->count_shipping_products }}
                            </h3>
                            <p class="text-[11.11px]">Shipping Products</p>
                        </div>
                    </div>
                    {{-- Sold Out Products --}}
                    <div class="flex flex-row gap-2 px-1 py-2 items-center">
                        <div>
                            <x-icon.sold />
                        </div>
                        <div>
                            <h3 class="font-bold text-[19.2px] text-rp-red-500">{{ $this->count_sold_out_products }}
                            </h3>
                            <p class="text-[11.11px]">Sold Out Products</p>
                        </div>
                    </div>
                    {{-- Pending Bookings --}}
                    <div class="flex flex-row gap-2 px-1 py-2 items-center">
                        <div>
                            <x-icon.booking />
                        </div>
                        <div>
                            <h3 class="font-bold text-[19.2px] text-rp-red-500">{{ $this->count_pending_bookings }}</h3>
                            <p class="text-[11px]">Pending Bookings</p>
                        </div>
                    </div>
                    {{-- To Ship Products --}}
                    <div class="flex flex-row gap-2 px-1 py-2 items-center">
                        <div>
                            <x-icon.open-box />
                        </div>
                        <div>
                            <h3 class="font-bold text-[19.2px] text-rp-red-500">{{ $this->count_to_ship_products }}</h3>
                            <p class="text-[11px]">To Ship Products</p>
                        </div>
                    </div>
                    {{-- Pending Return/Refund --}}
                    <div class="flex flex-row gap-2 px-1 py-2 items-center">
                        <div>
                            <x-icon.cancel-purchase />
                        </div>
                        <div>
                            <h3 class="font-bold text-[19.2px] text-rp-red-500">{{ $this->count_pending_returns }}</h3>
                            <p class="text-[10px] break-keep">Pending Return/Refund</p>
                        </div>
                    </div>
                    {{-- Pending Inquiries --}}
                    <div class="flex flex-row gap-2 px-1 py-2 items-center">
                        <div>
                            <x-icon.inquiry />
                        </div>
                        <div>
                            <h3 class="font-bold text-[19.2px] text-rp-red-500">{{ $this->count_pending_inquiries }}
                            </h3>
                            <p class="text-[11px]">Pending Inquiries</p>
                        </div>
                    </div>
                    {{-- In progress Bookings --}}
                    <div class="flex flex-row gap-2 px-1 py-2 items-center">
                        <div>
                            <x-icon.in-progress />
                        </div>
                        <div>
                            <h3 class="font-bold text-[19.2px] text-rp-red-500">{{ $this->count_in_progress_bookings }}
                            </h3>
                            <p class="text-[11px]">In progress Bookings</p>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Pending Request --}}
            <div class="px-4 py-5 bg-white rounded-1 flex-1 flex flex-col justify-between">
                <div>
                    <h2 class="font-bold text-lg mb-4">Active Requests</h2>
                    <div class="flex flex-row">
                        <button wire:click='$set("active_requests_tab", "orders")'
                            class="px-3 py-2 w-[146px] text-center {{ $active_requests_tab === 'orders' ? 'text-rp-red-500 font-bold border-b border-b-rp-red-500' : '' }}">Orders</button>
                        <button wire:click='$set("active_requests_tab", "bookings")'
                            class="px-3 py-2 w-[169px] text-center {{ $active_requests_tab === 'bookings' ? 'text-rp-red-500 font-bold border-b border-b-rp-red-500' : '' }}">Service
                            Bookings</button>
                    </div>
                    <div class="w-full overflow-auto mt-3">
                        {{-- Orders table --}}
                        @if ($active_requests_tab === 'orders')
                            <x-table.standard>
                                <x-slot:table_header>
                                    <x-table.standard.td class="font-bold">User</x-table.standard.td>
                                    <x-table.standard.td class="font-bold">Order</x-table.standard.td>
                                    <x-table.standard.td class="font-bold">
                                        <div class="flex flex-row gap-1 items-center">
                                            <p>Amount</p>
                                            <button wire:click='sortOrders("amount")'>
                                                <x-icon.sort />
                                            </button>
                                        </div>
                                    </x-table.standard.td>
                                    <x-table.standard.td class="font-bold">
                                        <div class="flex flex-row gap-1 items-center">
                                            <p>Delivery</p>
                                            <button wire:click='sortOrders("delivery")'>
                                                <x-icon.sort />
                                            </button>
                                        </div>
                                    </x-table.standard.td>
                                    <x-table.standard.td class="font-bold">Status</x-table.standard.td>
                                </x-slot:table_header>
                                <x-slot:table_data>
                                    @foreach ($requests as $item)
                                        <x-table.standard.row>
                                            <x-table.standard.td>
                                                <div class="flex flex-row gap-1 items-center">
                                                    <div class="w-[32px] h-[32px] min-w-[32px] max-w-[32px]">
                                                        <img src="{{ $item->buyer->media->isNotEmpty() ? $this->get_media_url($item->buyer->media->first(), 'thumbnail') : url('images/user/default-avatar.png') }}"
                                                            class="w-full h-full object-cover rounded-full" alt="User avatar" />
                                                    </div>
                                                    <p>
                                                        {{ $item->buyer->name }}
                                                    </p>
                                                </div>
                                            </x-table.standard.td>
                                            <x-table.standard.td>
                                                {{ $item->product->name }}
                                            </x-table.standard.td>
                                            <x-table.standard.td class="text-rp-green-600 font-bold">
                                                {{ number_format($item->amount, 2) }}
                                            </x-table.standard.td>
                                            <x-table.standard.td>
                                                {{ $item->payment_option->name }}
                                            </x-table.standard.td>
                                            <x-table.standard.td>
                                                <div class="flex flex-row gap-2 justify-between items-center">
                                                    <div>
                                                        @switch($item->shipping_status->name)
                                                            @case('Pending')
                                                                <x-status color="yellow" class="w-28">Pending</x-status>
                                                                @break
                                                            @case('Packed')
                                                                <x-status color="yellow" class="w-28">Packed</x-status>
                                                                @break
                                                            @default
                                                                {{ $item->shipping_status->name }}
                                                        @endswitch
                                                    </div>
                                                    <a  href="{{ route('merchant.seller-center.logistics.orders.show', ['merchant' => $merchant, 'productOrder' => $item]) }}">
                                                        <x-icon.chevron-right />
                                                    </a>
                                                </div>
                                            </x-table.standard.td>
                                        </x-table.standard.row>
                                    @endforeach
                                </x-slot:table_data>
                            </x-table.standard>
                        @endif

                        @if ($active_requests_tab === 'bookings')
                            <x-table.standard>
                                <x-slot:table_header>
                                    <x-table.standard.td class="font-bold">User</x-table.standard.td>
                                    <x-table.standard.td class="font-bold">Service</x-table.standard.td>
                                    <x-table.standard.td class="font-bold">
                                        <div class="flex flex-row gap-1 items-center">
                                            <p>Schedule</p>
                                            <button wire:click='toggleBookingsSort'>
                                                <x-icon.sort />
                                            </button>
                                        </div>
                                    </x-table.standard.td>
                                    <x-table.standard.td class="font-bold">Status</x-table.standard.td>
                                </x-slot:table_header>
                                <x-slot:table_data>
                                    @foreach ($requests as $item)
                                        <x-table.standard.row>
                                            <x-table.standard.td>
                                                <div class="flex flex-row gap-1 items-center">
                                                    <div class="w-[32px] h-[32px] min-w-[32px] max-w-[32px]">
                                                        <img src="{{ $item->entity->media->isNotEmpty() ? $this->get_media_url($item->entity->media->first(), 'thumbnail') : url('images/user/default-avatar.png') }}"
                                                            class="w-full h-full object-cover rounded-full" alt="User avatar" />
                                                    </div>
                                                    <p>
                                                        {{ $item->entity->name }}
                                                    </p>
                                                </div>
                                            </x-table.standard.td>
                                            <x-table.standard.td>
                                                {{ $item->service->name }}
                                            </x-table.standard.td>
                                            <x-table.standard.td class="text-rp-green-600 font-bold">
                                                {{ \Carbon\Carbon::parse($item->service_date)->format('M d, Y') }}
                                            </x-table.standard.td>
                                            <x-table.standard.td>
                                                <div class="flex flex-row gap-2 justify-between items-center">
                                                    <div>
                                                        @switch($item->status->name)
                                                            @case('Booked')
                                                                <x-status color="yellow" class="w-28">Pending</x-status>
                                                            @break

                                                            @case('In Progress')
                                                                <x-status color="primary" class="w-28">In Progress</x-status>
                                                            @break

                                                            @default
                                                        @endswitch
                                                    </div>
                                                    <a  href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $item->service, 'type' => 'bookings', 'booking' => $item]) }}">
                                                        <x-icon.chevron-right />
                                                    </a>
                                                </div>
                                            </x-table.standard.td>
                                        </x-table.standard.row>
                                    @endforeach
                                </x-slot:table_data>
                            </x-table.standard>
                        @endif
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="flex items-center justify-center w-full gap-8">
                    @if ($requests->hasPages())
                        <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                            <button wire:click="previousPage" {{ $requests->onFirstPage() ? 'disabled' : '' }}
                                class="{{ $requests->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                                <svg  width="7" height="13" viewBox="0 0 7 13"
                                    fill="none">
                                    <path d="M6 11.5001L1 6.50012L6 1.50012" stroke="#647887" stroke-width="1.66667"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <!-- Pagination Elements -->
                            @foreach ($elements as $element)
                                <!-- "Three Dots" Separator -->
                                @if (is_string($element))
                                    <button
                                        class="h-full px-4 py-2 bg-white border-r cursor-default">{{ $element }}</button>
                                @else
                                    <button wire:click="gotoPage({{ $element }})"
                                        class="h-full bg-white border-r px-4 py-2 {{ $element == $requests->currentPage() ? 'cursor-default' : 'cursor-pointer' }}">{{ $element }}</button>
                                @endif
                            @endforeach

                            <button wire:click="nextPage" {{ !$requests->hasMorePages() ? 'disabled' : '' }}
                                class="{{ !$requests->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                                <svg  width="7" height="13" viewBox="0 0 7 13"
                                    fill="none">
                                    <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        {{-- 2nd col --}}
        <div class="w-5/12 flex flex-col space-y-8">
            {{-- Best-selling Products --}}
            <div class="px-4 py-5 bg-white rounded-1 flex-1">
                <div class="flex flex-row items-center justify-between">
                    <h2 class="font-bold text-lg">Best-selling Products</h2>
                    <a  href="{{ route('merchant.seller-center.assets.index', ['merchant' => $merchant]) }}" class="text-rp-red-500 underline">See all</a>
                </div>

                <div class="overflow-auto">
                    <x-table.standard>
                        <x-slot:table_header>
                            <x-table.standard.th>Product</x-table.standard.th>
                            <x-table.standard.th class="!text-right">Amount sold</x-table.standard.th>
                        </x-slot:table_header>
                        <x-slot:table_data>
                            @foreach ($this->best_selling_products as $product)
                                <x-table.standard.row>
                                    <x-table.standard.td>{{ $product->name }}</x-table.standard.td>
                                    <x-table.standard.td>
                                        <div class="flex flex-row items-center gap-2 justify-end">
                                            <p class="text-rp-green-600 font-bold">{{ number_format($product->sold_count) }} sold</p>
                                            <a  href="{{ route('merchant.seller-center.assets.show', ['merchant' => $merchant, 'product' => $product->id]) }}">
                                                <x-icon.chevron-right />
                                            </a>
                                        </div>
                                    </x-table.standard.td>
                                </x-table.standard.row>
                            @endforeach
                        </x-slot:table_data>
                    </x-table.standard>
                </div>
            </div>
            {{-- Best-selling Services --}}
            <div class="px-4 py-5 bg-white rounded-1 flex-1">
                <div class="flex flex-row items-center justify-between">
                    <h2 class="font-bold text-lg">Best-selling Services</h2>
                    <a  href="{{ route('merchant.seller-center.services.index', ['merchant' => $merchant]) }}" class="text-rp-red-500 underline">See all</a>
                </div>

                <div class="overflow-auto">
                    <x-table.standard>
                        <x-slot:table_header>
                            <x-table.standard.th>Services</x-table.standard.th>
                            <x-table.standard.th class="!text-right">Booking amount</x-table.standard.th>
                        </x-slot:table_header>
                        <x-slot:table_data>
                            @foreach ($this->best_selling_services as $service)
                                <x-table.standard.row>
                                    <x-table.standard.td>{{ $service->name }}</x-table.standard.td>
                                    <x-table.standard.td>
                                        <div class="flex flex-row items-center gap-2 justify-end">
                                            <p class="text-rp-green-600 font-bold">{{ number_format($service->sold_count) }} sold</p>
                                            <a  href="{{ route('merchant.seller-center.services.show', ['merchant' => $merchant, 'service' => $service->id]) }}">
                                                <x-icon.chevron-right />
                                            </a>
                                        </div>
                                    </x-table.standard.td>
                                </x-table.standard.row>
                            @endforeach
                        </x-slot:table_data>
                    </x-table.standard>
                </div>
            </div>
        </div>
    </div>

    <livewire:merchant.seller-center.dashboard.seller-center-business-insights :merchant="$merchant" />

    <x-loader.black-screen wire:loading wire:target='active_requests_tab'>
        <x-loader.clock />
    </x-loader.black-screen>
</x-main.content>
