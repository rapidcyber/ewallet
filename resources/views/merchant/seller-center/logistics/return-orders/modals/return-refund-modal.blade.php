<div class="absolute flex flex-col gap-8 bg-white p-10 rounded-2xl w-[1180px] max-w-[90%] max-h-[95%] overflow-y-auto">
    {{-- CLOSE BUTTON --}}
    <button class="absolute top-6 right-6" @click="$dispatch('closeModal')">
        <x-icon.close />
    </button>

    {{-- HEADING --}}
    <div>
        <h3 class="text-2xl font-bold mb-2">Return & Refund</h3>
        <p class="text-[13px]">This will require the buyer to return the product to you. After return, please
            perform a quality check of the product before processing the refund.</p>
    </div>

    {{-- TABLE --}}
    <table>
        <tr class="border-b font-bold">
            <th class="text-left text-lg font-bold py-[10px]">Buyer</th>
            <th class="text-left text-lg font-bold py-[10px]">Order Details</th>
            <th class="text-left text-lg font-bold py-[10px]">Total Amount</th>
            <th class="text-left text-lg font-bold py-[10px]">Return Details</th>
        </tr>
        {{-- DATA --}}
        <tr>
            <td class="rounded-l-lg bg-white py-6 align-top">
                <div class="flex gap-[8px] items-center">
                    @if ($return_order->product_order->buyer->media->isNotEmpty())
                        <img src="{{ $this->get_media_url($return_order->product_order->buyer->media->first(), 'thumbnail') }}"
                            alt="" class="w-[32px] h-[32px] object-cover rounded-full">
                    @else
                        <img src="{{ url('images/user/default-avatar.png') }}" alt=""
                            class="w-[32px] h-[32px] object-cover rounded-full">
                    @endif
                    <span class="font-bold text-[13px]">{{ $return_order->product_order->buyer->name }}</span>
                </div>
            </td>
            <td class="bg-white py-6 align-top">
                <div class="flex gap-3">
                    <div class="w-16 h-16">
                        <img src="{{ $this->get_media_url($return_order->product_order->product->first_image, 'thumbnail') }}"
                            alt="" class="w-full h-full object-cover rounded-md">
                    </div>
                    <div class="w-[calc(100%-64px)]">
                        <h5 class="text-[13px] font-bold">{{ $return_order->product_order->product->name }}</h5>
                        {{-- <p class="text-[11px]">Silver, 64GB</p> --}}
                        <p class="text-[11px]">Order Number:</p>
                        <p class="text-[11px] text-rp-red-600">{{ $return_order->product_order->order_number }}</p>
                    </div>
                </div>
            </td>
            <td class="bg-white py-6 align-top">
                <div>
                    <h5 class="text-[13px] font-bold">P
                        {{ number_format($return_order->product_order->amount * $return_order->product_order->quantity, 2) }}
                    </h5>
                    <p class="text-[11px] w-fit px-2 py-[2px] text-rp-red-600 border border-rp-red-600 rounded-[5px]">
                        COD</p>
                    <p class="text-[11px]">Paid Price + Shipping Fee: P
                        {{ number_format($return_order->product_order->amount * $return_order->product_order->quantity + $return_order->product_order->shipping_fee, 2) }}
                    </p>
                    {{-- <p class="text-[11px]">Refund Amount: 0</p> --}}
                </div>
            </td>
            <td class="bg-white py-6 align-top">
                <div>
                    @if ($return_order->status->slug == 'return_initiated')
                        <h5 class="text-[13px] font-bold">Return Initiated</h5>
                        <p
                            class="text-[11px] w-fit px-2 py-[2px] text-rp-red-600 border border-rp-red-600 bg-rp-red-200 rounded-[5px]">
                            {{ $this->calculate_remaining_hours($return_order->created_at) }}
                        </p>
                    @else
                        <h5 class="text-[13px] font-bold">{{ $return_order->status->parent_status?->name }}</h5>
                        <p
                            class="text-[11px] w-fit px-2 py-[2px] text-rp-neutral-600 border border-rp-neutral-600 bg-rp-neutral-200 rounded-[5px]">
                            {{ $return_order->status->name }}
                        </p>
                    @endif
                    <p class="text-[11px]">Return Order Number: <span
                            class="text-rp-red-500">{{ $return_order->id }}</span>
                    </p>
                    <p class="text-[11px]">Return Reason: {{ $return_order->reason->name }}</p>
                </div>
            </td>
        </tr>
    </table>

    {{-- BUTTON --}}
    <div class="grid grid-cols-2 gap-3">
        <x-button.outline-button @click="$dispatch('closeModal')" wire:target="process_return" wire:loading.attr="disabled"
            wire:loading.class="opacity-50">Cancel</x-button.outline-button>
        <x-button.filled-button wire:click="process_return" wire:target="process_return" wire:loading.attr="disabled" :disabled="$button_clickable == false">Confirm</x-button.filled-button>
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

    <x-loader.black-screen wire:loading.block wire:target="process_return" class="z-10" />
</div>
