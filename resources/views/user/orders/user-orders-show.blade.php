<x-main.content x-data>
    <x-main.action-header>
        <x-slot:title>Order Details</x-slot:title>
        <x-slot:actions>
            @switch($product_order->shipping_status->name)
                @case('Pending')
                @case('Packed')

                @case('Ready to Ship')
                @case('To Ship')
                    <x-button.filled-button wire:click="open_cancel_order_modal" size="md">cancel</x-button.filled-button>
                    @if ($order_cancel_id)
                        <livewire:user.orders.modals.user-cancel-order-modal :order_id="$order_cancel_id" />
                    @endif
                @break

                @case('Completed')
                    @if ($this->check_review_exists == false)
                        <x-button.filled-button wire:click='open_review_modal' size="md">
                            write a review
                        </x-button.filled-button>
                    @endif
                    @if ($this->check_return_exists == false)
                        <x-button.outline-button wire:click='open_return_modal' size="md">
                            request return
                        </x-button.outline-button>
                    @endif
                @break

                @default
            @endswitch
        </x-slot:actions>
    </x-main.action-header>

    <div class="flex flex-col px-4 py-3 bg-white rounded-lg w-full text-sm mb-7">
        <div class="flex flex-row justify-between pb-2 border-b items-center ">
            <div class="flex flex-row gap-2 items-center border-gray-600">
                <div class="w-8 h-8">
                    @if ($product_order->product->merchant->logo)
                        <img src="{{ $this->get_media_url($product_order->product->merchant->logo, 'thumbnail') }}"
                            class="w-full h-full object-cover rounded-full"
                            alt="{{ $product_order->product->merchant->name . ' Logo' }}" />
                    @else
                        <img src="{{ url('/images/user/default-avatar.png') }}"
                            alt="w-full h-full object-cover rounded-full" alt="Default Logo">
                    @endif
                </div>
                <p class="text-rp-red-500">{{ $product_order->product->merchant->name }}</p>
                <div>
                    <x-icon.message />
                </div>
                <p>({{ $product_order->quantity }} item)</p>
            </div>
        </div>
        <div class="flex flex-row pt-4 justify-between break-words">
            {{-- Order details --}}
            <div class="flex flex-row w-5/12">
                <div class="w-[70px] h-[70px]">
                    <img src="{{ $this->get_media_url($product_order->product->first_image, 'thumbnail') }}"
                        alt="{{ $product_order->product->name }}" class="" />
                </div>
                <div class="overflow-hidden px-2">
                    <h3 class="font-bold text-rp-neutral-700 text-lg truncate overflow-hidden">
                        {{ $product_order->product->name }}</h3>
                    {{-- <p class="truncate">Green, Microfiber, Small</p> --}}
                </div>
            </div>
            {{-- Total --}}
            <div class="flex flex-col justify-center w-3/12 break-words px-2">
                <p>Total: P {{ number_format($product_order->amount, 2) }}</p>
                <p class="text-rp-red-500">x {{ $product_order->quantity }}</p>
            </div>
            {{-- Status --}}
            <div class="flex flex-col justify-center items-end w-2/12 px-2">
                @switch($this->shipping_status->name)
                    @case('Pending')
                    @case('Packed')

                    @case('Ready to Ship')
                    @case('To Ship')
                        <x-status color="neutral" class="w-28">{{ $product_order->shipping_status->name }}</x-status>
                    @break

                    @case('Shipping')
                        <x-status color="yellow" class="w-28">{{ $product_order->shipping_status->name }}</x-status>
                    @break

                    @case('Completed')
                        <x-status color="green" class="w-28">Delivered</x-status>
                    @break

                    @case('Cancellation')
                        <x-status color="red" class="w-28">Cancelled</x-status>
                    @break

                    @case('Failed Delivery')
                        <x-status color="red" class="w-28">Failed Delivery</x-status>
                    @break

                    @default
                        <x-status color="neutral" class="w-28">{{ $product_order->shipping_status->name }}</x-status>
                @endswitch
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-7">
        <div class="text-sm">
            <x-card.tracking-details :logs="$product_order->logs" delivery_partner="{{ $product_order->shipping_option->name }}"
                tracking_number="{{ $product_order->tracking_number }}" delivery_status="{{ $delivery_status }}" />
        </div>
        <div class="flex flex-col gap-7">
            <div class="bg-white rounded-lg p-7 break-words">
                <h2 class="text-[19px] text-rp-neutral-700 font-bold">Deliver Details</h2>
                <p>Order Number: <span class="text-rp-red-500">{{ $product_order->order_number }}</span></p>
                @if ($this->shipping_status->slug == 'completed')
                    <p class="font-light text-sm text-rp-neutral-500 mb-4">Delivered
                        {{ \Carbon\Carbon::parse($product_order->processed_at)->timezone('Asia/Manila')->format('F j, Y') }}</p>
                @elseif ($this->shipping_status->slug == 'cancellation')
                    <p class="font-light text-sm text-rp-neutral-500 mb-4">Cancel requested on
                        {{ \Carbon\Carbon::parse($product_order->processed_at)->timezone('Asia/Manila')->format('F j, Y') }}</p>
                @elseif ($this->shipping_status->slug == 'failed_delivery')
                    <p class="font-light text-sm text-rp-neutral-500 mb-4">Delivery failed
                        {{ \Carbon\Carbon::parse($product_order->processed_at)->timezone('Asia/Manila')->format('F j, Y') }}</p>
                @endif

                <div class="space-y-4 mt-4">
                    <div class="flex gap-3 items-center">
                        <div>
                            <x-icon.phone fill="#D3DADE" />
                        </div>
                        <p>{{ $this->format_phone_number($product_order->buyer->phone_number, $product_order->buyer->phone_iso) }}</p>
                    </div>
                    <div class="flex gap-3 items-center">
                        <div>
                            <x-icon.location fill="#D3DADE" />
                        </div>
                        <div>{{ $product_order->location->address }}</div>
                    </div>
                </div>
            </div>
            @if ($product_order->cancellation)     
                <div class="bg-white rounded-lg p-7 break-words">
                    <h2 class="text-[19px] text-rp-neutral-700 font-bold mb-4">Cancel Details</h2>
                    <div class="space-y-1">
                        <div class="flex justify-between items-center">
                            <p>Cancelled by</p>
                            <p class="text-rp-neutral-700 font-bold">{{ ucwords($product_order->cancellation->cancelled_by) }}</p>
                        </div>
                        <div class="flex justify-between items-center">
                            <p>Reason for cancellation:</p>
                            <p class="text-rp-neutral-700 font-bold">{{ $product_order->cancellation->reason->name }}</p>
                        </div>
                        <div class="flex justify-between items-start">
                            <p>Comments</p>
                            <p class="w-2/3 text-rp-neutral-700 font-bold text-right break-words">{{ $product_order->cancellation->comment }}</p>
                        </div>
                        <div>
                            <p>Attached Images ({{ $product_order->cancellation->media->count() }})</p>
                            <div class="flex gap-2">
                                @foreach ($product_order->cancellation->media as $key => $image)
                                    <img src="{{ $this->get_media_url($image) }}" alt="image-{{ $key }}"
                                        class="w-[123px] h-[102px] rounded-md object-cover">
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="bg-white rounded-lg p-7 break-words">
                <h2 class="text-[19px] text-rp-neutral-700 font-bold">Payment Summary</h2>
                <p class="font-light text-sm text-rp-neutral-500 mb-4">Paid via
                    {{ $product_order->payment_option->name }}</p>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <p>Product subtotal:</p>
                        <p class="text-rp-neutral-700 font-bold">{{ \Number::currency($product_order->amount * $product_order->quantity, 'PHP') }}</p>
                    </div>
                    <div class="flex justify-between items-center">
                        <p>Shipping Fee:</p>
                        <p class="text-rp-neutral-700 font-bold">{{ \Number::currency($product_order->shipping_fee, 'PHP') }}
                        </p>
                    </div>
                    <div class="flex justify-between items-center">
                        <p>Total:</p>
                        <p class="font-bold text-rp-red-500">{{ \Number::currency(($product_order->amount * $product_order->quantity) + $product_order->shipping_fee, 'PHP') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------ MODAL STARTS HERE -------------- --}}
    {{-- REVIEW PRODUCT --}}
    @if ($show_review_modal)
        <livewire:user.orders.user-orders-review-modal :order_review_id="$product_order->id" />
    @endif

    @if ($show_return_modal)
        <livewire:user.orders.user-orders-return-modal :order_return_id="$product_order->id" />
    @endif

    <x-loader.black-screen wire:loading.flex class="z-30"
        wire:target="activeBox,open_review_modal,open_return_modal,open_cancel_order_modal">
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
