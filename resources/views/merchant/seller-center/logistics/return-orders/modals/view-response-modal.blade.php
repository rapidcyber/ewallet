<div class="absolute flex flex-col gap-8 bg-white p-10 rounded-2xl w-[1180px] max-w-[90%] max-h-[95%] overflow-y-auto">
    {{-- CLOSE BUTTON --}}
    <button class="absolute top-6 right-6" @click="$dispatch('closeModal')">
        <x-icon.close />
    </button>

    {{-- HEADING --}}
    <div>
        <h3 class="text-2xl font-bold mb-2">View Response</h3>
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
                    <h5 class="text-[13px] font-bold">{{ $return_order->status->parent_status?->name }}</h5>
                    <p
                        class="text-[11px] w-fit px-2 py-[2px] text-rp-neutral-600 border border-rp-neutral-600 bg-rp-neutral-200 rounded-[5px]">
                        {{ $return_order->status->name }}
                    </p>
                    <p class="text-[11px]">Return Order Number:
                        <span class="text-rp-red-500">{{ $return_order->id }}</span>
                    </p>
                    <p class="text-[11px]">Return Reason: {{ $return_order->reason->name }}</p>
                </div>
            </td>
        </tr>
    </table>

    {{-- FORM --}}
    <div class="flex flex-col gap-2">
        {{-- comment --}}
        <x-input.input-group>
            <x-slot:label><span class="text-rp-red-600">*</span>Comment</x-slot:label>
            <x-input.textarea x-ref="inpcomment" name="" id="" cols="30" rows="10" placeholder="{{ $return_order->dispute->response->comment }}" disabled readonly
                maxlength="300"></x-input.textarea>
            <p class="text-right text-[11px]"><span>{{ strlen($return_order->dispute->response->comment) }}</span>/<span x-html="$refs.inpcomment.maxLength"></span></p>
        </x-input.input-group>

        {{-- uploaded images --}}
        <div class="block mb-8">
            <p class="text-[13px] text-rp-neutral-500">Attached Images ({{ $return_order->dispute->response->media->count() }})</p>
            <div class="flex gap-2">
                @foreach ($return_order->dispute->response->media as $image)    
                    <div class="w-36 aspect-square">
                        <img src="{{ $this->get_media_url($image) }}" alt="temp img"
                            class="rounded-[5px] w-full object-cover">
                    </div>
                @endforeach
            </div>
        </div>


        {{-- buttons --}}
        <div class="grid grid-cols-2 gap-3">
            <x-button.outline-button @click="$dispatch('closeModal')">Cancel</x-button.outline-button>
            <x-button.filled-button @click="$dispatch('closeModal')">Confirm</x-button.filled-button>
        </div>
    </div>
</div>
