<x-modal x-model="true">
    <x-modal.form-modal title="File a Dispute" class="!w-1/2 max-w-[774px] min-w-[600px]">
        <div class="flex flex-col rounded-lg w-full text-sm">
            <div class="flex flex-row justify-between pb-2 border-b items-center ">
                <div class="flex flex-row gap-2 items-center border-gray-600">
                    <div class="w-8 h-8">
                        @if ($logo = $return_order->product_order->product->merchant->logo)
                            <img src="{{ $this->get_media_url($logo, 'thumbnail') }}"
                                class="w-full h-full object-cover rounded-full"
                                alt="{{ $return_order->product_order->product->merchant->name }} Merchant Logo" />
                        @else
                            <img src="{{ url('images/user/default-avatar.png') }}"
                                class="w-full h-full object-cover rounded-full" alt="Default Merchant Logo" />
                        @endif
                    </div>
                    <p class="text-rp-red-500">{{ $return_order->product_order->product->merchant->name }}</p>
                </div>
                <div>
                    <p>Order Date:
                        <span>{{ \Carbon\Carbon::parse($return_order->product_order->created_at)->timezone('Asia/Manila')->format('F j, Y') }}</span>
                    </p>
                    <p>Return Requested Date:
                        <span>{{ $return_order->created_at ? \Carbon\Carbon::parse($return_order->created_at)->timezone('Asia/Manila')->format('F j, Y') : '-' }}</span>
                    </p>
                </div>
            </div>
            <div class="py-5">
                <div class="flex">
                    @if ($product_image = $return_order->product_order->product->first_image)
                        <div class="w-16 h-16">
                            <img src="{{ $this->get_media_url($product_image, 'thumbnail') }}"
                                alt="{{ $return_order->product_order->product->name }}"
                                class="w-full h-full object-cover" />
                        </div>
                    @endif
                    <div class="w-[80%] overflow-hidden px-2">
                        <h3 class="font-bold text-rp-neutral-700 text-lg truncate overflow-hidden">
                            {{ $return_order->product_order->product->name }}</h3>
                        {{-- <p class="truncate">Green, Microfiber, Small</p> --}}
                        <p class="text-sm">
                            {{ $return_order->created_at ? \Carbon\Carbon::parse($return_order->created_at)->timezone('Asia/Manila')->format('F j, Y g:i A') : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <x-input.input-group class="mb-6">
            <x-slot:label>Comment*</x-slot:label>
            <x-input.textarea wire:model='comment' maxlength="2000" rows="6" />
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


        <x-slot:action_buttons>
            <x-button.outline-button @click="$dispatch('closeModal');visible=false" wire:target='submit'
                wire:loading.attr='disabled' wire:loading.class='cursor-progress'
                class="w-1/2">cancel</x-button.outline-button>
            <x-button.filled-button wire:click='submit' wire:target='submit' wire:loading.attr='disabled'
                wire:loading.class='cursor-progress' class="w-1/2">confirm</x-button.filled-button>
        </x-slot:actions>
    </x-modal.form-modal>
</x-modal>
