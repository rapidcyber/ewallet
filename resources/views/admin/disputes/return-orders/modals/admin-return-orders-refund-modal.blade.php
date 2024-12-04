<x-modal x-model="$wire.visible">
    <div
        class="absolute flex flex-col gap-8 bg-white p-10 rounded-2xl w-[1180px] max-w-[90%] max-h-[95%] overflow-y-auto">
        {{-- CLOSE BUTTON --}}
        <button class="absolute top-6 right-6" @click="$wire.visible=false;$dispatch('closeModal')">
            <x-icon.close />
        </button>

        {{-- HEADING --}}
        <div>
            <h3 class="text-2xl font-bold mb-2">Refund Only</h3>
        </div>

        <div class="flex flex-col w-full px-4 py-3 text-sm break-words bg-white rounded-md">
            <div class="flex flex-row justify-between w-full pb-2 border-b">
                <div class="flex flex-col max-w-[50%]">
                    <p>Buyer: <span class="text-primary-600">{{ $return_order->product_order->buyer->name }}</span></p>
                    <p>Merchant: <span
                            class="text-primary-600">{{ $return_order->product_order->product->merchant->name }}</span>
                    </p>
                </div>
                <div class="max-w-[50%]">
                    <p>{{ $return_order->product_order->processed_at? 'Delivered to buyer on ' .\Carbon\Carbon::parse($return_order->product_order->processed_at)->timezone('Asia/Manila')->format('F d, Y'): 'No date available' }}
                    </p>
                    <p>{{ 'Return requested on ' .\Carbon\Carbon::parse($return_order->created_at)->timezone('Asia/Manila')->format('F d, Y') }}
                    </p>
                </div>
            </div>
            <div class="flex flex-row justify-between py-3 break-words">
                {{-- Order details --}}
                <div class="flex flex-row w-5/12 gap-3">
                    <div class="flex-[20%]">
                        <img
                            src={{ $return_order->product_order->product->first_image ? $this->get_media_url($return_order->product_order->product->first_image, 'thumbnail') : '/images/guest/sofabed.png' }} />
                    </div>
                    <div class="flex-[80%] overflow-hidden">
                        <h2 class="overflow-hidden text-lg font-bold truncate">
                            {{ $return_order->product_order->product->name }}</h2>
                        {{-- <p class="truncate">{{ $return_order->product_order->product->description }}</p> --}}
                        <div class="flex flex-col mt-3">
                            <div>
                                <span>Order Number:</span>
                                <span class="text-primary-600">{{ $return_order->product_order->order_number }}</span>
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
                        <p class="text-rp-neutral-500">Paid Price + Shipping Fee: <span class="text-rp-neutral-700">₱
                                {{ number_format($return_order->product_order->amount * $return_order->product_order->quantity + $return_order->product_order->shipping_fee, 2) }}</span>
                        </p>
                        <p class="text-rp-neutral-500">
                            Refund Amount:
                            <span class="text-rp-neutral-700">₱
                                @if (in_array($return_order->status->name, ['Refunded Only', 'Returned and Refunded']))
                                    {{ number_format($return_order->product_order->amount + $return_order->product_order->quantity, 2) }}
                                @else
                                    0.00
                                @endif
                            </span>
                        </p>
                        <p class="text-rp-neutral-500">Return Reason: <span
                                class="text-rp-neutral-700">{{ $return_order->reason->name }}</span></p>
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex items-center w-3/12">
                    <div class="flex flex-row items-center justify-end w-full">
                        @if ($return_order->status->parent_status?->name == 'Resolved')
                            <div class="flex flex-col items-center">
                                <x-status color="green" class="w-44">Resolved</x-status>
                                <p class="font-light">{{ $return_order->status->name }}</p>
                            </div>
                        @elseif ($return_order->status->parent_status?->name == 'Dispute In Progress')
                            @if ($return_order->status->name == 'Pending Response')
                                <div class="flex flex-col items-center">
                                    <x-status color="neutral" class="w-44">Pending Merchant Response</x-status>
                                </div>
                            @else
                                <div class="flex flex-col items-center">
                                    <x-status color="red"
                                        class="w-44">{{ $return_order->status->name }}</x-status>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- BUTTON --}}
        <div class="grid grid-cols-2 gap-3">
            <x-button.outline-button @click="$wire.visible=false;$dispatch('closeModal')" wire:target="refund" wire:loading.attr="disabled"
                wire:loading.class="opacity-50" color="primary">Cancel</x-button.outline-button>
            <x-button.filled-button wire:click="refund" wire:target="refund" wire:loading.attr='disabled'
                :disabled="$button_clickable == false" color="primary">Confirm</x-button.filled-button>
        </div>

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

        <x-loader.black-screen wire:loading.block wire:target="refund" class="z-10" />
    </div>
</x-modal>
