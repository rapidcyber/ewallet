<div class="fixed inset-0 z-50 w-screen overflow-y-auto">
    <div class="flex items-center justify-center min-h-full">

        <div class="relative transform overflow-hidden rounded-xl bg-white  shadow-xl transition-all  py-7 px-6 w-[1070px] h-auto my-14 "
            x-data @click.outside="$wire.clear_service_filter(); featuredServices=false">
            <div class="flex items-center justify-between mb-4">
                <p class="text-2xl font-bold text-rp-neutral-700">Featured Services</p>

                {{-- BUTTON TO CLOSE MODAL --}}
                <button wire:click="clear_service_filter" @click="featuredServices=false">
                    <x-icon.close />
                </button>
            </div>

            <div class="flex flex-col gap-3" x-data="{ isFilterOpen: false }">

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

                <div x-cloak x-show="isFilterOpen" class="flex flex-row gap-2">
                    <div class="flex items-center flex-1 gap-1">
                        @if (!empty($main_category))
                            <div class="rounded-full cursor-pointer" wire:click="clear_main_category">
                                <x-icon.close-filled fill="#647887" />
                            </div>
                        @endif
                        <x-dropdown.select class="w-full text-sm rounded-md border-rp-neutral-500"
                            wire:model.live="main_category" wire:change="getServiceSubCategories">
                            <x-dropdown.select.option value="" selected disabled>Main
                                Category</x-dropdown.select.option>
                            @foreach ($main_categories as $category)
                                <x-dropdown.select.option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </x-dropdown.select.option>
                            @endforeach
                        </x-dropdown.select>
                    </div>
                    <div class="flex items-center flex-1 gap-1">
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
                                    <x-dropdown.select.option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </x-dropdown.select.option>
                                @endforeach
                            @endif

                        </x-dropdown.select>
                    </div>
                    <div class="flex items-center flex-1 gap-1">
                        @if (!empty($service_day))
                            <div class="rounded-full cursor-pointer" wire:click="clear_service_day">
                                <x-icon.close-filled fill="#647887" />
                            </div>
                        @endif
                        <x-dropdown.select class="w-full text-sm rounded-md border-rp-neutral-500"
                            wire:model.live="service_day">
                            <x-dropdown.select.option value="" selected disabled>Operating
                                Days</x-dropdown.select.option>
                            <x-dropdown.select.option value="monday">Monday</x-dropdown.select.option>
                            <x-dropdown.select.option value="tuesday">Tuesday</x-dropdown.select.option>
                            <x-dropdown.select.option value="wednesday">Wednesday</x-dropdown.select.option>
                            <x-dropdown.select.option value="thursday">Thursday</x-dropdown.select.option>
                            <x-dropdown.select.option value="friday">Friday</x-dropdown.select.option>
                            <x-dropdown.select.option value="saturday">Saturday</x-dropdown.select.option>
                            <x-dropdown.select.option value="sunday">Sunday</x-dropdown.select.option>

                        </x-dropdown.select>
                    </div>

                </div>
            </div>

            {{-- TABLE VIEW --}}
            <div class="w-full my-5 overflow-auto">
                <table class="min-w-full bg-white rounded-lg table-fixed border-spacing-2">
                    {{-- TABLE HEADING SECTION --}}
                    <tr class="">
                        <th class="text-left text-rp-neutral-700 text-[19px] font-bold  py-4">
                            Service Name</th>
                        <th class="text-left text-rp-neutral-700 text-[19px] font-bold  py-4 w-[210px]">
                            Operating Days</th>
                        <th class="text-left text-rp-neutral-700 text-[19px] font-bold  py-4 w-[196px]">
                            Location</th>
                        <th class="text-left text-rp-neutral-700 text-[19px] font-bold  py-4 w-[206px]">
                            Scheduled</th>
                        <th class="text-left text-rp-neutral-700 text-[19px] font-bold  py-4">
                            Inquiries
                        </th>
                    </tr>

                    {{-- START OF TABLE ROWS --}}
                    @forelse ($services as $service)
                        <tr class="hover:bg-rp-neutral-50 text-[13px] text-rp-neutral-700" :key="{{ $service->id }}">
                            <td class="py-6 hover:bg-rp-neutral-50  w-[310px] rounded-l-2xl mt-[14px]">
                                <div class="flex gap-2 pl-3">
                                    <div class="flex items-center gap-2">
                                        <input
                                            class="rounded mr-2  text-rp-purple-600 focus:ring-rp-purple-600  border-1 border-[#D0D5DD]"
                                            type="checkbox" id="service-{{ $service->id }}"
                                            {{ $service->is_featured ? 'checked' : '' }}
                                            wire:change="service_feature_change({{ $service }})">
                                        <img class="w-[100px] object-cover rounded-md"
                                            src="{{ $this->get_media_url($service->first_image, 'thumbnail') }}" alt="">
                                    </div>
                                    <div class="text-[16px] font-bold text-rp-neutral-700 text-wrap basis-6/12">
                                        <p>
                                            {{ $service->name }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-6 hover:bg-rp-neutral-50 mt-[14px] w-[210px]">
                                {{ ucwords(implode(', ', $this->arrange_service_days(array_keys($service->service_days)))) }}
                            </td>
                            <td class="py-6 hover:bg-rp-neutral-50  mt-[14px] w-[196px]">
                                {{ $service->location->address }}
                            </td>
                            <td class="py-6 hover:bg-rp-neutral-50  mt-[14px] w-[206px] text-center">
                                {{ $service->bookings()->whereHas('status', function ($query) {
                                        $query->whereIn('slug', ['booked', 'in-progress']);
                                    })->count() }}
                            </td>
                            <td class="py-6 hover:bg-rp-neutral-50  mt-[14px] text-center rounded-r-2xl">
                                {{ $service->inquiries()->count() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td>
                                No services to show ...
                            </td>
                        </tr>
                    @endforelse
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex items-center justify-center w-full gap-8">
                @if ($services->hasPages())
                    <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                        <button wire:click="previousPage" {{ $services->onFirstPage() ? 'disabled' : '' }}
                            class="{{ $services->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                            <svg width="7" height="13" viewBox="0 0 7 13" fill="none">
                                <path d="M6 11.5001L1 6.50012L6 1.50012" stroke="#647887" stroke-width="1.66667"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        {{-- Pagination Elements --}}
                        @foreach ($services_elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <button
                                    class="h-full px-4 py-2 bg-white border-r cursor-default">{{ $element }}</button>
                            @else
                                <button wire:click="gotoPage({{ $element }})"
                                    class="h-full bg-white border-r px-4 py-2 {{ $element == $services->currentPage() ? 'cursor-default' : 'cursor-pointer' }}">{{ $element }}</button>
                            @endif
                        @endforeach

                        <button wire:click="nextPage" {{ !$services->hasMorePages() ? 'disabled' : '' }}
                            class="{{ !$services->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                            <svg width="7" height="13" viewBox="0 0 7 13" fill="none">
                                <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>

                    {{-- <div class="mt-4">
                        <p class="font-normal">Showing {{ $services->firstItem() }} ~
                            {{ $services->lastItem() }} items of
                            {{ $services->total() }} total results.</p>
                    </div> --}}
                @endif
            </div>
        </div>
    </div>
</div>
