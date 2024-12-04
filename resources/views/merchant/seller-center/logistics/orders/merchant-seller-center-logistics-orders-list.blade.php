<x-main.content x-data="{ action: '' }">
    <x-main.title class="mb-8">Logistics</x-main.title>
    {{-- Tabs --}}
    <x-tab class="mb-8">
        <x-tab.tab-item  href="{{ route('merchant.seller-center.logistics.orders.index', ['merchant' => $merchant]) }}"
            :isActive="request()->routeIs('merchant.seller-center.logistics.orders.*')" class="w-56">Orders</x-tab.tab-item>
        <x-tab.tab-item 
            href="{{ route('merchant.seller-center.logistics.return-orders.index', ['merchant' => $merchant]) }}"
            :isActive="request()->routeIs('merchant.seller-center.logistics.return-orders.*')" class="w-56">Return Orders</x-tab.tab-item>
        @can('merchant-warehouse', [$merchant, 'view'])
            <x-tab.tab-item 
                href="{{ route('merchant.seller-center.logistics.warehouse-shipping', ['merchant' => $merchant]) }}"
                :isActive="request()->routeIs('merchant.seller-center.logistics.warehouse-shipping')" class="w-56">Warehouse and Shipping</x-tab.tab-item>
        @endcan
    </x-tab>


    <div class="grid grid-cols-3 gap-6 rounded-2xl bg-white mb-4">
        <div class="flex flex-col gap-1 p-6">
            <div class="flex items-center gap-1">
                <p class="text-sm">Cancelation Rate</p>
                <div class="cursor-pointer">
                    <x-icon.important width="17" height="18" />
                </div>
            </div>
            <h3 class="text-3.5xl font-bold">{{ number_format($this->cancellation_rate, 2) . '%' }}</h3>
        </div>
        {{-- <div class="flex flex-col gap-1 p-6">
            <div class="flex items-center gap-1">
                <p class="text-sm">PNR</p>
                <div class="cursor-pointer">
                    <x-icon.important width="17" height="18" />
                </div>
            </div>
            <h3 class="text-3.5xl font-bold">0.00%</h3>
        </div> --}}
        <div class="flex flex-col gap-1 p-6">
            <div class="flex items-center gap-1">
                <p class="text-sm">Late Fulfillment</p>
                <div class="cursor-pointer">
                    <x-icon.important width="17" height="18" />
                </div>
            </div>
            <h3 class="text-3.5xl font-bold">{{ number_format($this->late_fulfillment_rate, 2) . '%' }}</h3>
        </div>
        <div class="flex flex-col gap-1 p-6">
            <div class="flex items-center gap-1">
                <p class="text-sm">Fast Fulfillment Rate</p>
                <div class="cursor-pointer">
                    <x-icon.important width="17" height="18" />
                </div>
            </div>
            <h3 class="text-3.5xl font-bold">{{ number_format($this->fast_fulfillment_rate, 2) . '%' }}</h3>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="flex flex-col gap-[22px] mb-[22px]">
        {{-- STATUS --}}
        <div class="relative grid grid-cols-7 gap-[15px]">

            <x-card.filter-card wire:click="$set('activeBox', null)" label="All" :data="$this->count_all"
                :isActive="$activeBox === null" />

            <x-card.filter-card wire:click="$set('activeBox', 'unpaid')" label="Unpaid" :data="$this->count_unpaid"
                :isActive="$activeBox === 'unpaid'" />

            <x-card.filter-card wire:click="$set('activeBox', 'to_ship')" label="To Ship" :data="$this->count_to_ship"
                :isActive="$activeBox === 'to_ship'" />

            <x-card.filter-card wire:click="$set('activeBox', 'shipping')" label="Shipping" :data="$this->count_shipping"
                :isActive="$activeBox === 'shipping'" />

            <x-card.filter-card wire:click="$set('activeBox', 'completed')" label="Completed" :data="$this->count_completed"
                :isActive="$activeBox === 'completed'" />

            <x-card.filter-card wire:click="$set('activeBox', 'cancellation')" label="Cancellation" :data="$this->count_cancelled"
                :isActive="$activeBox === 'cancellation'" />

            <x-card.filter-card wire:click="$set('activeBox', 'failed_delivery')" label="Failed Delivery"
                :data="$this->count_failed" :isActive="$activeBox === 'failed_delivery'" />

        </div>

        {{-- SEARCH --}}
        <div class="relative flex flex-col gap-[20px] p-6 bg-white rounded-xl ">
            {{-- intpu search --}}
            <form wire:submit.prevent='search' class="flex gap-5">
                <x-input.search wire:model='searchTerm' class="flex-1" />
                <div class="flex gap-2">
                    <x-button.filled-button type="submit" class="w-32">Search</x-button.filled-button>
                    <x-button.outline-button wire:click='reset_search' type="reset"
                        class="w-32">Reset</x-button.outline-button>
                </div>
            </form>

            {{-- Dropdown filter --}}
            <div class="grid {{ $activeBox == 'to_ship' ? 'grid-cols-5' : 'grid-cols-4' }} gap-3">
                {{-- Show if activeBox is 'To Ship' --}}
                @if ($activeBox === 'to_ship')
                    <x-dropdown.select wire:model.live='to_ship_status'>
                        <x-dropdown.select.option value="pending">Pending</x-dropdown.select.option>
                        <x-dropdown.select.option value="packed">Packed</x-dropdown.select.option>
                        <x-dropdown.select.option value="ready_to_ship">Ready to Ship</x-dropdown.select.option>
                    </x-dropdown.select>
                @endif


                {{-- Date --}}
                <x-dropdown.select-date wire:model.live="date">
                    <x-dropdown.select-date.option value="">Date</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="today">Today</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="past_week">Past Week</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="past_month">Past Month</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="past_6_months">Past 6 months</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="past_year">Past Year</x-dropdown.select-date.option>
                </x-dropdown.select-date>

                {{-- Deadline --}}
                <x-dropdown.select-date wire:model.live="deadline">
                    <x-dropdown.select-date.option value="">Deadline</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="12">Less than 12 hours</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="24">Less than 24 hours</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="48">Less than 48 hours</x-dropdown.select-date.option>
                    <x-dropdown.select-date.option value="72">Less than 72 hours</x-dropdown.select-date.option>
                </x-dropdown.select-date>

                {{-- Amount --}}
                <x-dropdown.select wire:model.live="amount">
                    <x-dropdown.select.option value="">Amount</x-dropdown.select.option>
                    <x-dropdown.select.option value="0-4999">0-4999</x-dropdown.select.option>
                    <x-dropdown.select.option value="5000-9999">5000-9999</x-dropdown.select.option>
                    <x-dropdown.select.option value="10000-14999">10000-14999</x-dropdown.select.option>
                    <x-dropdown.select.option value="15000+">15000 and above</x-dropdown.select.option>
                </x-dropdown.select>

                {{-- Delivery type --}}
                <x-dropdown.select wire:model.live="delivery_type">
                    <x-dropdown.select.option value="">Delivery Type</x-dropdown.select.option>
                    <x-dropdown.select.option value="standard">Standard</x-dropdown.select.option>
                    <x-dropdown.select.option value="on_demand">On-Demand</x-dropdown.select.option>
                </x-dropdown.select>
            </div>
        </div>
    </div>

    <div>
        <x-table.rounded>
            <x-slot:table_header>
                <x-table.rounded.th>Buyer</x-table.rounded.th>
                <x-table.rounded.th>Order Details</x-table.rounded.th>
                <x-table.rounded.th>Total Amount</x-table.rounded.th>
                <x-table.rounded.th>Delivery</x-table.rounded.th>
                <x-table.rounded.th>Status</x-table.rounded.th>
                <x-table.rounded.th>Actions</x-table.rounded.th>
            </x-slot:table_header>
            <x-slot:table_data>

                @foreach ($product_orders as $key => $product_order)
                    <tr>
                        <td class="pt-8"></td>
                    </tr>
                    <x-table.rounded.row @click='$wire.view_product_order({{ $product_order->id }})'
                        class="cursor-pointer hover:bg-rp-neutral-50">
                        <x-table.rounded.td>
                            <div class="flex gap-[8px] items-center">
                                @if ($product_order->buyer->media->isNotEmpty())
                                    <img src="{{ $this->get_media_url($product_order->buyer->media->first(), 'thumbnail') }}"
                                        alt="" class="w-8 h-8 rounded-full">
                                @else
                                    <img src="{{ url('images/user/default-avatar.png') }}" alt=""
                                        class="w-8 h-8 rounded-full">
                                @endif
                                <div class="flex flex-col">
                                    <span class="font-bold text-[13px]">{{ $product_order->buyer->name }}</span>
                                    <p class="text-[11px]">{{ $this->format_phone_number($product_order->buyer->phone_number, $product_order->buyer->phone_iso) }}</p>
                                </div>
                            </div>
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            <div class="flex gap-3">
                                @if ($product_order->product->first_image)
                                    <img src="{{ $this->get_media_url($product_order->product->first_image, 'thumbnail') }}"
                                        alt="" class="w-16 h-16 rounded-md object-cover">
                                @endif
                                <div class="grow text-rp-neutral-600">
                                    <h5 class="text-[13px] font-bold">{{ $product_order->product->name }}</h5>
                                    {{-- <p class="text-[11px]">Silver, 64GB</p> --}}
                                    <p class="text-[11px]">Order Number:</p>
                                    <p class="text-[11px] text-primary">{{ $product_order->order_number }}</p>
                                </div>
                            </div>
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            <div>
                                <h5 class="text-[13px] font-bold">{{ \Number::currency($product_order->amount * $product_order->quantity, 'PHP') }}</h5>
                                <p
                                    class="text-[11px] w-fit px-2 py-[2px] text-rp-red-500 border border-rp-red-500 rounded-md">
                                    {{ $product_order->payment_option->name }}</p>
                                <p class="text-[11px]">Product Price: 
                                    {{ \Number::currency($product_order->product->price, 'PHP') }}</p>
                                <p class="text-[11px]">Quantity: {{ $product_order->quantity }}</p>
                            </div>
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            <div>
                                <h5 class="text-[13px] font-bold">
                                    {{ ucwords(str_replace('_', ' ', $product_order->delivery_type)) }}</h5>
                                @if (!in_array($product_order->shipping_status->name, ['Unpaid', 'Pending', 'Packed']))
                                    <p class="text-[11px]">
                                        {{ $product_order->warehouse ? 'Warehouse: ' . $product_order->warehouse->name : '' }}
                                    </p>
                                    <p class="text-[11px]">3PL: {{ $product_order->shipping_option->name }}</p>
                                    @if ($product_order->tracking_number)
                                        <p class="text-[11px]">TN: <span
                                                class="text-rp-red-500">{{ $product_order->tracking_number }}</span>
                                        </p>
                                    @endif
                                @endif
                            </div>
                        </x-table.rounded.td>
                        <x-table.rounded.td class="text-[13px] font-bold">
                            @if (in_array($product_order->shipping_status->name, ['Pending', 'Packed', 'Ready to Ship']))
                                <div class="text-rp-neutral-600">
                                    <h5 class="text-[13px] font-bold">{{ $product_order->shipping_status->name }}</h5>
                                    <p
                                        class="text-[11px] w-fit px-2 py-[2px] text-rp-red-500 border border-rp-red-500 bg-rp-red-200 rounded-md">
                                        {{ $this->calculate_remaining_hours($product_order->created_at) }}</p>
                                    <div class="flex gap-x-3 flex-wrap max-w-[180px] text-[11px]">
                                        <div
                                            class="flex {{ $product_order->documents?->awb_downloaded ? 'opacity-50' : '' }}">
                                            <x-icon.document />
                                            <span class="ml-1">AWB</span>
                                        </div>
                                        <div
                                            class="flex {{ $product_order->documents?->pick_list_downloaded ? 'opacity-50' : '' }}">
                                            <x-icon.document />
                                            <span class="ml-1">Pick List</span>
                                        </div>
                                        <div
                                            class="flex {{ $product_order->documents?->packing_list_downloaded ? 'opacity-50' : '' }}">
                                            <x-icon.document />
                                            <span class="ml-1">Packing List</span>
                                        </div>
                                    </div>
                                </div>
                            @elseif (
                                $product_order->shipping_status->name == 'Cancellation' &&
                                    $product_order->termination_reason &&
                                    $product_order->processed_at)
                                <div>
                                    <h5 class="text-[13px] font-bold">Cancelled</h5>
                                    <p class="text-[11px]">Reason for cancellation:</p>
                                    <p class="text-[11px] text-rp-red-500">{{ $product_order->termination_reason }}
                                    </p>
                                    <p class="text-[11px]">Cancel Date:
                                        <span>{{ \Carbon\Carbon::parse($product_order->processed_at)->format('M d, Y') }}</span>
                                    </p>
                                </div>
                            @elseif ($product_order->shipping_status->name == 'Failed Delivery' && $product_order->termination_reason)
                                <div>
                                    <h5 class="text-[13px] font-bold">Failed Delivery</h5>
                                    <p class="text-[11px]">Reason for failed delivery:</p>
                                    <p class="text-[11px] text-rp-red-500">{{ $product_order->termination_reason }}
                                    </p>
                                </div>
                            @else
                                {{ $product_order->shipping_status->name }}
                            @endif
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            <div class="flex flex-col gap-[6px]">
                                @switch($product_order->shipping_status->name)
                                    @case('Unpaid')
                                        <x-button.outline-button
                                            @click.stop="$wire.open_cancel_order_modal({{ $product_order->id }})">Cancel
                                            Order</x-button.outline-button>
                                    @break

                                    @case('Pending')
                                        <x-button.filled-button
                                            @click.stop="action='pack';$wire.set('order_id', {{ $product_order->id }});$wire.set('show_modal', true)">
                                            Pack & Print
                                        </x-button.filled-button>
                                        <div class="relative w-full" x-data="{ drop: false }">
                                            <x-button.outline-button @click.stop="drop = !drop" class="w-full">More
                                                Action</x-button.outline-button>
                                            <x-dropdown.dropdown-list x-cloak x-show="drop"
                                                class="absolute bg-white top-[100%] right-0 w-28"
                                                @click.outside="drop = false">
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.download_documents({{ $product_order->id }}, 'all')">Print
                                                    Docs</x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.open_cancel_order_modal({{ $product_order->id }})">Cancel
                                                    Order</x-dropdown.dropdown-list.item>
                                            </x-dropdown.dropdown-list>
                                        </div>
                                    @break

                                    @case('Packed')
                                        <x-button.filled-button
                                            @click.stop="action='ship';$wire.set('order_id', {{ $product_order->id }});$wire.set('show_modal', true)">
                                            Arrange Shipment
                                        </x-button.filled-button>
                                        <div class="relative w-full" x-data="{ drop: false }">
                                            <x-button.outline-button @click.stop="drop = !drop" class="w-full">More
                                                Action</x-button.outline-button>
                                            <x-dropdown.dropdown-list x-cloak x-show="drop"
                                                class="absolute top-[100%] right-0 w-36" @click.outside="drop = false">
                                                <x-dropdown.dropdown-list.item>Recreate Package</x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.download_documents({{ $product_order->id }}, 'awb')">Print
                                                    AWB</x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.download_documents({{ $product_order->id }}, 'packing-list')">Print
                                                    Packing
                                                    List</x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.download_documents({{ $product_order->id }}, 'pick-list')">Print
                                                    Pick List</x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.download_documents({{ $product_order->id }}, 'all')">Print
                                                    All</x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.open_cancel_order_modal({{ $product_order->id }})">Cancel
                                                    Order</x-dropdown.dropdown-list.item>
                                            </x-dropdown.dropdown-list>
                                        </div>
                                    @break

                                    @case('Ready to Ship')
                                        <x-button.outline-button
                                            @click.stop="$wire.show_order_logs({{ $product_order->id }})">Logistics
                                            Status</x-button.outline-button>
                                        <div class="relative" x-data="{ drop: false }" class="w-full">
                                            <x-button.outline-button @click.stop="drop = !drop" class="w-full">More
                                                Action</x-button.outline-button>
                                            <x-dropdown.dropdown-list x-cloak x-show="drop"
                                                class="absolute top-[100%] right-0 w-36" @click.outside="drop = false">
                                                <x-dropdown.dropdown-list.item>Recreate Package</x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.download_documents({{ $product_order->id }}, 'awb')">Print
                                                    AWB</x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.download_documents({{ $product_order->id }}, 'packing-list')">Print
                                                    Packing
                                                    List</x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.download_documents({{ $product_order->id }}, 'pick-list')">Print
                                                    Pick List</x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.download_documents({{ $product_order->id }}, 'all')">Print
                                                    All</x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item
                                                    @click.stop="$wire.open_cancel_order_modal({{ $product_order->id }})">Cancel
                                                    Order</x-dropdown.dropdown-list.item>
                                            </x-dropdown.dropdown-list>
                                        </div>
                                    @break

                                    @case('Shipping')
                                    @case('Cancellation')

                                    @case('Failed Delivery')
                                        <x-button.outline-button
                                            @click.stop="$wire.show_order_logs({{ $product_order->id }})">Logistics
                                            Status</x-button.outline-button>
                                    @break

                                    @case('Completed')
                                        <x-button.outline-button
                                            @click.stop="$wire.show_order_logs({{ $product_order->id }})">Logistics
                                            Status</x-button.outline-button>

                                        @default
                                    @endswitch

                                </div>
                            </x-table.rounded.td>
                        </x-table.rounded.row>
                    @endforeach
                </x-slot:table_data>
            </x-table.rounded>

            {{-- Pagination --}}
            <div class="w-full flex items-center justify-center gap-8">
                @if ($product_orders->hasPages())
                    <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                        <button wire:click="previousPage" {{ $product_orders->onFirstPage() ? 'disabled' : '' }}
                            class="{{ $product_orders->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                            <svg  width="7" height="13" viewBox="0 0 7 13"
                                fill="none">
                                <path d="M6 11.5001L1 6.50012L6 1.50012" stroke="#647887" stroke-width="1.66667"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        {{-- Pagination Elements --}}
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <button
                                    class="h-full bg-white border-r px-4 py-2 cursor-default">{{ $element }}</button>
                            @else
                                <button wire:click="gotoPage({{ $element }})"
                                    class="h-full border-r px-4 py-2 {{ $element == $product_orders->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                            @endif
                        @endforeach

                        <button wire:click="nextPage" {{ !$product_orders->hasMorePages() ? 'disabled' : '' }}
                            class="{{ !$product_orders->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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

        {{-- Confirmation Modal --}}
        <x-modal x-model="$wire.show_modal">
            <template x-if="action === 'pack'">
                <x-modal.confirmation-modal class="text-pretty">
                    <x-slot:title>Mark as Packed?</x-slot:title>
                    <x-slot:message>
                    </x-slot:message>
                    <x-slot:action_buttons>
                        <x-button.outline-button class="flex-1"
                            @click="$wire.set('show_modal',false)">cancel</x-button.outline-button>
                        <x-button.filled-button class="flex-1"
                            @click="$wire.pack_and_print">proceed</x-button.filled-button>
                    </x-slot:action_buttons>
                </x-modal.confirmation-modal>
            </template>
            <template x-if="action === 'ship'">
                <x-modal.confirmation-modal class="text-pretty">
                    <x-slot:title>Mark as Ready to Ship?</x-slot:title>
                    <x-slot:message>
                    </x-slot:message>
                    <x-slot:action_buttons>
                        <x-button.outline-button class="flex-1"
                            @click="$wire.set('show_modal',false)">cancel</x-button.outline-button>
                        <x-button.filled-button class="flex-1"
                            @click="$wire.arrange_shipment">proceed</x-button.filled-button>
                    </x-slot:action_buttons>
                </x-modal.confirmation-modal>
            </template>
        </x-modal>

        {{-- LOGISTIC STATUS MODAL --}}
        @if ($show_modal and $order_logs)
            <x-modal x-model="$wire.show_modal">
                <div class="absolute flex flex-col gap-8 bg-white p-10 rounded-2xl w-[718px] max-w-[90%] max-h-[95%] overflow-y-auto"
                    @click.outside="$wire.set('show_modal',false)">
                    {{-- CLOSE BUTTON --}}
                    <button class="absolute top-6 right-6" @click="$wire.set('show_modal',false)">
                        <x-icon.close />
                    </button>
                    <h3 class="text-2xl font-bold ">Logistic Status</h3>

                    {{-- ORDER TRACKER ILLUSTRATION --}}
                    <div class="grid grid-cols-3">
                        <div class="w-full flex flex-col gap-3 justify-center items-center">
                            <svg class="z-30"  width="57" height="57"
                                viewBox="0 0 57 57" fill="none">
                                <rect x="0.75" y="0.5" width="56" height="56" rx="8"
                                    fill="{{ $delivery_status >= 1 ? '#FF3D8F' : '#bbc5cd' }}" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M26.6774 12.9043C27.3078 12.5409 28.0227 12.3496 28.7503 12.3496C29.478 12.3496 30.1928 12.5409 30.8232 12.9043C30.824 12.9048 30.8249 12.9052 30.8257 12.9057L41.2749 18.8767C41.9053 19.2406 42.4288 19.7639 42.7931 20.3941C43.1574 21.0243 43.3495 21.7392 43.3502 22.467L43.3502 22.4682L43.3502 34.4114C43.3495 35.1393 43.1574 35.8542 42.7931 36.4843C42.4288 37.1145 41.9053 37.6378 41.2749 38.0018L41.2704 38.0043L30.8257 43.9727C30.8248 43.9732 30.824 43.9737 30.8232 43.9742C30.1928 44.3375 29.478 44.5288 28.7503 44.5288C28.0227 44.5288 27.3078 44.3375 26.6775 43.9742C26.6766 43.9737 26.6758 43.9732 26.675 43.9727L16.2302 38.0043L16.2257 38.0018C15.5954 37.6378 15.0718 37.1145 14.7075 36.4843C14.3433 35.8542 14.1511 35.1393 14.1504 34.4114V22.467C14.1511 21.7392 14.3433 21.0243 14.7075 20.3941C15.0718 19.7639 15.5954 19.2406 16.2257 18.8767L16.2302 18.8741L26.675 12.9057C26.6758 12.9052 26.6766 12.9048 26.6774 12.9043ZM28.7503 14.6801C28.4308 14.6801 28.1169 14.7642 27.8402 14.9239L27.8357 14.9265L17.391 20.8949C17.3903 20.8953 17.3897 20.8957 17.389 20.896C17.1134 21.0556 16.8845 21.2846 16.7252 21.5604C16.5655 21.8366 16.4812 22.1499 16.4808 22.4689V34.4095C16.4812 34.7285 16.5655 35.0419 16.7252 35.3181C16.8845 35.5938 17.1134 35.8228 17.3889 35.9824C17.3896 35.9827 17.3903 35.9831 17.391 35.9835L27.8402 41.9545C28.1169 42.1143 28.4308 42.1984 28.7503 42.1984C29.0698 42.1984 29.3837 42.1143 29.6605 41.9545L29.665 41.9519L40.1097 35.9835C40.1103 35.9832 40.111 35.9828 40.1117 35.9824C40.3872 35.8228 40.6161 35.5938 40.7755 35.3181C40.935 35.0421 41.0193 34.729 41.0198 34.4102C41.0198 34.4098 41.0198 34.4094 41.0198 34.409V22.4694C41.0198 22.469 41.0198 22.4686 41.0198 22.4682C41.0193 22.1494 40.935 21.8364 40.7755 21.5604C40.6161 21.2846 40.3872 21.0556 40.1116 20.896C40.1109 20.8956 40.1103 20.8953 40.1097 20.8949L29.665 14.9265L29.6605 14.9239C29.3837 14.7642 29.0698 14.6801 28.7503 14.6801Z"
                                    fill="white" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M14.7095 20.332C15.0318 19.7749 15.7446 19.5846 16.3016 19.9068L28.7498 27.1076L41.198 19.9068C41.7551 19.5846 42.4679 19.7749 42.7901 20.332C43.1123 20.889 42.922 21.6018 42.3649 21.924L29.3333 29.4624C28.9723 29.6712 28.5273 29.6712 28.1664 29.4624L15.1347 21.924C14.5776 21.6018 14.3873 20.889 14.7095 20.332Z"
                                    fill="white" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M28.7512 27.2744C29.3947 27.2744 29.9164 27.7961 29.9164 28.4396V43.4865C29.9164 44.13 29.3947 44.6517 28.7512 44.6517C28.1076 44.6517 27.5859 44.13 27.5859 43.4865V28.4396C27.5859 27.7961 28.1076 27.2744 28.7512 27.2744Z"
                                    fill="white" />
                            </svg>
                            <h5 class="font-bold text-base">Pickup</h5>
                        </div>
                        <div class="relative w-full flex flex-col gap-3 justify-center items-center">
                            <div
                                class="absolute w-[calc(100%_-_40px)] h-[2px] bg-primary top-[26px] left-[-40%] {{ $delivery_status > 1 ? 'bg-rp-red-500' : 'bg-rp-neutral-300' }}">
                            </div>
                            <svg class="z-30"  width="57" height="57"
                                viewBox="0 0 57 57" fill="none">
                                <rect x="0.75" y="0.5" width="56" height="56" rx="8"
                                    fill="{{ $delivery_status >= 2 ? '#FF3D8F' : '#bbc5cd' }}" />
                                <path
                                    d="M44.1506 29.0954L40.8313 21.3506C40.7466 21.1506 40.605 20.98 40.424 20.8599C40.243 20.7399 40.0306 20.6758 39.8135 20.6757H36.4942V18.4628C36.4942 18.1694 36.3777 17.888 36.1702 17.6805C35.9627 17.473 35.6813 17.3564 35.3878 17.3564H14.3662C14.0727 17.3564 13.7913 17.473 13.5838 17.6805C13.3763 17.888 13.2598 18.1694 13.2598 18.4628V37.2717C13.2598 37.5651 13.3763 37.8466 13.5838 38.0541C13.7913 38.2615 14.0727 38.3781 14.3662 38.3781H16.7339C16.9887 39.3156 17.5449 40.1433 18.3167 40.7334C19.0885 41.3234 20.0331 41.6431 21.0046 41.6431C21.9761 41.6431 22.9207 41.3234 23.6925 40.7334C24.4643 40.1433 25.0205 39.3156 25.2753 38.3781H32.2235C32.4783 39.3156 33.0345 40.1433 33.8063 40.7334C34.5782 41.3234 35.5227 41.6431 36.4942 41.6431C37.4658 41.6431 38.4103 41.3234 39.1821 40.7334C39.954 40.1433 40.5102 39.3156 40.765 38.3781H43.1327C43.4261 38.3781 43.7075 38.2615 43.915 38.0541C44.1225 37.8466 44.2391 37.5651 44.2391 37.2717V29.5269C44.2388 29.3786 44.2087 29.2318 44.1506 29.0954ZM36.4942 22.8885H39.0832L41.4509 28.4205H36.4942V22.8885ZM21.0046 39.4845C20.5669 39.4845 20.1391 39.3547 19.7752 39.1116C19.4113 38.8684 19.1277 38.5229 18.9602 38.1185C18.7927 37.7142 18.7489 37.2693 18.8343 36.84C18.9197 36.4108 19.1304 36.0165 19.4399 35.707C19.7494 35.3976 20.1437 35.1868 20.5729 35.1014C21.0021 35.016 21.4471 35.0599 21.8514 35.2273C22.2557 35.3948 22.6013 35.6784 22.8445 36.0423C23.0876 36.4062 23.2174 36.8341 23.2174 37.2717C23.2174 37.8586 22.9843 38.4214 22.5693 38.8364C22.1543 39.2514 21.5915 39.4845 21.0046 39.4845ZM32.2235 36.1653H25.2753C25.0205 35.2278 24.4643 34.4001 23.6925 33.8101C22.9207 33.22 21.9761 32.9003 21.0046 32.9003C20.0331 32.9003 19.0885 33.22 18.3167 33.8101C17.5449 34.4001 16.9887 35.2278 16.7339 36.1653H15.4726V19.5693H34.2814V33.4657C33.7776 33.7581 33.3365 34.1471 32.9834 34.6103C32.6302 35.0736 32.372 35.602 32.2235 36.1653ZM36.4942 39.4845C36.0566 39.4845 35.6288 39.3547 35.2649 39.1116C34.901 38.8684 34.6174 38.5229 34.4499 38.1185C34.2824 37.7142 34.2386 37.2693 34.324 36.84C34.4093 36.4108 34.6201 36.0165 34.9296 35.707C35.239 35.3976 35.6333 35.1868 36.0625 35.1014C36.4918 35.016 36.9367 35.0599 37.341 35.2273C37.7454 35.3948 38.091 35.6784 38.3341 36.0423C38.5773 36.4062 38.707 36.8341 38.707 37.2717C38.707 37.8586 38.4739 38.4214 38.0589 38.8364C37.6439 39.2514 37.0811 39.4845 36.4942 39.4845ZM42.0263 36.1653H40.765C40.5203 35.2177 39.9684 34.3779 39.1957 33.7773C38.4229 33.1768 37.4729 32.8493 36.4942 32.8461V30.6333H42.0263V36.1653Z"
                                    fill="white" />
                            </svg>
                            <div
                                class="absolute w-[calc(100%_-_40px)] h-[2px] bg-primary top-[26px] right-[-40%] {{ $delivery_status >= 2 ? 'bg-rp-red-500' : 'bg-rp-neutral-300' }}">
                            </div>
                            <h5 class="font-bold text-base">Shipping</h5>
                        </div>
                        <div class="w-full flex flex-col gap-3 justify-center items-center">
                            <svg class="z-30"  width="57" height="57"
                                viewBox="0 0 57 57" fill="none">
                                <rect x="0.75" y="0.5" width="56" height="56" rx="8"
                                    fill="{{ $delivery_status >= 3 ? '#FF3D8F' : '#bbc5cd' }}" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M28.0759 14.0079C28.4754 13.7083 29.0247 13.7083 29.4242 14.0079L42.4133 23.7497C43.0765 24.2472 43.4668 25.0278 43.4668 25.8569V40.5829C43.4668 42.0376 42.2876 43.2168 40.8329 43.2168H34.1441C32.6894 43.2168 31.5102 42.0376 31.5102 40.5829V31.5655H25.9899V40.5829C25.9899 42.0376 24.8106 43.2168 23.3559 43.2168H16.6672C15.2125 43.2168 14.0332 42.0376 14.0332 40.5829V25.8569C14.0332 25.0279 14.4235 24.2472 15.0868 23.7497C15.0868 23.7497 15.0868 23.7497 15.0868 23.7497L28.0759 14.0079ZM28.75 16.3113L16.4351 25.5475L16.4351 25.5475C16.3377 25.6205 16.2804 25.7351 16.2804 25.8569V40.5829C16.2804 40.7965 16.4536 40.9696 16.6672 40.9696H23.3559C23.5695 40.9696 23.7426 40.7965 23.7426 40.5829V30.4419C23.7426 29.8213 24.2457 29.3183 24.8662 29.3183H32.6338C33.2543 29.3183 33.7574 29.8213 33.7574 30.4419V40.5829C33.7574 40.7965 33.9305 40.9696 34.1441 40.9696H40.8329C41.0465 40.9696 41.2196 40.7965 41.2196 40.5829V25.8569C41.2196 25.7352 41.1623 25.6205 41.0649 25.5475L28.75 16.3113Z"
                                    fill="white" />
                            </svg>
                            <h5 class="font-bold text-base">Delivery</h5>
                        </div>
                    </div>

                    {{-- ORDER TRACKER DETAILS --}}
                    <div class="flex flex-col gap-8">
                        @foreach ($order_logs as $key => $log)
                            <div class="tracker-dets flex gap-[11px]" wire:key='log-{{ $key }}'>
                                <span
                                    class="text-[11px] {{ !$loop->first ? 'opacity-70' : '' }}">{{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}</span>
                                <div class="relative pl-10 grow">
                                    <svg class="absolute top-0 left-0"  width="23"
                                        height="23" viewBox="0 0 23 23" fill="none">
                                        <circle cx="11.4902" cy="11.3828" r="10.75"
                                            fill="{{ $loop->first ? '#FF3D8F' : '#bbc5cd' }}" />
                                    </svg>
                                    <h5 class="font-bold {{ !$loop->first ? 'opacity-70' : '' }}">
                                        {{ $log->title }}</h5>
                                    <p class="text-[11px] {{ !$loop->first ? 'opacity-70' : '' }}">
                                        {{ $log->description ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- TABLE --}}
                    <table class="w-full border">
                        <tr class="border-b text-left">
                            <th class="font-normal w-1/3 p-[10px] bg-rp-neutral-50">Tracking Number</th>
                            <th class="font-normal w-1/3 p-[10px] bg-rp-neutral-50 border-x">Items</th>
                            <th class="font-normal w-1/3 p-[10px] bg-rp-neutral-50">3PL</th>
                        </tr>
                        <tr class="text-left">
                            <td class="font-normal p-[10px]">{{ $product_order->tracking_number ?? '' }}</td>
                            <td class="font-normal p-[10px] border-x">{{ $product_order->quantity }}</td>
                            <td class="font-normal p-[10px]">{{ $product_order->shipping_option->name }}</td>
                        </tr>
                    </table>

                    {{-- BUTTON --}}
                    <x-button.filled-button @click="$wire.set('show_modal',false)"
                        class="w-40 mx-auto">OK</x-button.filled-button>
                </div>
            </x-modal>
        @endif

        {{-- Cancel Order Modal --}}
        @if ($show_cancel_order_modal and $order_id)
            <livewire:merchant.seller-center.logistics.orders.modals.merchant-cancel-order-modal :merchant="$merchant" :order_id="$order_id" />
        @endif

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

        <x-loader.black-screen wire:loading.block
            wire:target="activeBox,show_order_logs,cancel_order,closeModal,open_cancel_order_modal" class="z-20" />
    </x-main.content>
