<div class="flex h-full" x-data="{ isAddItemVisible: $wire.entangle('isAddItemVisible'), isEditItemModalVisible: $wire.entangle('isEditItemModalVisible') }">
    <x-main.content class="overflow-y-auto grow">

        <x-main.title class="mb-8">
            @if ($booking->status->name == 'Inquiry')
                Send Quotation
            @else
                Fulfill Service
            @endif
        </x-main.title>


        <div>
            {{-- Send to --}}
            <div class="mb-3">
                <x-input.input-group>
                    <x-slot:label>Send to</x-slot:label>
                    <x-input type="text" value="{{ $this->recipient->phone_number }}" readonly>
                        <x-slot:icon>
                            <x-icon.phone />
                        </x-slot:icon>
                    </x-input>
                </x-input.input-group>
            </div>

            <div class="flex gap-3 mb-3">
                <x-input.input-group class="w-1/2">
                    <x-slot:label>Currency</x-slot:label>

                    <x-dropdown.select wire:model='currency' class="h-11">
                        <x-dropdown.select.option value="">Select</x-dropdown.select.option>
                        <x-dropdown.select.option value="PHP" selected>PHP</x-dropdown.select.option>
                        @error('currency')
                            <span class="text-red-500">{{ $message }}</span>
                        @enderror
                    </x-dropdown.select>

                </x-input.input-group>


                <x-input.input-group class="w-1/2">
                    <x-slot:label>Due Date:</x-slot:label>
                    <x-input wire:model.live='due_date' type="date" class="h-full" />
                    @error('due_date')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </x-input.input-group>

            </div>

            {{-- Items & Services --}}
            <div class="mb-3">
                <h2 class="font-bold text-lg">Items & Services</h2>
                <div class="flex flex-col gap-2 mt-3">
                    {{-- ITEMS --}}
                    @error('items')
                        <span class="text-red-500 mb-4">{{ $message }}</span>
                    @enderror
                    @foreach ($items as $key => $item)
                        <div
                            class="flex flex-row items-center justify-between border border-rp-neutral-500 bg-white rounded-lg px-5 py-4">
                            <div>
                                <P>{{ $item['name'] }}</p>
                                <p>
                                    <span class="font-semibold">₱{{ number_format($item['price'], 2) }}</span> x
                                    {{ $item['quantity'] }}
                                </p>
                            </div>
                            <div class="flex flex-row gap-11 items-center">
                                <p class="text-lg font-bold">₱{{ number_format($item['price'] * $item['quantity'], 2) }}
                                </p>
                                <div class="flex flex-row gap-2">
                                    <div wire:click="handleEditItem({{ $key }})" class="cursor-pointer">
                                        <x-icon.edit />
                                    </div>
                                    <div wire:click="handleRemoveItem({{ $key }})" class="cursor-pointer">
                                        <x-icon.close />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <x-button.outline-button wire:click="handleAddItemModal(true)">add</x-button.outline-button>
                </div>
            </div>

            {{-- Inclusions --}}
            @if (!empty($items))
                <div class="mb-3">
                    <h2 class="font-bold text-lg mb-2">Inclusions</h2>
                    <div class="flex flex-row w-full gap-2">
                        <div class="w-4/12">
                            <label class="flex flex-row items-center gap-3 cursor-pointer">
                                <x-input type="checkbox" wire:model="allow_vat" wire:change="handleVatValue()" />
                                <h2 class="font-bold text-lg">Vat (12%)</h2>
                            </label>
                            <div class="mt-3">
                                <p>VAT</p>
                                <x-input type="number" value="{{ number_format($this->vat_amt, 2) }}" readonly
                                    :disabled="!$allow_vat" />
                            </div>
                        </div>
                        <div class="w-4/12">
                            <label class="flex flex-row items-center gap-3 cursor-pointer">
                                <x-input type="checkbox" wire:model="allow_discount"
                                    wire:change="handleDiscountValue()"></x-input>
                                <h2 class="font-bold text-lg">Discount</h2>
                            </label>
                            <div class="mt-3">
                                <p>Discount</p>
                                <x-input type="number" wire:model.live.debounce.250ms="discountAmt" placeholder="0.00"
                                    :disabled="!$allow_discount" />
                            </div>
                            @error('discountAmt')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="w-4/12">
                            <label class="flex flex-row items-center gap-3 cursor-pointer">
                                <x-input type="checkbox" wire:model.live="allow_shipping"
                                    wire:change="handleShippingValue()" />
                                <h2 class="font-bold text-lg">Shipping Fee</h2>
                            </label>
                            <div class="mt-3">
                                <p>Shipping Fee</p>
                                <x-input type="number" step="0.01" wire:model.live.debounce.250ms="shippingAmt"
                                    placeholder="0.00" :disabled="!$allow_shipping" />
                                @error('shippingAmt')
                                    <span class="text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <h2 class="font-bold text-lg">Partial Payment</h2>
                    <div class="mt-2">
                        <label class="flex flex-row items-center gap-3 cursor-pointer">
                            <x-input type="checkbox" wire:model="allow_partial" wire:change="handlePartialValue()" />
                            <p>Allow partial payment</p>
                        </label>
                        <x-input.input-group class="mt-2">
                            <x-slot:label>Partial Payment</x-slot:label>
                            <x-input type="number" wire:model.live="partialAmt" placeholder="0.00" :disabled="!$allow_partial" />
                            @error('partialAmt')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </x-input.input-group>
                    </div>
                </div>
            @endif
        </div>
    </x-main.content>


    <x-layout.summary>
        <x-slot:profile>
            <x-layout.summary.profile
                image_path="{{ $this->recipient->media->first() ? $this->get_media_url($this->recipient->media->first(), 'thumbnail') : url('images/user/default-avatar.png') }}">
                <x-slot:info_block_top>
                    Transaction to:
                </x-slot:info_block_top>
                <x-slot:info_block_middle>
                    {{ $this->recipient->name }}
                </x-slot:info_block_middle>
                <x-slot:info_block_bottom>
                    +{{ $this->recipient->phone_number }}
                </x-slot:info_block_bottom>
            </x-layout.summary.profile>
        </x-slot:profile>
        <x-slot:body>
            <x-layout.summary.section title="Invoice Details">
                <x-slot:data>
                    <x-layout.summary.label-data>
                        <x-slot:label>
                            Send to
                        </x-slot:label>
                        <x-slot:data>
                            {{ $this->recipient->phone_number }}
                        </x-slot:data>
                    </x-layout.summary.label-data>
                    <x-layout.summary.label-data>
                        <x-slot:label>
                            Due Date
                        </x-slot:label>
                        <x-slot:data>
                            {{ empty($due_date) ? '-' : \Carbon\Carbon::parse($due_date)->format('m/d/Y') }}
                        </x-slot:data>
                    </x-layout.summary.label-data>
                </x-slot:data>
            </x-layout.summary.section>
            <x-layout.summary.section title="Items & Services">
                <x-slot:data>
                    @foreach ($items as $item)
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                {{ $item['name'] }}
                            </x-slot:label>
                            <x-slot:data>
                                {{ '₱' . number_format($item['price'] * $item['quantity'], 2) }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    @endforeach
                    <x-layout.summary.label-data>
                        <x-slot:label>
                            Total
                        </x-slot:label>
                        <x-slot:data class="text-rp-red-500">
                            {{ '₱' . number_format($this->total_items, 2) }}
                        </x-slot:data>
                    </x-layout.summary.label-data>
                </x-slot:data>
            </x-layout.summary.section>
            <x-layout.summary.section title="Inclusions">
                <x-slot:data>
                    @if ($allow_vat)
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                VAT (12%)
                            </x-slot:label>
                            <x-slot:data>
                                {{ '₱' . number_format($this->vat_amt, 2) }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    @endif
                    @if ($allow_discount)
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Discount
                            </x-slot:label>
                            <x-slot:data>
                                {{ '₱' . number_format(abs(empty($discountAmt) ? 0 : $discountAmt), 2) }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    @endif
                    @if ($allow_shipping)
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Shipping Fee
                            </x-slot:label>
                            <x-slot:data>
                                {{ '₱' . number_format(empty($shippingAmt) ? 0 : $shippingAmt, 2) }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    @endif
                    <x-layout.summary.label-data>
                        <x-slot:label>
                            Total
                        </x-slot:label>
                        <x-slot:data class="text-rp-red-500">
                            {{ '₱' . number_format($this->inclusions, 2) }}
                        </x-slot:data>
                    </x-layout.summary.label-data>

                </x-slot:data>
            </x-layout.summary.section>
            <x-layout.summary.section title="Summary">
                <x-slot:data>
                    <x-layout.summary.label-data>
                        <x-slot:label>
                            Items & Services
                        </x-slot:label>
                        <x-slot:data>
                            {{ '₱' . number_format($this->total_items, 2) }}
                        </x-slot:data>
                    </x-layout.summary.label-data>
                    <x-layout.summary.label-data>
                        <x-slot:label>
                            Inclusions
                        </x-slot:label>
                        <x-slot:data>
                            {{ $this->inclusions < 0 ? '-' : '' . '₱' . number_format(abs($this->inclusions), 2) }}
                        </x-slot:data>
                    </x-layout.summary.label-data>
                    <x-layout.summary.label-data>
                        <x-slot:label>
                            Total
                        </x-slot:label>
                        <x-slot:data>
                            {{ '₱' . number_format($this->total, 2) }}
                        </x-slot:data>
                    </x-layout.summary.label-data>
                    @if ($allow_partial)
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Partial Payment
                            </x-slot:label>
                            <x-slot:data>
                                {{ '₱' . number_format(empty($partialAmt) ? 0 : $partialAmt, 2) }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    @endif
                </x-slot:data>
            </x-layout.summary.section>
        </x-slot:body>
        <x-slot:action>
            <div class="flex gap-3 items-center mb-5">
                {{-- I agree --}}
                <x-input type="checkbox" wire:model.live="agree" id="agree" />
                <label for="agree" class="text-rp-neutral-600 cursor-pointer">I agree that the above information
                    is correct.</label>
            </div>
            <div class="flex flex-col gap-3">
                <x-button.filled-button wire:click="submit" wire:target='submit' wire:loading.attr='disabled'
                    wire:loading.class='opacity-50' :disabled="!$agree" size="md">send</x-button.filled-button>
                <x-button.outline-button wire:target='submit' wire:loading.attr='disabled'
                    wire:loading.class='opacity-50' @click="history.back()" size="md"
                    color="red">cancel</x-button.outline-button>
            </div>
        </x-slot:action>
    </x-layout.summary>

    {{-- ---------------- MODAL STARTS HERE -----------------  --}}
    {{-- ADD ITEM MODAL --}}
    <x-modal x-model="isAddItemVisible">
        <x-modal.form-modal title="Add Item">
            <x-input.input-group class="mb-3">
                <x-slot:label>Name</x-slot:label>
                <x-input type="text" wire:model="new_item.name" />
                @error('new_item.name')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </x-input.input-group>
            <x-input.input-group class="mb-3">
                <x-slot:label>Item Description</x-slot:label>
                <x-input.textarea wire:model="new_item.description" />
                @error('new_item.description')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </x-input.input-group>
            <div class="flex flex-row gap-2">
                <x-input.input-group class="w-1/2">
                    <x-slot:label>Quantity</x-slot:label>
                    <x-input type="number" placeholder="0" wire:model="new_item.quantity" />
                    @error('new_item.quantity')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </x-input.input-group>

                <x-input.input-group class="w-1/2">
                    <x-slot:label>Price</x-slot:label>
                    <x-input type="number" placeholder="0.00" wire:model="new_item.price" />
                    @error('new_item.price')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </x-input.input-group>

            </div>
            <x-slot:action_buttons>
                <x-button.outline-button wire:target='handleCommitAdd' wire:loading.attr="disabled"
                    wire:loading.class="opacity-50" @click="isAddItemVisible=false"
                    class="w-1/2">cancel</x-button.outline-button>
                <x-button.filled-button wire:click="handleCommitAdd()" wire:target='handleCommitAdd'
                    wire:loading.attr="disabled" wire:loading.class="opacity-50"
                    class="w-1/2">add</x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.form-modal>
    </x-modal>

    {{-- EDIT ITEM MODAL --}}
    <x-modal x-model="isEditItemModalVisible">
        <x-modal.form-modal title="Edit Item">
            <x-input.input-group class="mb-3">
                <x-slot:label>Name</x-slot:label>
                <x-input type="text" wire:model="edit_item.name" />
                @error('edit_item.name')
                    <span class="text-red-500">Item name is required</span>
                @enderror
            </x-input.input-group>
            <x-input.input-group class="mb-3">
                <x-slot:label>Item Description</x-slot:label>
                <x-input.textarea wire:model="edit_item.description" />
                @error('edit_item.description')
                    <span class="text-red-500">Item description is required</span>
                @enderror
            </x-input.input-group>
            <div class="flex flex-row gap-2">
                <x-input.input-group class="w-1/2">
                    <x-slot:label>Quantity</x-slot:label>
                    <x-input type="number" placeholder="0" wire:model="edit_item.quantity" />
                    @error('edit_item.quantity')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </x-input.input-group>

                <x-input.input-group class="w-1/2">
                    <x-slot:label>Price</x-slot:label>
                    <x-input type="number" placeholder="0.00" wire:model="edit_item.price" />
                    @error('edit_item.price')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </x-input.input-group>

            </div>
            <x-slot:action_buttons>
                <x-button.outline-button @click="isEditItemModalVisible=false"
                    class="w-1/2">cancel</x-button.outline-button>
                <x-button.filled-button wire:click="handleCommitEdit()" class="w-1/2">add</x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.form-modal>
    </x-modal>


    {{-- API ERROR MESSAGE --}}
    <div x-data="{ show: @entangle('apiErrorMsg') }" x-cloak x-show="show"
        class="absolute inset-0 z-100 bg-opacity-50 bg-black flex items-center justify-center" @click="show = false">
        <div class="p-5 bg-white border-2 border-red-600 rounded-md w-96">
            <div class="flex flex-row justify-between items-center p-4">
                <p class="text-red-600 text-lg">Failed!</p>
                <div>
                    <x-icon.error />
                </div>
            </div>
            <hr>
            <div class="p-4 text-red-600 font-bold">
                {{ $apiErrorMsg }}
            </div>
            <hr>
            <small class="text-xs">Click anywhere inside this box to close</small>
        </div>
    </div>

    {{-- API SUCCESS MESSAGE  --}}
    <div x-data="{ show: @entangle('apiSuccessMsg') }" x-cloak x-show="show"
        class="absolute inset-0 z-100 bg-opacity-50 bg-black flex items-center justify-center" @click="show = false">
        <div class="p-5 bg-white border-2 border-rp-green-600 rounded-md w-96" onclick="window.history.back()">
            <div class="flex flex-row justify-between items-center p-4">
                <p class="text-rp-green-600 text-md">Success!</p>
                <x-icon.check />
            </div>
            <hr>
            <div class="p-4 text-rp-green-600 font-bold">
                {{ $apiSuccessMsg }}
            </div>
            <hr>
            <small class="text-xs">Click anywhere inside this box to continue</small>
        </div>
    </div>
</div>
