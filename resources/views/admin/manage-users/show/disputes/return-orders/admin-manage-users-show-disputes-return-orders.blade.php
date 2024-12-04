<x-main.content class="!px-16 !py-10">

    <livewire:components.layout.admin.user-details-header :user="$user" />

    <x-layout.admin.user-details.disputes.dispute-filter-card-header :user="$user" :returnOrderCount="$this->return_orders_disputes_count"
        :disputesCount="$this->transaction_disputes_count" class="my-8" />

    <x-layout.search-container class="mb-8">
        <x-input.search wire:model.live='searchTerm' icon_position="left" />
    </x-layout.search-container>

    <div class="flex flex-col gap-5">
        @foreach ($return_orders as $key => $return_order)
            <div class="flex flex-col px-4 py-3 bg-white rounded-md w-full text-sm break-words"
                wire:key='return-order-{{ $key }}'>
                <div class="flex flex-row justify-between pb-2 border-b w-full">
                    <div class="flex flex-col max-w-[50%]">
                        <p>Buyer: <span class="text-primary-600">{{ $return_order->product_order->buyer->name }}</span>
                        </p>
                        <p>Merchant: <span
                                class="text-primary-600">{{ $return_order->product_order->product->merchant->name }}</span>
                        </p>
                    </div>
                    <div class="max-w-[50%]">
                        <p>Delivered to buyer on
                            {{ \Carbon\Carbon::parse($return_order->product_order->processed_at)->format('F d, Y') }}
                        </p>
                        <p>Return requested on {{ \Carbon\Carbon::parse($return_order->created_at)->format('F d, Y') }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-row py-3 justify-between break-words">
                    {{-- Order details --}}
                    <div class="flex flex-row gap-3 w-5/12">
                        <div class="flex-[20%]">
                            <img src="{{ $this->get_media_url($return_order->product_order->product->first_image, 'thumbnail') }}"
                                alt="{{ $return_order->product_order->product->name }}">
                        </div>
                        <div class="flex-[80%] overflow-hidden">
                            <h3 class="font-bold text-lg truncate overflow-hidden">
                                {{ $return_order->product_order->product->name }}</h3>
                            {{-- <p class="truncate">Green, Microfiber, Small</p> --}}
                            <div class="flex flex-col mt-3">
                                <div>
                                    <span>Order Number:</span>
                                    <span
                                        class="text-primary-600">{{ $return_order->product_order->order_number }}</span>
                                </div>
                                <div>
                                    <span>Tracking Number:</span>
                                    <span
                                        class="text-primary-600">{{ $return_order->product_order->tracking_number }}</span>
                                </div>
                                <div>
                                    <span>Return Request Number:</span>
                                    <span class="text-primary-600">{{ $return_order->id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="flex flex-col justify-center w-4/12 p-4">
                        <div class="flex flex-col gap-2">
                            <p class="text-rp-neutral-500">Paid Price + Shipping Fee: <span
                                    class="text-rp-neutral-700">P
                                    {{ number_format($return_order->product_order->amount + $return_order->product_order->shipping_fee, 2) }}</span>
                            </p>
                            {{-- <p class="text-rp-neutral-500">Refund Amount: <span class="text-rp-neutral-700">P 10,000</span></p> --}}
                            <p class="text-rp-neutral-500">Return Reason: <span
                                    class="text-rp-neutral-700">{{ $return_order->reason->name }}</span></p>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="flex items-center w-5/12">
                        <div class="flex flex-row justify-between items-center w-full">
                            @if ($return_order->status->name == 'Return Initiated')
                                <x-status color="neutral" class="w-44">Pending Merchant Approval</x-status>
                            @endif

                            @if (
                                $return_order->status->name == 'Return In Progress' ||
                                    $return_order->status->parent_status?->name == 'Return In Progress')
                                <x-status color="yellow" class="w-44">Return in progress</x-status>
                            @endif

                            @if ($return_order->status->name == 'Rejected' || $return_order->status->parent_status?->name == 'Rejected')
                                <x-status color="red" class="w-44">Rejected</x-status>
                            @endif

                            @if ($return_order->status->name == 'Resolved' || $return_order->status->parent_status?->name == 'Resolved')
                                <div class="flex flex-col items-center">
                                    <x-status color="green" class="w-44">Resolved</x-status>
                                    <p class="font-light">{{ $return_order->status->name }}</p>
                                </div>
                            @endif

                            @if (
                                $return_order->status->name == 'Dispute In Progress' ||
                                    $return_order->status->parent_status?->name == 'Dispute In Progress')
                                <div class="flex flex-col items-center">
                                    <x-status color="yellow" class="w-44">Dispute in progress</x-status>
                                    <p class="font-light">{{ $return_order->status->name }}</p>
                                </div>
                            @endif

                            <div class="cursor-pointer">
                                <a
                                    href="{{ route('admin.manage-users.show.disputes.return-orders.details', ['user' => $user, 'returnOrder' => $return_order]) }}"><x-icon.thin-chevron-right /></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

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
                                class="h-full bg-white border-r px-4 py-2 {{ $element == $return_orders->currentPage() ? 'cursor-default' : 'cursor-pointer' }}">{{ $element }}</button>
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

</x-main.content>
