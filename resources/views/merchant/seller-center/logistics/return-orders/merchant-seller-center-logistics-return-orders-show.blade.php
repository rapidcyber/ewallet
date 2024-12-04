@push('style')
    <style>
        .tracker-dets:not(:first-child) span,
        .tracker-dets:not(:first-child) h5,
        .tracker-dets:not(:first-child) p {
            color: #BBC5CD;
        }

        .tracker-dets:not(:first-child) svg circle {
            fill: #BBC5CD;
        }
    </style>
@endpush

<x-main.content>
    {{-- HEAD --}}
    <x-main.action-header>
        <x-slot:title>Return Order Details</x-slot:title>
        <x-slot:actions>
            @if ($return_order->status->parent_status)
                @switch($return_order->status->parent_status->slug)
                    @case('return_in_progress')
                        <div class="relative" x-data="{ drop: false }">
                            <x-button.outline-button @click="drop = !drop">More Action</x-button.outline-button>
                            <x-dropdown.dropdown-list x-cloak x-show="drop" class="absolute top-[100%] right-0 w-36"
                                @click.outside="drop = false">
                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'process_refund')">Process Refund</x-dropdown.dropdown-list.item>
                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'reject_request_after_return')">Reject</x-dropdown.dropdown-list.item>
                            </x-dropdown.dropdown-list>
                        </div>
                    @break

                    @case('rejected')
                        <div class="relative" x-data="{ drop: false }">
                            <x-button.outline-button @click="drop = !drop">More Action</x-button.outline-button>
                            <x-dropdown.dropdown-list x-cloak x-show="drop" class="absolute top-[100%] right-0 w-36"
                                @click.outside="drop = false">
                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'return_refund')">Return & Refund</x-dropdown.dropdown-list.item>
                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'refund')">Refund Only</x-dropdown.dropdown-list.item>
                            </x-dropdown.dropdown-list>
                        </div>
                    @break

                    @case('dispute_in_progress')
                        @switch($return_order->status->slug)
                            @case('pending_response')
                                <x-button.filled-button @click.stop="$wire.open_modal({{ $return_order->id }}, 'respond')">Respond</x-button.filled-button>
                            @break

                            @case('pending_resolution')
                                <x-button.outline-button @click.stop="$wire.open_modal({{ $return_order->id }}, 'view_response')">view response</x-button.outline-button>
                            @break
                        @endswitch
                    @break

                    @case('resolved')
                        <x-button.outline-button @click.stop="$wire.open_modal({{ $return_order->id }}, 'view_resolution')">view resolution</x-button.outline-button>
                    @break

                @endswitch
            @else
                @switch($return_order->status->slug)
                    @case('return_initiated')
                        <div class="relative" x-data="{ drop: false }">
                            <x-button.outline-button @click="drop = !drop">More Action</x-button.outline-button>
                            <x-dropdown.dropdown-list x-cloak x-show="drop" class="absolute top-[100%] right-0 w-36"
                                @click.outside="drop = false">
                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'refund')">Refund Only</x-dropdown.dropdown-list.item>
                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'return_refund')">Return & Refund</x-dropdown.dropdown-list.item>
                                <x-dropdown.dropdown-list.item @click.stop="$wire.open_modal({{ $return_order->id }}, 'reject_request')">Reject</x-dropdown.dropdown-list.item>
                            </x-dropdown.dropdown-list>
                        </div>
                    @break
                @endswitch
            @endif
        </x-slot:actions>
    </x-main.action-header>

    <div class="grid grid-cols-2 gap-[30px]">
        {{-- 1st Column --}}
        <div class="flex flex-col gap-[30px]">
            {{-- customer information --}}
            <div class="px-5 py-6 break-words bg-white rounded-lg">
                <h2 class="text-rp-neutral-700 font-bold text-[19px] mb-5">Customer Information</h2>
                <div class="flex items-center justify-between gap-2 text-sm 2xl:text-base">
                    <div class="flex items-center w-1/3 gap-2 px-1">
                        <div class="h-10 min-w-10">
                            <img src="{{ $return_order->product_order->buyer->profile_picture ? $this->get_media_url($return_order->product_order->buyer->profile_picture, 'thumbnail') : url('images/user/default-avatar.png') }}"
                                alt="" class="object-cover w-full h-full rounded-full">
                        </div>
                        <div class="w-[calc(100%-40px)]">
                            <p>{{ $this->return_order->product_order->buyer->name }}</p>
                        </div>
                    </div>
                    <div class="w-1/3 px-1">
                        <p>Phone Number:</p>
                        <p class="text-rp-red-500">
                            @php
                                $phone_number = $return_order->product_order->buyer->phone_number;
                                $formatted_phone_number = sprintf(
                                    '(+%s) %s-%s-%s',
                                    substr($phone_number, 0, 2),
                                    substr($phone_number, 2, 3),
                                    substr($phone_number, 5, 3),
                                    substr($phone_number, 8),
                                );
                            @endphp
                            {{ $formatted_phone_number }}
                        </p>
                    </div>
                    <div class="w-1/3 px-1">
                        <p>Email:</p>
                        <p class="text-rp-red-500">{{ $return_order->product_order->buyer->email }}</p>
                    </div>
                </div>
            </div>
            {{-- Order Information --}}
            <div class="flex flex-col gap-5 px-5 py-6 break-words bg-white rounded-lg">
                <h2 class="text-rp-neutral-700 font-bold text-[19px]">Order Information</h2>
                <div class="flex gap-3">
                    <div class="w-16 h-16">
                        <img src="{{ $this->get_media_url($return_order->product_order->product->first_image, 'thumbnail') }}"
                            alt="" class="object-cover w-full h-full rounded-md">
                    </div>
                    <div class="flex-1 text-sm text-rp-neutral-600 2xl:text-base">
                        <h5 class="font-bold">{{ $return_order->product_order->product->name }}</h5>
                        {{-- <p class="">{{ $return_order->product_order->product->description }}</p> --}}
                    </div>
                </div>
                <div class="flex justify-between w-full gap-1">
                    <div class="w-1/6 text-sm 2xl:text-base">
                        <p>Quantity:</p>
                        <p class="text-rp-red-500">{{ $return_order->product_order->quantity . 'x' }}</p>
                    </div>
                    <div class="w-1/6 text-sm 2xl:text-base">
                        <p>Product Price:</p>
                        <p class="text-rp-red-500">
                            ₱ <span>{{ number_format($return_order->product_order->amount, 2) }}</span></p>
                    </div>
                    <div class="w-2/6 text-sm 2xl:text-base">
                        <p>Order Number:</p>
                        <p class="text-rp-red-500">{{ $return_order->product_order->order_number }}</p>
                    </div>
                    <div class="w-1/5 text-sm 2xl:text-base">
                        <p>Order Date:</p>
                        <p class="text-rp-red-500">
                            {{ \Carbon\Carbon::parse($return_order->product_order->created_at)->timezone('Asia/Manila')->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Return Request History --}}
            <x-card.return-request-history :logs="$return_order->logs" />

            {{-- Tracking Details --}}
            <x-card.tracking-details :logs="$return_order->product_order->logs" :delivery_partner="$return_order->product_order->shipping_option->name" :tracking_number="$return_order->product_order->tracking_number" :delivery_status="$return_order->product_order->shipping_status->name" />
        </div>

        {{-- 2nd Column --}}
        <div class="flex flex-col gap-[30px]">
            {{-- Return Details --}}
            <div class="col-start-1 p-6 break-words bg-white rounded-lg">
                <h2 class="text-rp-neutral-700 font-bold text-[19px] mb-8">Return Details</h2>
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between">
                        <p class="w-1/2">Return Reason:</p>
                        <p class="w-1/2 font-bold text-right text-rp-neutral-700">{{ $return_order->reason->name }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p class="w-1/2">Status:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold capitalize">
                            {{ $return_order->status->parent_status?->name ?? $return_order->status->name }}
                        </p>
                    </div>
                    <div class="flex justify-between">
                        <p class="w-1/2">Action Status:</p>
                        @if ($return_order->status->name == 'Return Initiated')
                            <x-status color="red" class="w-44">
                                <p>{{ $this->calculate_remaining_hours($return_order->created_at) }}</p>
                            </x-status>
                        @else
                            @switch($return_order->status->parent_status->slug)
                                @case('return_in_progress')
                                @case('dispute_in_progress')
                                @case('rejected')
                                    <div class="flex flex-col items-center">
                                        <x-status color="neutral"
                                            class="w-44">{{ $return_order->status->name }}</x-status>
                                    </div>
                                @break

                                @case('resolved')
                                    <div class="flex flex-col items-center">
                                        <x-status color="green"
                                            class="w-44">{{ $return_order->status->name }}</x-status>
                                    </div>
                                @break
                            @endswitch
                        @endif
                    </div>
                    <div class="flex justify-between">
                        <p class="w-1/2">Return Order Number:</p>
                        <p class="w-1/2 font-bold text-right text-rp-neutral-700">{{ $return_order->id }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p class="w-1/2">Return Order Date:</p>
                        <p class="w-1/2 font-bold text-right text-rp-neutral-700">
                            {{ \Carbon\Carbon::parse($return_order->created_at)->timezone('Asia/Manila')->format('F d, Y') }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p class="w-1/2">Comments:</p>
                        <p class="w-1/2 font-bold text-right text-rp-neutral-700">{{ $return_order->comment }}</p>
                    </div>
                    <div class="flex flex-col">
                        <p class="flex">Attached Images ({{ $return_order->media->count() }})</p>
                        <div class="flex flex-row flex-wrap gap-2">
                            @foreach ($return_order->media as $key => $media)
                                <div class="h-28 max-h-28 w-36 max-w-36" wire:key='return-order-media-{{ $key }}'>
                                    <img src="{{ $this->get_media_url($media) }}" 
                                        class="object-cover w-full h-full rounded-md"
                                        alt="{{ $media->name }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Buyer's Dispute Details --}}
            @if ($return_order->dispute)
                <div class="col-start-1 bg-white rounded-lg p-7">
                    <h2 class="text-rp-neutral-700 font-bold text-[19px] mb-5">Buyer's Dispute Details</h2>
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between">
                            <p class="w-1/2">Comments:</p>
                            <p class="w-1/2 font-bold text-right break-words text-rp-neutral-700">{{ $return_order->dispute->comment }}</p>
                        </div>
                        <div class="flex flex-col">
                            <p class="flex">Attached Images ({{ $return_order->dispute->media->count() }})</p>
                            <div class="flex flex-row flex-wrap gap-2">
                                @foreach ($return_order->dispute->media as $key => $media)
                                    <div class="h-28 max-h-28 w-36 max-w-36" wire:key='return-order-dispute-media-{{ $key }}'>
                                        <img src="{{ $this->get_media_url($media) }}" 
                                            class="object-cover w-full h-full rounded-md"
                                            alt="{{ $media->name }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- delivery details --}}
            <div class="col-start-1 bg-white rounded-lg p-7">
                <h2 class="text-rp-neutral-700 font-bold text-[19px] mb-5">Delivery Details</h2>
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between">
                        <p>Delivery Mode:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                            {{ ucwords(str_replace('_', ' ', $return_order->product_order->delivery_type)) }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p>Delivery date:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                            {{ \Carbon\Carbon::parse($return_order->product_order->processed_at)->timezone('Asia/Manila')->format('F d, Y') }}
                        </p>
                    </div>
                    <div class="flex justify-between">
                        <p>Warehouse:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                            {{ $return_order->product_order->warehouse->name }}'s</p>
                    </div>
                    <div class="flex justify-between">
                        <p>Address:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                            {{ $return_order->product_order->location->address }}</p>
                    </div>
                </div>
            </div>
            {{-- Refund and Payment Information --}}
            <div class="col-start-1 bg-white rounded-lg p-7">
                <h2 class="text-rp-neutral-700 font-bold text-[19px] mb-4">Return and Payment Information</h2>
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between">
                        <p class="w-1/2">Payment Method used by buyer:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                            {{ $return_order->product_order->payment_option->name }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p class="w-1/2">Paid Price + Shipping Fee:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                            {{ '₱ ' . number_format($return_order->product_order->amount * $return_order->product_order->quantity + $return_order->product_order->shipping_fee, 2) }}
                        </p>
                    </div>
                    <div class="flex justify-between">
                        <p class="w-1/2">Refund Amount:</p>
                        @if (in_array($return_order->status->slug, ['refunded_only', 'returned_and_refunded']))
                            <p class="text-rp-red-500 max-w-[230px] text-[19px] text-right font-bold">₱
                                {{ number_format($return_order->product_order->amount * $return_order->product_order->quantity, 2) }}
                            </p>
                        @else
                            <p class="text-rp-red-500 max-w-[230px] text-[19px] text-right font-bold">₱ 0.00</p>
                        @endif
                    </div>
                </div>
            </div>
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

    <x-loader.black-screen wire:loading.block wire:target="activeBox,open_modal,closeModal,search,reset_search,successModal" class="z-30"/>

</x-main.content>
