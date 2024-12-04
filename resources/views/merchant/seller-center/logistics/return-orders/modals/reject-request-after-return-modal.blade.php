<div class="absolute flex flex-col gap-8 bg-white p-10 rounded-2xl w-[1180px] max-w-[90%] max-h-[95%] overflow-y-auto">
    {{-- CLOSE BUTTON --}}
    <button class="absolute top-6 right-6" @click="$dispatch('closeModal')">
        <x-icon.close />
    </button>

    {{-- HEADING --}}
    <div>
        <h3 class="text-2xl font-bold mb-2">Reject Request After Return</h3>
        <p class="text-[13px]">This request will be processed for refunds. Please confirm if you agree to
            refund the request.</p>

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
                        {{ $return_order->product_order->payment_option->name }}
                    </p>
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
                    <p class="text-[11px]">Return Order Number: <span
                            class="text-rp-red-500">{{ $return_order->id }}</span>
                    </p>
                    <p class="text-[11px]">Return Reason: {{ $return_order->reason->name }}</p>
                </div>
            </td>
        </tr>
    </table>

    {{-- FORM --}}
    <form wire:submit.prevent="reject_request" class="flex flex-col gap-2">
        {{-- reason --}}
        <x-input.input-group>
            <x-slot:label><span class="text-rp-red-600">*</span>What is the reason for rejection?</x-slot:label>
            <x-dropdown.select wire:model='reason'>
                <x-dropdown.select.option value="" selected hidden>Select</x-dropdown.select.option>
                @foreach ($this->get_rejection_reasons as $key => $reason_opt)
                    <x-dropdown.select.option value="{{ $reason_opt->slug }}" wire:key="reason-{{ $key }}">{{ $reason_opt->name }}</x-dropdown.select.option>
                @endforeach
            </x-dropdown.select>
            @error('reason')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </x-input.input-group>

        {{-- comment --}}
        <x-input.input-group>
            <x-slot:label for="comment"><span class="text-rp-red-600">*</span>Comment</x-slot:label>
            <x-input.textarea x-ref="inpcomment" name="comment" wire:model="comment" id="comment" cols="30"
                rows="10" maxlength="300"></x-input.textarea>
            <div class="flex flex-row justify-between">
                <div>
                    @error('comment')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <p class="text-right text-[11px]"><span x-html="$wire.comment.length"></span>/<span
                            x-html="$refs.inpcomment.maxLength"></span></p>
                </div>
            </div>
        </x-input.input-group>

        {{-- Upload images --}}
        <x-input.input-group>
            <x-slot:label><span class="text-rp-red-600">*</span>Upload Images</x-slot:label>
            <livewire:components.input.interactive-upload-images :uploaded_images="$uploaded_images" :max="5"
                function="updateImages" />
            @error('uploaded_images')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
            @error('uploaded_images.*')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </x-input.input-group>

        {{-- buttons --}}
        <div class="grid grid-cols-2 gap-3">
            <x-button.outline-button type="button" @click="$dispatch('closeModal')" wire:target="reject_request"
                wire:loading.attr="disabled" wire:loading.class="opacity-50">Cancel</x-button.outline-button>
            <x-button.filled-button type="submit" wire:target="reject_request" wire:loading.attr='disabled'
                :disabled="$button_clickable == false">Confirm</x-button.filled-button>
        </div>
    </form>

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

    <x-loader.black-screen wire:loading.block wire:target="reject_request" class="z-10" />
</div>
