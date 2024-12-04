<div class="absolute flex flex-col gap-8 bg-white p-10 rounded-2xl w-[980px] max-w-[90%] max-h-[95%] overflow-y-auto">
    {{-- CLOSE BUTTON --}}
    <button class="absolute top-6 right-6" @click="isPackageQuantityModalOpen=false">
        <x-icon.close />
    </button>

    {{-- HEADING --}}
    <div>
        <h3 class="text-2xl font-bold mb-2">Package Quantity</h3>
    </div>

    
    <div x-data="{ isFilterOpen: false }" class="space-y-3.5">
        <div class="flex flex-row items-center gap-3">
            <x-input.search icon_position="left" class="flex-1" wire:model.live='searchTerm' />
            <button @click="isFilterOpen=!isFilterOpen" class="flex items-center gap-2 px-4 shadow-md cursor-pointer h-14">
                <p class="text-sm font-bold uppercase">filters</p>
                <div>
                    <x-icon.triangle-down />
                </div>
            </button>
        </div>
        <div x-cloak x-show="isFilterOpen" class="flex flex-row gap-2">
            <div class="flex-1">
                <x-dropdown.select wire:model.live="main_category">
                    <x-dropdown.select.option value="" selected>Main Category</x-dropdown.select.option>
                    @foreach ($this->main_categories as $key => $main_category_option)
                        <x-dropdown.select.option value="{{ $main_category_option->slug }}" wire:key='main_category-{{ $key }}'>
                            {{ $main_category_option->name }}
                        </x-dropdown.select.option>
                    @endforeach

                </x-dropdown.select>
            </div>
            <div class="flex-1">

                <x-dropdown.select placeholder="Subcategory" wire:model.live="sub_category">
                    <x-dropdown.select.option value="" selected>Subcategory</x-dropdown.select.option>
                    @if ($main_category and $this->sub_categories->isNotEmpty())
                        @foreach ($this->sub_categories as $sub_category_option)
                            <x-dropdown.select.option value="{{ $sub_category_option->slug }}" wire:key='sub_category-{{ $key }}'>
                                {{ $sub_category_option->name }}
                            </x-dropdown.select.option>
                        @endforeach
                    @endif
                </x-dropdown.select>

            </div>
            <div class="flex-1">
                <x-dropdown.select wire:model.live="price_range">
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
                <x-dropdown.select wire:model.live="stock_range">
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
                <x-dropdown.select wire:model.live='condition'>
                    <x-dropdown.select.option value="" selected>Condition</x-dropdown.select.option>
                    @foreach ($this->product_conditions as $key => $condition_option)
                        <x-dropdown.select.option value="{{ $key }}" wire:key='condition-{{ $key }}'>
                            {{ $condition_option }}
                        </x-dropdown.select.option>
                    @endforeach
                </x-dropdown.select>
            </div>
            <div class="flex-1">
                <x-dropdown.select wire:model.live='approval_status'>
                    <x-dropdown.select.option value="" selected>Approval
                        Status</x-dropdown.select.option>
                    <x-dropdown.select.option value="review">Pending Approval</x-dropdown.select.option>
                    <x-dropdown.select.option value="approved">Approved</x-dropdown.select.option>
                    <x-dropdown.select.option value="rejected">Rejected</x-dropdown.select.option>
                    <x-dropdown.select.option value="suspended">Suspended</x-dropdown.select.option>
                </x-dropdown.select>
            </div>
        </div>
    </div>
    
    <div class="overflow-auto">
        <table class="w-full table-auto break-words">
            <thead>
                <tr>
                    <th class="text-left px-3 py-2">Products</th>
                    {{-- <th class="text-left px-3 py-2">Variation</th> --}}
                    <th class="text-left px-3 py-2">Price</th>
                    <th class="text-left px-3 py-2">Stock in this warehouse</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $key => $product)
                    <tr wire:key='product-{{ $key }}'>
                        <td class="text-left px-3 py-2 rounded-l-2xl w-80">
                            <div class="flex flex-row">
                                <div class="w-16 h-16 bg-black">
                                    <img src="{{ $this->get_media_url($product->first_image, 'thumbnail') }}" alt="" class="w-full h-full object-cover" />
                                </div>
                                <div class="w-[calc(100%-80px)] px-2">
                                    <p class="text-rp-neutural-700 font-bold">{{ $product->name }}</p>
                                </div>
                            </div>
                        </td>
                        {{-- <td class="text-left px-3 py-2">
                            Variant 1
                        </td> --}}
                        <td class="text-left px-3 py-2">
                            P {{ number_format($product->price, 2) }}
                        </td>
                        <td class="text-left px-3 py-2 rounded-r-2xl">
                            {{ $product->stocks }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    <div class="flex items-center justify-center w-full gap-8">
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
                        <button
                            class="h-full px-4 py-2 bg-white border-r cursor-default">{{ $element }}</button>
                    @else
                        <button wire:click="gotoPage({{ $element }})"
                            class="h-full bg-white border-r px-4 py-2 {{ $element == $products->currentPage() ? 'cursor-default' : 'cursor-pointer' }}">{{ $element }}</button>
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
        @endif
    </div>
</div>