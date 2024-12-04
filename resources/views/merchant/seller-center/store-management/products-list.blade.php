<div class="fixed inset-0 z-50 w-screen overflow-y-auto">
    <div class="flex items-center justify-center min-h-full" x-data="{ isFilterOpen: false }">
        <div class="relative transform overflow-hidden rounded-xl bg-white  shadow-xl transition-all py-7 px-6 w-[1070px] h-auto my-14"
            x-data @click.outside="$wire.clear_product_filter();featuredProducts=false">
            <div class="flex items-center justify-between mb-4">
                <p class="text-2xl font-bold text-rp-neutral-700">Featured Products</p>

                {{-- BUTTON TO CLOSE MODAL --}}
                <button wire:click="clear_product_filter" @click="featuredProducts=false">
                    <x-icon.close />
                </button>
            </div>

            <div class="flex flex-col gap-2">

                <div class="flex flex-row items-center gap-3">
                    <x-input.search icon_position="left" class="flex-1" wire:model.live='searchTerm' />
                    <div @click="isFilterOpen=!isFilterOpen"
                        class="flex items-center gap-2 px-4 shadow-md cursor-pointer h-14">
                        <p class="text-sm font-bold uppercase">filters</p>
                        <div>
                            <x-icon.triangle-down />
                        </div>
                    </div>
                </div>


                <div x-cloak x-show="isFilterOpen" class="flex flex-wrap items-center gap-2 ">

                    <div class="flex items-center flex-1 gap-2 bg-rp-neu">
                        @if (!empty($main_category))
                            <div class="rounded-full cursor-pointer" wire:click="clear_main_category">
                                <x-icon.close-filled fill="#647887" />
                            </div>
                        @endif
                        <x-dropdown.select class="w-full text-sm rounded-md border-rp-neutral-500"
                            wire:model.live="main_category" wire:change="getProductSubCategories">
                            <x-dropdown.select.option value="" selected disabled>Main
                                Category</x-dropdown.select.option>
                            @foreach ($main_categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                                {{-- @foreach ($category->sub_categories as $s_cat)
                                    <option value="{{ $s_cat->id }}">
                                        {{ $s_cat->name }}
                                    </option>
                                @endforeach --}}
                            @endforeach
                        </x-dropdown.select>
                    </div>
                    <div class="flex items-center flex-1 gap-2 bg-rp-neu">
                        @if (!empty($sub_category))
                            <div class="rounded-full cursor-pointer" wire:click="clear_sub_category">
                                <x-icon.close-filled fill="#647887" />
                            </div>
                        @endif
                        <x-dropdown.select class="w-full text-sm rounded-md border-rp-neutral-500"
                            wire:model.live="sub_category">
                            <x-dropdown.select.option value="" selected disabled>Sub
                                Category</x-dropdown.select.option>
                            @if ($sub_categories)
                                @foreach ($sub_categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                    {{-- @foreach ($category->sub_categories as $s_cat)
                                    <option value="{{ $s_cat->id }}">
                                        {{ $s_cat->name }}
                                    </option>
                                @endforeach --}}
                                @endforeach
                            @endif
                        </x-dropdown.select>
                    </div>
                    <div class="flex items-center flex-1 gap-2">
                        @if (!empty($condition))
                            <div class="rounded-full cursor-pointer" wire:click="clear_condition">
                                <x-icon.close-filled fill="#647887" />
                            </div>
                        @endif
                        <x-dropdown.select class="w-full text-sm rounded-md border-rp-neutral-500"
                            wire:model.live="condition">
                            <x-dropdown.select.option value="" selected
                                disabled>Condition</x-dropdown.select.option>
                            <x-dropdown.select.option value="brand-new">Brand New</x-dropdown.select.option>
                            <x-dropdown.select.option value="used">Used</x-dropdown.select.option>
                        </x-dropdown.select>
                    </div>

                    <button
                        class="text-center duration-300 transition focus-within:ring-2 focus:outline-none p-[10px] rounded-[9px] border border-rp-neutral-500"
                        @click="showPriceRange=!showPriceRange">
                        PRICE RANGE: {{ $ranges['min_price'] . ' - ' . $ranges['max_price'] }}
                    </button>

                    <button
                        class="text-center duration-300 transition focus-within:ring-2 focus:outline-none p-[10px] rounded-[9px] border border-rp-neutral-500"
                        @click="showStockRange=!showStockRange">
                        STOCK RANGE: {{ $ranges['min_stock'] . ' - ' . $ranges['max_stock'] }}
                    </button>
                </div>

                <div x-cloak x-show="isFilterOpen" class="flex justify-center p-2 space-x-3 rounded-md">
                    <!-- Price Range -->
                    <div x-cloak x-show="showPriceRange" class="p-2 rounded-md bg-rp-neutral-100">
                        <p class="text-xs">Price Range:</p>
                        <div class="relative flex items-center w-full gap-2">
                            <div class="flex items-center justify-center gap-4">
                                <div>
                                    <x-input type="text" maxlength="5" wire:model.live="ranges.min_price" />
                                </div>
                                -
                                <div>
                                    <x-input type="text" maxlength="5" wire:model.live="ranges.max_price" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Range -->
                    <div x-cloak x-show="showStockRange" class="p-2 rounded-md bg-rp-neutral-100">
                        <p class="text-xs">Stock Range:</p>
                        <div class="relative flex items-center w-full gap-2">
                            <div class="flex items-center justify-center gap-4">
                                <div>
                                    <x-input type="text" maxlength="5" wire:model.live="ranges.min_stock" />
                                </div>
                                -
                                <div>
                                    <x-input type="text" maxlength="5" wire:model.live="ranges.max_stock" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            {{-- START OF TABLE SECTION --}}
            <div class="w-full mt-5 overflow-auto">
                <table class="min-w-full bg-white rounded-lg table-fixed">
                    {{-- TABLE HEADINGS --}}
                    <tr class="">
                        <th class="text-left text-rp-neutral-700 text-[19px] font-bold  py-4 px-2">
                            Products
                        </th>
                        {{-- <th class="text-left text-rp-neutral-700 text-[19px] font-bold  py-4">
                            Variation
                        </th> --}}
                        <th class="text-left text-rp-neutral-700 text-[19px] font-bold  py-4">
                            Price
                        </th>
                        <th class="text-left text-rp-neutral-700 text-[19px] font-bold  py-4">
                            Stock
                        </th>
                        <th class="text-left text-rp-neutral-700 text-[19px] font-bold  py-4">
                            Sold
                        </th>
                    </tr>

                    @forelse ($products as $product)
                        <tr class="hover:bg-rp-neutral-50 text-[13px] text-rp-neutral-700">
                            <td class="py-6  hover:bg-rp-neutral-50  w-[350px] rounded-l-2xl mt-[14px]">
                                <div class="flex gap-2 pl-3">
                                    <div class="flex items-center gap-2">
                                        <input
                                            class="rounded  text-rp-purple-600 focus:ring-rp-purple-600  border-1 border-[#D0D5DD] "
                                            type="checkbox" id="{{ $product->id }}"
                                            {{ $product->is_featured ? 'checked' : '' }}
                                            wire:change="product_feature_change({{ $product }})">
                                        <img class="w-[100px] object-cover rounded-md"
                                            src="{{ $this->get_media_url($product->first_image, 'thumbnail') }}" alt="">
                                    </div>
                                    <div class="text-[16px] font-bold text-rp-neutral-700 text-wrap basis-6/12">
                                        <p>
                                            {{ $product->name }}
                                        </p>
                                    </div>
                                </div>

                            </td>
                            {{-- <td class="py-6  hover:bg-rp-neutral-50  mt-[14px]">Variant 1</td> --}}
                            <td class="py-6  hover:bg-rp-neutral-50  mt-[14px]">P
                                {{ number_format($product->price, 2) }}</td>
                            <td class="py-6  hover:bg-rp-neutral-50  mt-[14px]">{{ $product->stock_count }}
                            </td>
                            <td class="py-6  hover:bg-rp-neutral-50  mt-[14px] rounded-r-2xl">
                                {{ $product->sold_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td>
                                No products to show ...
                            </td>
                        </tr>
                    @endforelse
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex items-center justify-center w-full gap-8">
                @if ($products->hasPages())
                    <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                        <button wire:click="previousPage" {{ $products->onFirstPage() ? 'disabled' : '' }}
                            class="{{ $products->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                            <svg width="7" height="13" viewBox="0 0 7 13" fill="none">
                                <path d="M6 11.5001L1 6.50012L6 1.50012" stroke="#647887" stroke-width="1.66667"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        {{-- Pagination Elements --}}
                        @foreach ($products_elements as $element)
                            {{-- "Three Dots" Separator --}}
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
                            <svg width="7" height="13" viewBox="0 0 7 13" fill="none">
                                <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>

                    {{-- <div class="mt-4">
                        <p class="font-normal">Showing {{ $services->firstItem() }} ~
                            {{ $products->lastItem() }} items of
                            {{ $products->total() }} total results.</p>
                    </div> --}}
                @endif
            </div>
        </div>
    </div>
</div>
