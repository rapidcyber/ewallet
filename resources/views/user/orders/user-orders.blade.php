<x-main.content x-data="{
    isWriteReviewModalVisible: $wire.entangle('is_write_review_modal_visible'),
    isRequestReturnModalVisible: $wire.entangle('is_request_return_modal_visible')
}">
    <x-main.title class="mb-8">Orders</x-main.title>

    <div class="grid grid-cols-5 gap-[15px] mb-8">
        <x-card.filter-card wire:click="$set('activeBox', '')" label="All" :data="$this->count_all" :isActive="$activeBox === ''" />
        <x-card.filter-card wire:click="$set('activeBox', 'to-ship')" label="To Ship" :data="$this->count_to_ship"
            :isActive="$activeBox === 'to-ship'" />
        <x-card.filter-card wire:click="$set('activeBox', 'to-receive')" label="To Receive" :data="$this->count_to_receive"
            :isActive="$activeBox === 'to-receive'" />
        <x-card.filter-card wire:click="$set('activeBox', 'received')" label="Received" :data="$this->count_received"
            :isActive="$activeBox === 'received'" />
        <x-card.filter-card wire:click="$set('activeBox', 'cancellation')" label="Cancellation" :data="$this->count_cancellation"
            :isActive="$activeBox === 'cancellation'" />
    </div>

    <x-layout.search-container class="mb-8">
        <x-input.search wire:model.live.debounce.300ms='searchTerm' icon_position="left" />
    </x-layout.search-container>

    {{-- ORDER LIST --}}
    <div class="flex flex-col gap-3 w-full">
        @foreach ($orders as $key => $order)
            <div role="button" tabindex="0" @keyup.enter="$wire.open_order_show({{$order->id}})" wire:click.stop="open_order_show('{{ $order->order_number }}')"
                class="flex flex-col px-4 py-3 bg-white rounded-lg w-full text-sm hover:bg-gray-50 cursor-pointer" wire:key='order-{{ $key }}'>
                <div class="flex flex-row justify-between pb-2 border-b items-center ">
                    <div class="flex flex-row gap-2 items-center">
                        <div class="w-8 h-8">
                            @if ($order->product->merchant->logo)
                                <img src="{{ $this->get_media_url($order->product->merchant->logo, 'thumbnail') }}"
                                    class="w-full h-full object-cover rounded-full"
                                    alt="{{ $order->product->merchant->name . ' Logo' }}" />
                            @else
                                <img src="{{ url('/images/user/default-avatar.png') }}"
                                    alt="w-full h-full object-cover rounded-full" alt="Default Logo">
                            @endif

                        </div>
                        <p class="text-rp-red-500">{{ $order->product->merchant->name }}</p>
                        <div>
                            <x-icon.message />
                        </div>
                        <p>({{ $order->quantity }} item)</p>
                    </div>
                    <div class="">
                        @if ($order->shipping_status->slug == 'completed')
                            <p>Delivered {{ \Carbon\Carbon::parse($order->processed_at)->timezone('Asia/Manila')->format('F j, Y') }}</p>
                        @elseif ($order->shipping_status->slug == 'cancellation')
                            <p>Cancel requested on {{ \Carbon\Carbon::parse($order->processed_at)->timezone('Asia/Manila')->format('F j, Y') }}
                            </p>
                        @elseif ($order->shipping_status->slug == 'failed_delivery')
                            <p>Delivery failed {{ \Carbon\Carbon::parse($order->processed_at)->timezone('Asia/Manila')->format('F j, Y') }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex flex-row py-3 justify-between items-center break-words">
                    {{-- Order details --}}
                    <div class="flex flex-row gap-3 w-5/12">
                        <div class="w-[70px] h-[70px]">
                            <img src="{{ $this->get_media_url($order->product->first_image, 'thumbnail') }}"
                                alt="{{ $order->product->name }}" class="w-full h-full object-cover" />
                        </div>
                        <div class="overflow-hidden flex flex-col justify-between">
                            <h3 class="font-bold text-rp-neutral-700 text-lg truncate overflow-hidden">
                                {{ $order->product->name }}</h3>
                            {{-- <p class="truncate">Green, Microfiber, Small</p> --}}
                            <div class="flex flex-col">
                                <div>
                                    <span>Order Number:</span>
                                    <span class="text-rp-red-500">{{ $order->order_number }}</span>
                                </div>
                                <div>
                                    <span>Tracking Number:</span>
                                    <span class="text-rp-red-500">{{ $order->tracking_number }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Total --}}
                    <div class="flex flex-col justify-center w-3/12 break-words px-2">
                        <p>Total: {{ \Number::currency($order->amount, 'PHP') }}</p>
                        <p class="text-rp-red-500">x {{ $order->quantity }}</p>
                    </div>
                    {{-- Status --}}
                    <div class="flex flex-col justify-center w-2/12 px-2">
                        @switch($order->shipping_status->name)
                            @case('Pending')
                            @case('Packed')

                            @case('Ready to Ship')
                            @case('To Ship')
                                <x-status color="neutral" class="w-28">{{ $order->shipping_status->name }}</x-status>
                            @break

                            @case('Shipping')
                                <x-status color="yellow" class="w-28">{{ $order->shipping_status->name }}</x-status>
                            @break

                            @case('Completed')
                                <x-status color="green" class="w-28">Delivered</x-status>
                            @break

                            @case('Cancellation')
                                @if ($order->termination_reason)
                                    <p class="text-rp-neutral-500">Cancel Reason:</p>
                                    <p class="text-rp-neutral-700">{{ $order->termination_reason }}</p>
                                @endif
                                <x-status color="red" class="w-28">Cancelled</x-status>
                            @break

                            @case('Failed Delivery')
                                @if ($order->termination_reason)
                                    <p class="text-rp-neutral-500">Failed Reason:</p>
                                    <p class="text-rp-neutral-700">{{ $order->termination_reason }}</p>
                                @endif
                                <x-status color="red" class="w-28">Failed Delivery</x-status>
                            @break

                            @default
                                <x-status color="neutral" class="w-28">{{ $order->shipping_status->name }}</x-status>
                        @endswitch
                    </div>
                    {{-- Actions --}}
                    <div class="flex flex-col gap-3 justify-center w-2/12 px-2">
                        @switch($order->shipping_status->name)
                            @case('Pending')
                            @case('Packed')
                            @case('Ready to Ship')
                            @case('To Ship')
                                <x-button.filled-button wire:click.stop="open_cancel_order_modal('{{ $order->order_number }}')"
                                    size="md">cancel</x-button.filled-button>
                            @break

                            @case('Completed')
                                @if (isset($order->is_reviewed))
                                    <x-button.filled-button disabled wire:click.stop class="cursor-not-allowed"
                                        size="md">write a review</x-button.filled-button>
                                @else
                                    <x-button.filled-button wire:click.stop="open_review_modal('{{ $order->order_number }}')"
                                        size="md">write a review</x-button.filled-button>
                                @endif

                                @if ($order->return_orders_count > 0)
                                    <x-button.filled-button disabled wire:click.stop class="cursor-not-allowed"
                                        size="md">request return</x-button.filled-button>
                                @else
                                    <x-button.outline-button wire:click.stop="open_return_modal('{{ $order->order_number }}')"
                                        size="md">request return</x-button.outline-button>
                                @endif
                            @break

                            @default
                        @endswitch

                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="w-full flex items-center justify-center gap-8">
        @if ($orders->hasPages())
            <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                <button wire:click="previousPage" {{ $orders->onFirstPage() ? 'disabled' : '' }}
                    class="{{ $orders->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                        <button class="h-full bg-white border-r px-4 py-2 cursor-default">{{ $element }}</button>
                    @else
                        <button wire:click="gotoPage({{ $element }})"
                            class="h-full border-r px-4 py-2 {{ $element == $orders->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                    @endif
                @endforeach

                <button wire:click="nextPage" {{ !$orders->hasMorePages() ? 'disabled' : '' }}
                    class="{{ !$orders->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    {{-- ------------ MODAL STARTS HERE -------------- --}}
    {{-- REVIEW PRODUCT --}}
    @if ($order_review_id)
        <livewire:user.orders.user-orders-review-modal :order_review_id="$order_review_id" />
    @endif

    @if ($order_return_id)
        <livewire:user.orders.user-orders-return-modal :order_return_id="$order_return_id" />
    @endif

    {{-- CANCEL ORDER MODAL --}}
    @if ($order_cancel_id)
        <livewire:user.orders.modals.user-cancel-order-modal :order_id="$order_cancel_id" />
    @endif

    <x-loader.black-screen wire:loading.flex wire:target="activeBox,open_review_modal,open_return_modal,open_cancel_order_modal">
        <x-loader.clock />
    </x-loader.black-screen>

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
</x-main.content>
