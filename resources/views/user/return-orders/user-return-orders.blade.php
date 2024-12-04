<x-main.content x-data="{
    confirmationModal: {
        visible: $wire.entangle('confirmationModalVisible'),
    },
    show_modal: $wire.entangle('show_modal').live,
}">
    <x-main.title class="mb-8">Return Orders</x-main.title>

    <div class="relative grid grid-cols-6 gap-[15px] mb-8 mt-5">
        <x-card.filter-card wire:click="$set('activeBox', '')" label="All" :data="$this->count_all" :isActive="$this->activeBox === ''"
            color="red" />
        <x-card.filter-card wire:click="$set('activeBox', 'pending')" label="Pending Approval" :data="$this->count_pending"
            :isActive="$this->activeBox === 'pending'" color="red" />
        <x-card.filter-card wire:click="$set('activeBox', 'returning')" label="Return in Progress" :data="$this->count_return"
            :isActive="$this->activeBox === 'returning'" color="red" />
        <x-card.filter-card wire:click="$set('activeBox', 'rejected')" label="Rejected" :data="$this->count_rejected"
            :isActive="$this->activeBox === 'rejected'" color="red" />
        <x-card.filter-card wire:click="$set('activeBox', 'resolved')" label="Resolved" :data="$this->count_resolved"
            :isActive="$this->activeBox === 'resolved'" color="red" />
        <x-card.filter-card wire:click="$set('activeBox', 'disputed')" label="Dispute in Progress" :data="$this->count_disputed"
            :isActive="$this->activeBox === 'disputed'" color="red" />
    </div>

    <div class="mt-8 space-y-8 w-full">
        <div class="p-5 bg-white rounded-lg">
            <x-input.search wire:model.live='searchTerm' icon_position="left" />
        </div>

        {{-- TABLE --}}
        <div class="w-full overflow-x-auto">
            <div class="flex flex-col gap-3 w-full">
                @foreach ($return_orders as $return_order)
                    <div role="button" tabindex="0" @keyup.enter="$wire.open_return_orders_show({{ $return_order->id }})" wire:click.stop='open_return_orders_show({{ $return_order->id }})' @click.stop=""
                        class="flex flex-col px-4 py-3 bg-white rounded-md w-full text-sm hover:bg-gray-100 cursor-pointer"
                        wire:key='return-order-{{ $return_order->id }}'>
                        <div class="flex flex-row justify-between pb-2 border-b ">
                            <div class="flex flex-row gap-2 items-center">
                                <div class="w-8 h-8">
                                    @if ($logo = $return_order->product_order->product->merchant->logo)
                                        <img class="w-full h-full rounded-full object-cover"
                                            src="{{ $this->get_media_url($logo, 'thumbnail') }}" />
                                    @else
                                        <img class="w-full h-full rounded-full object-cover"
                                            src="{{ url('/images/user/default-avatar.png') }}" />
                                    @endif
                                </div>
                                <div class="text-rp-red-600">
                                    {{ $return_order->product_order->product->merchant->name }}
                                </div>
                                <div>
                                    <img src="/images/guest/message2.svg" />
                                </div>
                                <p>({{ $return_order->product_order->quantity }} Item)</p>
                            </div>
                            <div>
                                @if ($return_order->product_order->processed_at != null)
                                    <p>Delivered
                                        {{ \Carbon\Carbon::parse($return_order->product_order->processed_at)->timezone('Asia/Manila')->format('F j, Y') }}
                                    </p>
                                @endif
                                <p>Return requested on
                                    {{ \Carbon\Carbon::parse($return_order->created_at)->timezone('Asia/Manila')->format('F j, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex flex-row py-3 justify-between items-center break-words">
                            {{-- Order details --}}
                            <div class="flex flex-row gap-3 w-5/12">
                                <div class="w-[70px] h-[70px]">
                                    <img class="w-full h-full object-cover" alt="{{ $return_order->product_order->product->name }}"
                                        src="{{ $this->get_media_url($return_order->product_order->product->first_image, 'thumbnail') }}" />
                                </div>
                                <div class="overflow-hidden flex flex-col justify-between">
                                    <h3 class="font-bold text-rp-neutral-700 text-lg truncate overflow-hidden">
                                        {{ $return_order->product_order->product->name }}</h3>
                                    {{-- <p class="truncate">Green, Microfiber, Small</p> --}}
                                    <div class="flex flex-col">
                                        <div>
                                            <span>Order Number:</span>
                                            <span
                                                class="text-rp-red-600">{{ $return_order->product_order->order_number }}</span>
                                        </div>
                                        <div>
                                            <span>Tracking Number:</span>
                                            <span
                                                class="text-rp-red-600">{{ $return_order->product_order->tracking_number }}</span>
                                        </div>
                                        <div>
                                            <span>Return Request Number:</span>
                                            <span class="text-rp-red-600">{{ $return_order->id }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- Total --}}
                            <div class="flex flex-col justify-center w-3/12 p-4">
                                <div class="flex flex-col gap-2">
                                    <div class="flex flex-row justify-between gap-1">
                                        <p class="font-semibold">Paid Price + Shipping Fee:</p>
                                        <p>{{ \Number::currency(($return_order->product_order->amount * $return_order->product_order->quantity) + $return_order->product_order->shipping_fee, 'PHP') }}</p>
                                    </div>
                                    {{-- <div class="flex flex-row justify-between gap-1">
                                        <p class="font-semibold">Refund Amount:</p>
                                        <p>P 10,000</p>
                                    </div> --}}
                                    <div class="flex flex-row justify-between gap-1">
                                        <p class="font-semibold">Return Reason:</p>
                                        <p>{{ $return_order->reason->name }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Status --}}
                            <div class="flex flex-col justify-center items-center w-2/12">
                                <div class="flex flex-col justify-center items-center">
                                    @if ($return_order->status->name == 'Return Initiated')
                                        <x-status color="neutral" class="w-24">Pending Approval</x-status>
                                    @endif

                                    @if (
                                        $return_order->status->name == 'Return In Progress' ||
                                            $return_order->status->parent_status?->name == 'Return In Progress')
                                        <x-status color="yellow" class="w-24">Return in progress</x-status>
                                    @endif

                                    @if ($return_order->status->name == 'Rejected' || $return_order->status->parent_status?->name == 'Rejected')
                                        <x-status color="red" class="w-24">Rejected</x-status>
                                    @endif

                                    @if ($return_order->status->name == 'Resolved' || $return_order->status->parent_status?->name == 'Resolved')
                                        <x-status color="green" class="w-24">Resolved</x-status>
                                        <p class="font-light">{{ $return_order->status->name }}</p>
                                    @endif

                                    @if (
                                        $return_order->status->name == 'Dispute In Progress' ||
                                            $return_order->status->parent_status?->name == 'Dispute In Progress')
                                        <x-status color="yellow" class="w-24">Dispute in progress</x-status>
                                        <p class="font-light">{{ $return_order->status->name }}</p>
                                    @endif
                                </div>

                            </div>

                            {{-- Actions --}}
                            <div class="flex flex-col gap-3 justify-center items-center w-2/12 p-4">
                                @if ($return_order->status->name == 'Rejected' || $return_order->status->parent_status?->name == 'Rejected')
                                    <x-button.filled-button
                                        wire:click.stop='open_dispute_modal({{ $return_order->id }})' color="red"
                                        :disabled="false" class="w-full max-w-44">
                                        File a Dispute
                                    </x-button.filled-button>
                                @endif

                                @if (
                                    $return_order->status->name == 'Return Initiated' ||
                                        $return_order->status->name == 'Rejected' ||
                                        $return_order->status->parent_status?->name == 'Rejected')
                                    <x-button.outline-button
                                        wire:click.stop='open_cancel_request_modal({{ $return_order->id }})'
                                        color="red" class="w-full max-w-44">
                                        Cancel Request
                                    </x-button.outline-button>
                                @endif
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="w-full flex items-center justify-center gap-8">
        @if ($return_orders->hasPages())
            <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                <button wire:click="previousPage" {{ $return_orders->onFirstPage() ? 'disabled' : '' }}
                    class="{{ $return_orders->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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

    @if ($show_cancel_request_modal && $cancel_request_id)
        <x-modal x-model="$wire.show_cancel_request_modal">
            <x-modal.confirmation-modal title="Cancel Request?">
                <x-slot:message>
                    This action will treat the return request as resolved.
                </x-slot:message>
                <x-slot:action_buttons>
                    <x-button.outline-button wire:target='cancel_request' wire:loading.attr='disabled'
                        wire:loading.class='cursor-progress' wire:click="closeModal" class="w-1/2">Go
                        Back</x-button.outline-button>
                    <x-button.filled-button wire:target='cancel_request' wire:loading.attr='disabled'
                        wire:loading.class='cursor-progress' wire:click='cancel_request' class="w-1/2">
                        Proceed
                    </x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.confirmation-modal>
        </x-modal>
    @endif


    @if ($show_modal && $return_order_id)
        <livewire:user.return-orders.user-return-orders-disputes-modal :return_order_id="$return_order_id" />
    @endif

    <x-loader.black-screen wire:loading wire:target="activeBox,open_dispute_modal,open_cancel_request_modal">
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
