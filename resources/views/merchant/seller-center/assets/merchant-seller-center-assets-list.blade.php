<x-main.content>
    <x-main.action-header x-data="{ showNoWarehousesModal: false }" class="mb-8">
        <x-slot:title>Manage Assets</x-slot:title>
        <x-slot:actions>
            @if ($can_create)
                @if ($this->has_warehouses)
                    <x-button.filled-button href="{{ route('merchant.seller-center.assets.create', ['merchant' => $merchant]) }}">+
                        add product</x-button.filled-button>
                @else
                    <x-button.filled-button @click="showNoWarehousesModal=true">+ add product</x-button.filled-button>
                    {{-- Confirmation Modal --}}
                    <x-modal x-model="showNoWarehousesModal">
                        <x-modal.confirmation-modal class="!w-[450px]">
                            <x-slot:title>No Warehouse Found</x-slot:title>
                            <x-slot:message>
                                You currently don't have any warehouses to assign your products. Please create one first.
                            </x-slot:message>
                            <x-slot:action_buttons>
                                <x-button.outline-button class="flex-1" @click="showNoWarehousesModal=false;">cancel</x-button.outline-button>
                                <x-button.filled-button href="{{ route('merchant.seller-center.logistics.warehouse-shipping', ['merchant' => $merchant]) }}" class="flex-1">create</x-button.filled-button>
                            </x-slot:action_buttons>
                        </x-modal.confirmation-modal>
                    </x-modal>
                @endif
            @endif
        </x-slot:actions>
    </x-main.action-header>

    <x-layout.search-container x-data="{ isFilterOpen: false }" class="mb-8">
        <div class="flex flex-row items-center gap-3">
            <x-input.search icon_position="left" class="flex-1" wire:model.live='searchTerm' />
            <div role="button" tabindex="0" @keyup.enter="isFilterOpen=!isFilterOpen" @click="isFilterOpen=!isFilterOpen" class="flex items-center gap-2 px-4 shadow-md cursor-pointer h-14">
                <p class="text-sm font-bold uppercase">filters</p>
                <div>
                    <x-icon.triangle-down />
                </div>
            </div>
        </div>
        <div x-cloak x-show="isFilterOpen" class="flex flex-row gap-2">
            <div class="flex-1">
                <x-dropdown.select wire:model.lazy="main_category" wire:change="getSubCategories">
                    <x-dropdown.select.option value="" selected>Main Category</x-dropdown.select.option>
                    @if (!is_string($main_categories) || $main_category->isNotEmpty())
                        @foreach ($main_categories as $category_option)
                            <x-dropdown.select.option value="{{ $category_option->id }}"
                                wire:key='main_category-{{ $category_option->id }}'>
                                {{ $category_option->name }}
                            </x-dropdown.select.option>
                        @endforeach
                    @endif
                </x-dropdown.select>
            </div>
            <div class="flex-1">
                <x-dropdown.select placeholder="Subcategory" wire:model.lazy="sub_category">
                    <x-dropdown.select.option value="" selected>Subcategory</x-dropdown.select.option>
                    @if ((!is_string($main_categories) || $main_category->isNotEmpty()) && $sub_categories)
                        @foreach ($sub_categories as $category_option)
                            <x-dropdown.select.option
                                value="{{ $category_option->id }}">{{ $category_option->name }}</x-dropdown.select.option>
                        @endforeach
                    @endif
                </x-dropdown.select>
            </div>
            <div class="flex-1">
                <x-dropdown.select wire:model.lazy="price_range">
                    <x-dropdown.select.option value="" selected>Price Range</x-dropdown.select.option>
                    <x-dropdown.select.option value="501-1000">₱501 - ₱1,000</x-dropdown.select.option>
                    <x-dropdown.select.option value="1001-2500">₱1,001 - ₱2,500</x-dropdown.select.option>
                    <x-dropdown.select.option value="2501-5000">₱2,501 - ₱5,000</x-dropdown.select.option>
                    <x-dropdown.select.option value="5001-10000">₱5,001 - ₱10,000</x-dropdown.select.option>
                    <x-dropdown.select.option value="10001-20000">₱10,001 - ₱20,000</x-dropdown.select.option>
                    <x-dropdown.select.option value="20001-50000">₱20,001 - ₱50,000</x-dropdown.select.option>
                    <x-dropdown.select.option value="50000">₱50,000 above</x-dropdown.select.option>

                </x-dropdown.select>
            </div>
            <div class="flex-1">
                <x-dropdown.select wire:model.lazy="stock_range">
                    <x-dropdown.select.option value="" selected>Stock Range</x-dropdown.select.option>
                    <x-dropdown.select.option value="0">Out of Stock</x-dropdown.select.option>
                    <x-dropdown.select.option value="1-10">1 - 10</x-dropdown.select.option>
                    <x-dropdown.select.option value="11-50">11 - 50</x-dropdown.select.option>
                    <x-dropdown.select.option value="51-100">51 - 100</x-dropdown.select.option>
                    <x-dropdown.select.option value="101-200">101 - 200</x-dropdown.select.option>
                    <x-dropdown.select.option value="201-500">201 - 500</x-dropdown.select.option>
                    <x-dropdown.select.option value="501-1000">501 - 1000</x-dropdown.select.option>
                    <x-dropdown.select.option value="1001-5000">1,001 - 5,000</x-dropdown.select.option>
                    <x-dropdown.select.option value="5001-10000">5,001 - 10,000</x-dropdown.select.option>
                    <x-dropdown.select.option value="10000">10,000 above</x-dropdown.select.option>
                </x-dropdown.select>
            </div>
            <div class="flex-1">
                <x-dropdown.select wire:model.lazy='condition'>
                    <x-dropdown.select.option value="" selected>Condition</x-dropdown.select.option>
                    <x-dropdown.select.option value="brand_new">Brand New</x-dropdown.select.option>
                    <x-dropdown.select.option value="used">Used</x-dropdown.select.option>
                </x-dropdown.select>
            </div>
            <div class="flex-1">
                <x-dropdown.select wire:model.lazy='approval_status'>
                    <x-dropdown.select.option value="" selected>Approval
                        Status</x-dropdown.select.option>
                    <x-dropdown.select.option value="review">Pending Approval</x-dropdown.select.option>
                    <x-dropdown.select.option value="approved">Approved</x-dropdown.select.option>
                    <x-dropdown.select.option value="rejected">Rejected</x-dropdown.select.option>
                    <x-dropdown.select.option value="suspended">Suspended</x-dropdown.select.option>
                </x-dropdown.select>
            </div>
        </div>
    </x-layout.search-container>

    {{-- TABLE --}}

    <div class="w-full">
        <x-table.rounded>
            <x-slot:table_header>
                <x-table.rounded.th>Products</x-table.rounded.th>
                <x-table.rounded.th>SKU(s)</x-table.rounded.th>
                <x-table.rounded.th>
                    <div class="flex flex-row items-center">
                        <span>Price</span>
                        <button wire:click="sortTable('price')">
                            <x-icon.sort />
                        </button>
                    </div>
                </x-table.rounded.th>
                <x-table.rounded.th>
                    <div class="flex flex-row items-center">
                        <span>Stock</span>
                        <button wire:click="sortTable('stock_count')">
                            <x-icon.sort />
                        </button>
                    </div>
                </x-table.rounded.th>
                <x-table.rounded.th>
                    <div class="flex flex-row items-center">
                        <span>Sold</span>
                        <button wire:click="sortTable('sold_count')">
                            <x-icon.sort />
                        </button>
                    </div>
                </x-table.rounded.th>
                <x-table.rounded.th>Active</x-table.rounded.th>
                <x-table.rounded.th class="w-[50px]"></x-table.rounded.th>
            </x-slot:table_header>
            <x-slot:table_data>
                @foreach ($products as $product)
                    <tr>
                        <td class="pt-8"></td>
                    </tr>
                    <x-table.rounded.row x-data="{ isMenuOpen: false }">
                        <x-table.rounded.td>
                            <div class="flex flex-row">
                                <img src="{{ $product->first_image ? $this->get_media_url($product->first_image, 'thumbnail') : url('images/user/default-avatar.png') }}" class="object-cover h-20 mr-4 rounded-md min-w-20">
                                <div class="w-[calc(100%-56px)] break-words px-1">
                                    <p class='text-rp-netral-700'>{{ $product->name }}</p>
                                    <p>
                                        @if ($product->approval_status === 'review')
                                            <p class='text-rp-yellow-600'>Pending Approval </p>
                                        @elseif ($product->approval_status === 'approved')
                                            <p class='text-rp-green-600'>Approved</span>
                                            @elseif ($product->approval_status === 'rejected')
                                            <p class='text-rp-red-500'> Rejected</p>
                                        @elseif ($product->approval_status === 'suspended')
                                            <p class='text-rp-red-500'> Suspended</p>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            {{ $product->sku }}
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            {{ \Number::currency($product->price, 'PHP') }}
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            {{ $product->stock_count }}
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            {{ $product->sold_count }}
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            @if ($product->approval_status === 'approved')
                                <div class="toggle-switch">
                                    <input class="toggle-input" id="toggle-{{ $product->sku }}" type="checkbox"
                                        wire:change="update_active_status('{{ $product->sku }}')"
                                        {{ $product->is_active ? 'checked' : '' }}
                                        {{ $product->approval_status !== 'approved' ? 'disabled' : '' }}>
                                    <label class="toggle-label" for="toggle-{{ $product->sku }}"></label>
                                </div>
                            @endif
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            <div class="relative w-max">
                                <button @click="isMenuOpen=true">
                                    <x-icon.kebab-menu />
                                </button>
                                {{-- Dropdown --}}
                                <x-dropdown.dropdown-list x-cloak x-show="isMenuOpen" @click.away="isMenuOpen=false"
                                    class="absolute right-0 top-[100%] w-28">
                                    <a 
                                        href="{{ route('merchant.seller-center.assets.show', ['merchant' => $merchant, 'product' => $product->id]) }}">
                                        <x-dropdown.dropdown-list.item>
                                            View Product
                                        </x-dropdown.dropdown-list.item>
                                    </a>
                                    @if ($can_edit)
                                        <a 
                                            href="{{ route('merchant.seller-center.assets.edit', ['merchant' => $merchant, 'product' => $product->id]) }}">
                                            <x-dropdown.dropdown-list.item>
                                                Edit
                                            </x-dropdown.dropdown-list.item>
                                        </a>
                                    @endif
                                    @if ($can_create)
                                        <x-dropdown.dropdown-list.item wire:click="replicateProduct({{ $product->id }})">
                                            Copy
                                        </x-dropdown.dropdown-list.item>
                                    @endif
                                    @if ($can_delete)
                                        <x-dropdown.dropdown-list.item @click="$wire.deleteProduct({{ $product->id }})">
                                            Delete
                                        </x-dropdown.dropdown-list.item>
                                    @endif
                                </x-dropdown.dropdown-list>
                            </div>
                        </x-table.rounded.td>
                    </x-table.rounded.row>
                @endforeach
            </x-slot:table_data>
        </x-table.rounded>
    </div>

    {{-- Pagination --}}
    <div class="flex items-center justify-end w-full gap-8">
        @if ($products->hasPages())
            <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                <button wire:click="previousPage" {{ $products->onFirstPage() ? 'disabled' : '' }}
                    class="{{ $products->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M6 11.5001L1 6.50012L6 1.50012" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <!-- Pagination Elements -->
                @foreach ($elements as $element)
                    <!-- "Three Dots" Separator -->
                    @if (is_string($element))
                        <button class="h-full px-4 py-2 bg-white border-r cursor-default">{{ $element }}</button>
                    @else
                        <button wire:click="gotoPage({{ $element }})"
                            class="h-full border-r px-4 py-2 {{ $element == $products->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                    @endif
                @endforeach

                <button wire:click="nextPage" {{ !$products->hasMorePages() ? 'disabled' : '' }}
                    class="{{ !$products->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <div class="mt-4">
                <p class="font-normal">Showing {{ $products->firstItem() }} ~ {{ $products->lastItem() }} items of
                    {{ $products->total() }} total results.</p>
            </div>
        @endif
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

    <x-loader.black-screen wire:loading.flex wire:target="replicateProduct,deleteProduct" class="z-20" />
</x-main.content>



@push('style')
    <style>
        /* Genel stil */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 24px;

        }

        /* Giriş stil */
        .toggle-switch .toggle-input {
            display: none;
        }

        /* Anahtarın stilinin etrafındaki etiketin stil */
        .toggle-switch .toggle-label {
            position: absolute;
            top: 0;
            left: 0;
            width: 40px;
            height: 24px;
            background-color: #90A1AD;
            border-radius: 34px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        /* Anahtarın yuvarlak kısmının stil */
        .toggle-switch .toggle-label::before {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            background-color: #fff;
            box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s;
        }

        /* Anahtarın etkin hale gelmesindeki stil değişiklikleri */
        .toggle-switch .toggle-input:checked+.toggle-label {
            background-color: #FF3D8F;
        }

        .toggle-switch .toggle-input:checked+.toggle-label::before {
            transform: translateX(16px);
        }

        /* Light tema */
        .toggle-switch.light .toggle-label {
            background-color: #BEBEBE;
        }

        .toggle-switch.light .toggle-input:checked+.toggle-label {
            background-color: #9B9B9B;
        }

        .toggle-switch.light .toggle-input:checked+.toggle-label::before {
            transform: translateX(6px);
        }

        /* Dark tema */
        .toggle-switch.dark .toggle-label {
            background-color: #4B4B4B;
        }

        .toggle-switch.dark .toggle-input:checked+.toggle-label {
            background-color: #717171;
        }

        .toggle-switch.dark .toggle-input:checked+.toggle-label::before {
            transform: translateX(16px);
        }
    </style>
@endpush
