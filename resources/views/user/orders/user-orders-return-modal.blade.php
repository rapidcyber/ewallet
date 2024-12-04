<x-modal x-model="$wire.visible">
    <x-modal.form-modal title="Request Return">
        <div>
            <div class="flex flex-col rounded-lg w-full text-sm">
                <div class="flex flex-row justify-between pb-2 border-b items-center ">
                    <div class="flex flex-row gap-2 items-center border-gray-600">
                        <div class="w-8 h-8">
                            @if ($product_order->product->merchant->logo)
                                <img src="{{ $this->get_media_url($product_order->product->merchant->logo, 'thumbnail') }}"
                                    class="w-full h-full object-cover rounded-full"
                                    alt="{{ $product_order->product->merchant->name . ' Logo' }}" />
                            @else
                                <img src="{{ url('images/user/default-avatar.png') }}"
                                    class="w-full h-full object-cover rounded-full" alt="Default Profile Picture" />
                            @endif
                        </div>
                        <p class="text-rp-red-500">{{ $product_order->product->merchant->name }}</p>
                    </div>
                    <div>
                        <p>Order Date:
                            <span>{{ \Carbon\Carbon::parse($product_order->created_at)->timezone('Asia/Manila')->format('F j, Y') }}</span>
                        </p>
                        <p>Delivery Date:
                            <span>{{ \Carbon\Carbon::parse($product_order->processed_at)->timezone('Asia/Manila')->format('F j, Y') }}</span>
                        </p>
                    </div>
                </div>
                <div class="py-5">
                    <div class="flex">
                        <div class="w-[15%]">
                            <img src="{{ $this->get_media_url($product_order->product->first_image, 'thumbnail') }}"
                                alt="{{ $product_order->product->name }}" class="w-full h-full object-cover" />
                        </div>
                        <div class="w-[85%] overflow-hidden px-2">
                            <h3 class="font-bold text-rp-neutral-700 text-lg truncate overflow-hidden">
                                {{ $product_order->product->name }}</h3>
                            {{-- <p class="truncate">Green, Microfiber, Small</p> --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-rp-neutral-50 rounded-lg p-7 break-words mb-6">
                <h2 class="text-[19px] text-rp-neutral-700 font-bold mb-3">Deliver Details</h2>
                <p>Order Number: <span class="text-rp-red-500">{{ $product_order->order_number }}</span></p>
                <p class="font-light text-sm text-rp-neutral-500 mb-4">Delivered
                    {{ \Carbon\Carbon::parse($product_order->processed_at)->format('F j, Y') }}</p>

                <div class="space-y-4">
                    <div class="flex gap-3 items-center">
                        <div>
                            <x-icon.phone fill="#D3DADE" />
                        </div>
                        <p>{{ $product_order->buyer->phone_number }}</p>
                    </div>
                    <div class="flex gap-3 items-center">
                        <div>
                            <x-icon.location fill="#D3DADE" />
                        </div>
                        <div>{{ $product_order->location->address }}</div>
                    </div>
                </div>
            </div>

            <x-input.input-group class="mb-6">
                <x-slot:label>Reason for return</x-slot:label>
                <x-dropdown.select wire:model='reason'>
                    <x-dropdown.select.option value="" selected hidden>Select</x-dropdown.select.option>
                    @foreach ($this->reason_options as $key => $option)
                        <x-dropdown.select.option value="{{ $option->slug }}"
                            wire:key='reason-{{ $key }}'>{{ $option->name }}</x-dropdown.select.option>
                    @endforeach
                </x-dropdown.select>
                @error('reason')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>

            <x-input.input-group class="mb-6">
                <x-slot:label>Comment*</x-slot:label>
                <x-input.textarea wire:model='comment' />
                @error('comment')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>


            <x-input.input-group class="mb-6">
                <x-slot:label>Upload Images</x-slot:label>
                <livewire:components.input.interactive-upload-images :uploaded_images="$uploaded_images" :max="5"
                    function="updateImages" />
                @error('uploaded_images')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
                @error('uploaded_images.*')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>

        </div>
        <x-slot:action_buttons>
            <x-button.outline-button wire:click="$dispatch('closeModal')"
                class="w-1/2">cancel</x-button.outline-button>
            <x-button.filled-button wire:click='submit' wire:target='submit' wire:loading.attr='disabled'
                wire:loading.class='cursor-progress' class="w-1/2">confirm</x-button.filled-button>
        </x-slot:actions>
    </x-modal.form-modal>
</x-modal>
