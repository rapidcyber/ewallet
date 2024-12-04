<x-main.content class="flex flex-col gap-8">
    <x-main.title wire:ignore>
        @if (request()->routeIs('merchant.seller-center.assets.create'))
            Add Product
        @endif
        @if (request()->routeIs('merchant.seller-center.assets.edit'))
            Edit Product
        @endif
    </x-main.title>

    {{-- BASIC INFORMATION --}}
    <div class="flex flex-col gap-4 p-6 rounded-lg bg-white">
        <h2 class="font-bold text-xl text-rp-neutral-700 mb-2">Basic Information</h2>

        {{-- UPLOAD IMAGES --}}
        <x-input.input-group>
            <x-slot:label><span class="text-[#F0146C]">*</span>Product Images (please upload up to 5 images)</x-slot:label>
            <livewire:components.input.interactive-upload-images :uploaded_images="$product_images" :max="5" function="updateProductImages"  />
            @error('product_images')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </x-input.input-group>

        {{-- PRODUCT NAME --}}
        <x-input.input-group>
            <x-slot:label><span class="text-[#F0146C]">*</span>Product Name</x-slot:label>
            <x-input type="text" wire:model.blur="name" type="text" x-ref="prod_name" 
                name="prod_name" id="prod_name" maxlength="120" placeholder="Enter product name" />
            <div class="flex justify-between">
                <div>
                    @error('name')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <p class="text-right text-[11px]"><span x-html="$wire.name.length"></span>/<span x-html="$refs.prod_name.maxLength"></span></p>
            </div>
        </x-input.input-group>

        @isset($assetEdit)
            <div wire:ignore>
                {{-- SKU --}}
                <x-input.input-group>
                    <x-slot:label for="sku" ><span class="text-[#F0146C]">*</span>SKU</x-slot:label>
                    <x-input type="text" readonly disabled wire:model="sku" x-ref="sku" name="sku" id="sku" maxlength="12" class="!bg-rp-neutral-100" />
                    <p class="text-right text-[11px]"><span x-html="$wire.sku.length"></span>/<span x-html="$refs.sku.maxLength"></span></p>
                </x-input.input-group>
            </div>
        @endisset

        {{-- CATEGORY --}}
        <x-input.input-group>
            <x-slot:label for="sku"><span class="text-[#F0146C]">*</span>Category</x-slot:label>
            <x-dropdown.select wire:model.change='category'>
                {{-- options --}}
                <x-dropdown.select.option value="" selected hidden>Select Category</x-dropdown.select.option>
                @foreach ($this->product_categories as $category_option)
                    <x-dropdown.select.option value="{{ $category_option->id }}"
                        class="!bg-gray-100 !cursor-not-allowed" disabled
                        wire:key='category-option-{{ $category_option->id }}'>
                        {{ $category_option->name }}
                    </x-dropdown.select.option>

                    @foreach ($category_option->sub_categories as $sub_category)
                        <x-dropdown.select.option value="{{ $sub_category->id }}"
                            wire:key='sub-category-option-{{ $sub_category->id }}'>
                            {{ '• ' . $sub_category->name }}
                        </x-dropdown.select.option>
                    @endforeach
                @endforeach
            </x-dropdown.select>
            @error('category')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </x-input.input-group>
        
        {{-- DESCRIPTION --}}
        <x-input.input-group>
            <x-slot:label><span class="text-[#F0146C]">*</span>Product Description</x-slot:label>
            <x-input.textarea wire:model.blur='description' x-ref='description' maxlength="2000" rows="10" />
            <div class="flex justify-between">
                <div>
                    @error('description')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <p class="text-right text-[11px]"><span x-html="$wire.description.length"></span>/<span
                    x-html="$refs.description.maxLength"></span></p>
            </div>
        </x-input.input-group>
       
    </div>

    {{-- LOCATION --}}
    <div class="flex flex-col gap-4 p-6 rounded-lg bg-white">
        <h2 class="font-bold text-xl text-rp-neutral-700">Location and Stocks</h2>
        <div class="flex items-center gap-2">
            <x-input wire:model.change='on_demand' type="checkbox" class="text-center" id="allow_on_demand_delivery"/>
            <label for="allow_on_demand_delivery" class="cursor-pointer">Allow for on-demand delivery</label>
        </div>

        <div class="flex flex-col gap-3">
            @foreach ($warehouse_stocks as $key => $warehouse_stock)
                <div class="p-4 flex items-center justify-between bg-rp-neutral-50">
                    <div class="flex items-center gap-2" wire:key='warehouse-stock-{{ $key }}'>
                        <div>
                            <p>Warehouse</p>
                            <x-dropdown.select wire:model.change='warehouse_stocks.{{ $key }}.id' class="w-48">
                                <x-dropdown.select.option value="" selected hidden>
                                    Select Warehouse
                                </x-dropdown.select.option>
                                @foreach ($this->warehouses as $warehouse)
                                    <x-dropdown.select.option value="{{ $warehouse->id }}" wire:key='warehouse-{{ $key }}-{{ $warehouse->id }}'>
                                        {{ $warehouse->name }}
                                    </x-dropdown.select.option>
                                @endforeach
                            </x-dropdown.select>
                        </div>
                        <div>
                            <p>Stocks</p>
                            <x-input wire:model.blur='warehouse_stocks.{{ $key }}.stock' type="number" step="1" min="0" />
                        </div>
                    </div>
                    @if (count($warehouse_stocks) > 1)
                        <div>
                            <div wire:click='remove_location({{ $key }})' class="cursor-pointer">
                                <x-icon.trash />
                            </div>
                        </div>
                    @endif
                </div>   
            @endforeach
            @error('warehouse_stocks')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
            @error('warehouse_stocks.*')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>


        <x-button.filled-button wire:click='add_location' wire:target='add_location' wire:loading.attr="disabled" wire:loading.class='opacity-50' class="mt-3">
            <div class="flex justify-between items-center">
                <p class="text-white text-left">
                    Add location
                </p>
                {{-- Add icon --}}
                <div>
                    <x-icon.rounded-add />
                </div>
            </div>
        </x-button.filled-button>
    </div>

    {{-- PRICING AND SPECIFICATIONS --}}
    <div class="p-6 rounded-lg bg-white">
        <h2 class="font-bold text-xl text-rp-neutral-700 mb-2">Pricing and Specifications</h2>
        
        <div class="space-y-6">

            {{-- Listed Price --}}
            <x-input.input-group>
                <x-slot:label><span class="text-[#F0146C]">*</span>Listed Price</x-slot:label>
                <x-input wire:model.blur='listed_price' type="number" step="0.01" min="0" max="999999999999999.99" placeholder="Enter Price Here" />
                @error('listed_price')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>

            {{-- Condition --}}
            <div>
                <p><span class="text-[#F0146C]">*</span>Condition</p>
                <div class="flex gap-4">
                    @foreach ($this->product_conditions as $key => $condition_options)
                        <div class="flex items-center gap-2" wire:key='condition-{{ $key }}'>
                            <x-input type="radio" wire:model.blur="condition" id="condition-{{ $key }}" name="new" value="{{ $condition_options->slug }}" />
                            <label for="condition-{{ $key }}">{{ $condition_options->name }}</label>
                        </div>
                    @endforeach
                </div>
                @error('condition')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Dimension --}}
            <div class="flex items-center gap-2">
                <x-input.input-group>
                    <x-slot:label><span class="text-[#F0146C]">*</span>Package Weight</x-slot:label>
                    <div class="focus-within:ring-1 flex flex-row border-rp-neutral-500 border-[1px] rounded-lg overflow-hidden bg-white items-center px-2 py-2 gap-2 text-rp-neutral-700">
                        <input wire:model.blur='package_weight' type="number" step="0.01" min="0"
                        class="w-full appearance-none bg-white border-none font-thin outline-none focus:ring-0 p-0 placeholder:text-neutral-400 text-base" >
                        <p class="text-sm font-thin">kg</p>
                    </div>
                </x-input.input-group>
                <x-input.input-group>
                    <x-slot:label><span class="text-[#F0146C]">*</span>Package Length</x-slot:label>
                    <div class="focus-within:ring-1 flex flex-row border-rp-neutral-500 border-[1px] rounded-lg overflow-hidden bg-white items-center px-2 py-2 gap-2 text-rp-neutral-700">
                        <input wire:model.blur='package_length' type="number" step="0.01" min="0"
                        class="w-full appearance-none bg-white border-none font-thin outline-none focus:ring-0 p-0 placeholder:text-neutral-400 text-base" >
                        <p class="text-sm font-thin">cm</p>
                    </div>
                </x-input.input-group>
                <x-input.input-group>
                    <x-slot:label><span class="text-[#F0146C]">*</span>Package Width</x-slot:label>
                    <div class="focus-within:ring-1 flex flex-row border-rp-neutral-500 border-[1px] rounded-lg overflow-hidden bg-white items-center px-2 py-2 gap-2 text-rp-neutral-700">
                        <input wire:model.blur='package_width' type="number" step="0.01" min="0"
                        class="w-full appearance-none bg-white border-none font-thin outline-none focus:ring-0 p-0 placeholder:text-neutral-400 text-base" >
                        <p class="text-sm font-thin">cm</p>
                    </div>
                </x-input.input-group>
                <x-input.input-group>
                    <x-slot:label><span class="text-[#F0146C]">*</span>Package Height</x-slot:label>
                    <div class="focus-within:ring-1 flex flex-row border-rp-neutral-500 border-[1px] rounded-lg overflow-hidden bg-white items-center px-2 py-2 gap-2 text-rp-neutral-700">
                        <input wire:model.blur='package_height' type="number" step="0.01" min="0"
                        class="w-full appearance-none bg-white border-none font-thin outline-none focus:ring-0 p-0 placeholder:text-neutral-400 text-base" >
                        <p class="text-sm font-thin">cm</p>
                    </div>
                </x-input.input-group>
            </div>
            
            <div>
                @error('package_weight')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror

                @error('package_length')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror

                @error('package_width')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror

                @error('package_height')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- BUTTONS (SAVE/CANCEL) --}}
    <div class="flex justify-center items-center gap-3">
        <x-button.filled-button class="w-40" wire:click="save" wire:target='save' wire:loading.attr="disabled" wire:loading.class='cursor-progress'>Save</x-button.filled-button>
        <x-button.outline-button class="w-40" href="{{ route('merchant.seller-center.assets.index', ['merchant' => $this->merchant]) }}">Cancel</x-button.outline-button>
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

    <x-loader.black-screen  wire:loading.flex wire:target="save" />
</x-main.content>