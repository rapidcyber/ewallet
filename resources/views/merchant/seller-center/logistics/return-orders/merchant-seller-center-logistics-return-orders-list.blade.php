<x-main.content>
    <x-main.title class="mb-8">Logistics</x-main.title>
    {{-- Tabs --}}
    <x-tab class="mb-8">
        <x-tab.tab-item href="{{ route('merchant.seller-center.logistics.orders.index', ['merchant' => $merchant]) }}"
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

    {{-- FILTER --}}
    <div class="flex flex-col gap-[22px] mb-[22px]">
        {{-- STATUS --}}
        <div class="relative grid grid-cols-6 gap-[15px]">
            <x-card.filter-card wire:click="$set('activeBox', '')" label="All" :data="$this->count_all" :isActive="$activeBox == ''" />
            <x-card.filter-card wire:click="$set('activeBox', 'return_initiated')" label="Return Initiated"
                :data="$this->count_initiated" :isActive="$activeBox == 'return_initiated'" />
            <x-card.filter-card wire:click="$set('activeBox', 'return_in_progress')" label="Return in progress"
                :data="$this->count_in_progress" :isActive="$activeBox == 'return_in_progress'" />
            <x-card.filter-card wire:click="$set('activeBox', 'rejected')" label="Rejected" :data="$this->count_rejected"
                :isActive="$activeBox == 'rejected'" />
            <x-card.filter-card wire:click="$set('activeBox', 'dispute_in_progress')" label="Dispute in progress"
                :data="$this->count_disputed" :isActive="$activeBox == 'dispute_in_progress'" />
            <x-card.filter-card wire:click="$set('activeBox', 'resolved')" label="Resolved" :data="$this->count_resolved"
                :isActive="$activeBox == 'resolved'" />

        </div>

        {{-- SEARCH --}}
        <div class="relative flex flex-col gap-[20px] p-6 bg-white rounded-xl ">
            {{-- Input Search --}}
            <form wire:submit.prevent='search' class="flex gap-5">
                <x-input.search wire:model='searchTerm' class="flex-1" />
                <div class="flex gap-2">
                    <x-button.filled-button type="submit" class="w-32">Search</x-button.filled-button>
                    <x-button.outline-button type="reset" wire:click='reset_search' class="w-32">Reset</x-button.outline-button>
                </div>
            </form>

            {{-- Dropdown filter --}}
            <div class="grid grid-cols-4 gap-3">
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
                <x-table.rounded.th>Return Details</x-table.rounded.th>
                <x-table.rounded.th>Actions</x-table.rounded.th>
            </x-slot:table_header>
            <x-slot:table_data>
                @foreach ($return_orders as $return_order)
                    <tr>
                        <td class="pt-8"></td>
                    </tr>

                    {{-- Status: Return Initiated --}}
                    <x-table.rounded.row @click="$wire.view_return_order({{ $return_order->id }})" class="cursor-pointer hover:bg-rp-neutral-50">
                        <x-table.rounded.td>
                            <div class="flex gap-[8px] items-center">
                                <div class="w-8 h-8">
                                    @if ($return_order->product_order->buyer->media->isNotEmpty())
                                        <img src="{{ $this->get_media_url($return_order->product_order->buyer->media->first(), 'thumbnail') }}" alt=""
                                            class="w-full h-full object-cover rounded-full">
                                    @else    
                                        <img src="{{ url('images/user/default-avatar.png') }}" alt=""
                                            class="w-full h-full object-cover rounded-full">
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-[13px]">{{ $return_order->product_order->buyer->name }}</span>
                                    <p class="text-[11px]">{{ $this->format_phone_number($return_order->product_order->buyer->phone_number, $return_order->product_order->buyer->phone_iso) }}</p>
                                </div>
                            </div>
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            <div class="flex gap-3">
                                <div class="w-16 h-16">
                                    <img src="{{ $this->get_media_url($return_order->product_order->product->first_image, 'thumbnail') }}" alt=""
                                        class="rounded-md w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <h5 class="text-[13px] font-bold">{{ $return_order->product_order->product->name }}</h5>
                                    {{-- <p class="text-[11px]">Silver, 64GB</p> --}}
                                    <p class="text-[11px]">Order Number:</p>
                                    <p class="text-[11px] text-rp-red-500">{{ $return_order->product_order->order_number }}</p>
                                </div>
                            </div>
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            <div class="text-rp-neutral-600">
                                <h5 class="text-[13px] font-bold">{{ \Number::currency($return_order->product_order->amount * $return_order->product_order->quantity, 'PHP') }}</h5>
                                <p
                                    class="text-[11px] w-fit px-2 py-[2px] text-rp-red-500 border border-rp-red-500 rounded-md">
                                    {{ $return_order->product_order->payment_option->name }}</p>
                                <p class="text-[11px]">Paid Price + Shipping Fee: {{ \Number::currency($return_order->product_order->transaction->amount + $return_order->product_order->transaction->service_fee, 'PHP') }}</p>
                                @if (in_array($return_order->status->slug, ['refunded_only', 'returned_and_refunded']))
                                    <p class="text-[11px]">Refund Amount: {{ \Number::currency($return_order->product_order->transaction->amount + $return_order->product_order->transaction->service_fee, 'PHP') }}</p>
                                @else
                                    <p class="text-[11px]">Refund Amount: 0</p>
                                @endif
                            </div>
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            <div>
                                <h5 class="text-[13px] font-bold">{{ $return_order->status->parent_status?->name ?? $return_order->status->name }}</h5>
                                @if ($return_order->status->name == 'Return Initiated')    
                                    <p class="text-[11px] w-fit px-2 py-[2px] text-rp-red-600 border border-rp-red-600 bg-rp-red-200 rounded-[5px]">
                                        {{ $this->calculate_remaining_hours($return_order->created_at) }}
                                    </p>
                                @elseif ($return_order->status->parent_status?->name == 'Resolved')
                                    <p class="text-[11px] w-fit px-2 py-[2px] text-rp-green-600 border border-rp-green-600 bg-rp-green-200 rounded-[5px]">
                                        {{ $return_order->status->name }}
                                    </p>
                                @else
                                    <p class="text-[11px] w-fit px-2 py-[2px] text-rp-neutral-600 border border-rp-neutral-600 bg-rp-neutral-200 rounded-[5px]">
                                        {{ $return_order->status->name }}
                                    </p>
                                @endif
                                <p class="text-[11px]">Return Order Number: <span
                                        class="text-rp-red-500">{{ $return_order->id }}</span></p>
                                <p class="text-[11px]">Return Reason: {{ $return_order->reason->name }}</p>
                            </div>
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            <div class="flex flex-col gap-[6px]">
                                @if ($return_order->status->parent_status?->name == 'Dispute In Progress')
                                    @if ($return_order->status->name == 'Pending Response')
                                        <x-button.filled-button @click.stop="$wire.open_modal({{ $return_order->id }}, 'respond')">Respond</x-button.filled-button>
                                    @endif
                                    <x-button.outline-button @click.stop="$wire.open_modal({{ $return_order->id }}, 'logistics_status')">
                                        Logistics Status
                                    </x-button.outline-button>
                                    @if ($return_order->status->name == 'Pending Resolution')
                                        <x-button.outline-button @click.stop="$wire.open_modal({{ $return_order->id }}, 'view_response')">View
                                            Response</x-button.outline-button>
                                    @endif
                                @elseif ($return_order->status->parent_status?->name == 'Resolved')
                                    <x-button.outline-button @click.stop="$wire.open_modal({{ $return_order->id }}, 'logistics_status')">Logistics
                                        Status</x-button.outline-button>
                                    <x-button.outline-button @click.stop="$wire.open_modal({{ $return_order->id }}, 'view_resolution')">View
                                        Resolution</x-button.outline-button>
                                @else
                                    <x-button.outline-button @click.stop="$wire.open_modal({{ $return_order->id }}, 'logistics_status')">Logistics
                                        Status</x-button.outline-button>
                                    <div class="relative w-full" x-data="{ drop: false }">
                                        <x-button.outline-button @click.stop="drop = !drop" class="w-full">More
                                            Action</x-button.outline-button>
                                        <x-dropdown.dropdown-list x-cloak x-show="drop"
                                            class="absolute bg-white top-[100%] right-0 w-28" @click.stop.outside="drop = false">
                                            @if ($return_order->status->name == 'Return Initiated')    
                                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'refund')">
                                                    Refund Only
                                                </x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'return_refund')">
                                                    Return & Refund
                                                </x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'reject_request')">
                                                    Reject
                                                </x-dropdown.dropdown-list.item>
                                            @elseif ($return_order->status->name == 'Pending Return')
                                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'process_refund')">
                                                    Process Refund
                                                </x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'reject_request_after_return')">
                                                    Reject
                                                </x-dropdown.dropdown-list.item>
                                            @elseif ($return_order->status->parent_status?->name == 'Rejected')
                                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'return_refund')">
                                                    Return & Refund
                                                </x-dropdown.dropdown-list.item>
                                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'refund')">
                                                    Refund Only
                                                </x-dropdown.dropdown-list.item>
                                            @endif
                                        </x-dropdown.dropdown-list>
                                    </div>
                                @endif
                            </div>
                        </x-table.rounded.td>
                    </x-table.rounded.row>
                @endforeach
            </x-slot:table_data>
        </x-table.rounded>

        {{-- Pagination --}}
        <div class="flex items-center justify-center w-full gap-8">
            @if ($return_orders->hasPages())
                <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                    <button wire:click="previousPage" {{ $return_orders->onFirstPage() ? 'disabled' : '' }}
                        class="{{ $return_orders->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                                class="h-full border-r px-4 py-2 {{ $element == $return_orders->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                        @endif
                    @endforeach

                    <button wire:click="nextPage" {{ !$return_orders->hasMorePages() ? 'disabled' : '' }}
                        class="{{ !$return_orders->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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


    {{-- MODALS START HERE --}}
    {{-- MODAL - REFUND ONLY --}}
    <x-modal x-model="$wire.showRefundModal">
        @if ($showRefundModal && $return_order_id)
            <livewire:merchant.seller-center.logistics.return-orders.modals.refund-modal :merchant="$merchant" :return_order_id="$return_order_id" />
        @endif
    </x-modal>

    {{-- MODAL - RETURN AND REFUND --}}
    <x-modal x-model="$wire.showReturnRefundModal">
        @if ($showReturnRefundModal && $return_order_id)
            <livewire:merchant.seller-center.logistics.return-orders.modals.return-refund-modal :merchant="$merchant" :return_order_id="$return_order_id" />
        @endif
    </x-modal>

    {{-- MODAL - REJECT REQUEST --}}
    <x-modal x-model="$wire.showRejectRequestModal">
        @if ($showRejectRequestModal && $return_order_id)
            <livewire:merchant.seller-center.logistics.return-orders.modals.reject-request-modal :merchant="$merchant" :return_order_id="$return_order_id" />
        @endif
    </x-modal>

    {{-- MODAL - PROCESS REFUND --}}
    <x-modal x-model="$wire.showProcessRefundModal">
        @if ($showProcessRefundModal && $return_order_id)
            <livewire:merchant.seller-center.logistics.return-orders.modals.process-refund-modal :merchant="$merchant" :return_order_id="$return_order_id" />
        @endif
    </x-modal>

    {{-- MODAL - REJECT REQUEST AFTER RETURN --}}
    <x-modal x-model="$wire.showRejectRequestAfterReturnModal">
        @if ($showRejectRequestAfterReturnModal && $return_order_id)
            <livewire:merchant.seller-center.logistics.return-orders.modals.reject-request-after-return-modal :merchant="$merchant" :return_order_id="$return_order_id" />
        @endif
    </x-modal>

    {{-- MODAL - RESPOND --}}
    <x-modal x-model="$wire.showRespondModal">
        @if ($showRespondModal && $return_order_id)
            <livewire:merchant.seller-center.logistics.return-orders.modals.respond-modal :merchant="$merchant" :return_order_id="$return_order_id" />
        @endif
    </x-modal>

    {{-- MODAL - VIEW RESPONSE --}}
    <x-modal x-model="$wire.showViewResponseModal">
        @if ($showViewResponseModal && $return_order_id)
            <livewire:merchant.seller-center.logistics.return-orders.modals.view-response-modal :merchant="$merchant" :return_order_id="$return_order_id" />
        @endif
    </x-modal>

    {{-- MODAL - VIEW RESOLUTION --}}
    <x-modal x-model="$wire.showViewResolutionModal" x-data="{ hasDisputes: false }">
        @if ($showViewResolutionModal && $return_order_id)
            <livewire:merchant.seller-center.logistics.return-orders.modals.view-resolution-modal :merchant="$merchant" :return_order_id="$return_order_id" />
        @endif
    </x-modal>

    {{-- MODAL - LOGISTICS STATUS --}}
    <x-modal x-model="$wire.showLogisticsStatusModal">
        @if ($showLogisticsStatusModal && $return_order_id)
            <livewire:merchant.seller-center.logistics.return-orders.modals.logistics-status-modal :merchant="$merchant" :return_order_id="$return_order_id" />
        @endif
    </x-modal>

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

    <x-loader.black-screen wire:loading.block wire:target="activeBox,open_modal,closeModal,search,reset_search,successModal" class="z-10"/>
</x-main.content>