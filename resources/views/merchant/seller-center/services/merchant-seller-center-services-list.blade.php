<x-main.content>
    <x-main.action-header>
        <x-slot:title>Manage Services</x-slot:title>
        <x-slot:actions>
            @if ($can_create)
                <x-button.filled-button href="{{ route('merchant.seller-center.services.create', ['merchant' => $merchant->account_number]) }}">+
                    enlist service</x-button.filled-button>
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
                <x-dropdown.select wire:model.change="main_category">
                    <x-dropdown.select.option value="">Main Category</x-dropdown.select.option>
                    @if ($this->main_categories || $main_category)
                        @foreach ($this->main_categories as $category_option)
                            <x-dropdown.select.option value="{{ $category_option->slug }}"
                                wire:key='main_category-{{ $category_option->slug }}'>
                                {{ $category_option->name }}
                            </x-dropdown.select.option>
                        @endforeach
                    @endif
                </x-dropdown.select>
            </div>
            <div class="flex-1">
                <x-dropdown.select placeholder="Subcategory" wire:model.change="sub_category">
                    <x-dropdown.select.option value="">Subcategory</x-dropdown.select.option>
                    @if (! $this->main_categories || $main_category and $this->sub_categories)
                        @foreach ($this->sub_categories as $category_option)
                            <x-dropdown.select.option wire:key='sub_category-{{ $category_option->slug }}'
                                value="{{ $category_option->slug }}">{{ $category_option->name }}</x-dropdown.select.option>
                        @endforeach
                    @endif
                </x-dropdown.select>
            </div>
            <div class="flex-1">
                <x-dropdown.select wire:model.change='searchDay'>
                    <x-dropdown.select.option value="">Operating Days</x-dropdown.select.option>
                    <x-dropdown.select.option value="monday">Monday</x-dropdown.select.option>
                    <x-dropdown.select.option value="tuesday">Tuesday</x-dropdown.select.option>
                    <x-dropdown.select.option value="wednesday">Wednesday</x-dropdown.select.option>
                    <x-dropdown.select.option value="thursday">Thursday</x-dropdown.select.option>
                    <x-dropdown.select.option value="friday">Friday</x-dropdown.select.option>
                    <x-dropdown.select.option value="saturday">Saturday</x-dropdown.select.option>
                    <x-dropdown.select.option value="sunday">Sunday</x-dropdown.select.option>
                </x-dropdown.select>
            </div>
            <div class="flex-1">
                <x-dropdown.select wire:model.change='approval_status'>
                    <x-dropdown.select.option value="">Approval Status</x-dropdown.select.option>
                    <x-dropdown.select.option value="review">Review</x-dropdown.select.option>
                    <x-dropdown.select.option value="approved">Approved</x-dropdown.select.option>
                    <x-dropdown.select.option value="rejected">Rejected</x-dropdown.select.option>
                    <x-dropdown.select.option value="suspended">Suspended</x-dropdown.select.option>
                </x-dropdown.select>
            </div>
        </div>
    </x-layout.search-container>

    <div>
        <x-table.rounded>
            <x-slot:table_header>
                <x-table.rounded.th>Service Name</x-table.rounded.th>
                <x-table.rounded.th>Operating Days</x-table.rounded.th>
                <x-table.rounded.th>Location</x-table.rounded.th>
                <x-table.rounded.th>
                    <div class="flex flex-row items-center">
                        <span>Scheduled</span>
                        <button wire:click="sortTable('bookings_count')">
                            <x-icon.sort />
                        </button>
                    </div>
                </x-table.rounded.th>
                <x-table.rounded.th>
                    <div class="flex flex-row items-center">
                        <span>Inquiries</span>
                        <button wire:click="sortTable('inquiries_count')">
                            <x-icon.sort />
                        </button>
                    </div>
                </x-table.rounded.th>
                <x-table.rounded.th>
                    Active
                </x-table.rounded.th>
                <x-table.rounded.th class="w-[40px]"></x-table.rounded.th>
            </x-slot:table_header>

            <x-slot:table_data>
                @foreach ($services as $service)
                    <tr>
                        <td class="pt-8"></td>
                    </tr>
                    <x-table.rounded.row x-data="{
                            isMenuOpen: false
                        }">
                        <x-table.rounded.td>
                            <div class="flex flex-row">
                                <img class="object-cover h-20 mr-4 rounded-md min-w-20"
                                    src="{{ $this->get_media_url($service->first_image, 'thumbnail') }}" alt="">
                                <div class="w-[180px] px-1">
                                    <p class="truncate">{{ $service->name }}</p>
                                    <span>
                                        @if ($service->approval_status === 'review')
                                            <p class='text-rp-yellow-600'>Pending Approval </p>
                                        @elseif ($service->approval_status === 'approved')
                                            <p class='text-rp-green-600'>Approved</span>
                                            @elseif ($service->approval_status === 'rejected')
                                            <p class='text-rp-red-500'>Rejected</p>
                                        @elseif ($service->approval_status === 'suspended')
                                            <p class='text-rp-red-500'>Suspended</p>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            {{ ucwords(implode(', ', $this->arrange_service_days(array_keys($service->service_days)))) }}
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            {{ $service->location->address }}
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            {{ $service->bookings_count }}
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            {{ $service->inquiries_count }}
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            @if ($service->approval_status === 'approved')
                                <div class="toggle-switch">
                                    <input class="toggle-input" id="toggle-{{ $service->id }}" type="checkbox"
                                        {{ $service->is_active ? 'checked' : '' }}
                                        wire:change="update_active_status({{ $service }})"
                                        {{ $service->approval_status !== 'approved' ? 'disabled' : '' }}>
                                    <label class="toggle-label" for="toggle-{{ $service->id }}"></label>
                                </div>
                            @endif
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            <div class="relative w-max">
                                <button @click="isMenuOpen=true">
                                    <x-icon.kebab-menu />
                                </button>
                                <x-dropdown.dropdown-list x-cloak x-show="isMenuOpen"
                                    @click.away="isMenuOpen=false;$wire.set_delete_id(null)"
                                    class="absolute right-0 top-[100%] w-[10rem]">
                                    <a href="{{ route('merchant.seller-center.services.show.bookings', ['merchant' => $merchant->account_number, 'service' => $service->id]) }}">
                                        <x-dropdown.dropdown-list.item>
                                            View bookings and inquiries
                                        </x-dropdown.dropdown-list.item>
                                    </a>

                                    <a href="{{ route('merchant.seller-center.services.show', ['merchant' => $merchant->account_number, 'service' => $service->id]) }}">
                                        <x-dropdown.dropdown-list.item>
                                            View Service
                                        </x-dropdown.dropdown-list.item>
                                    </a>

                                    @if ($can_edit)
                                        <a href="{{ route('merchant.seller-center.services.show.edit', ['merchant' => $merchant->account_number, 'service' => $service->id]) }}">
                                            <x-dropdown.dropdown-list.item>
                                                Edit
                                            </x-dropdown.dropdown-list.item>
                                        </a>
                                    @endif

                                    @if ($can_create)
                                        <x-dropdown.dropdown-list.item
                                            wire:click.prevent="set_copy_id({{ $service->id }})">
                                            <p>Copy</p>
                                            @if ($service->id == $actions_id['copy'])
                                                <div class="flex flex-row gap-2">
                                                    <svg wire:click.stop="commit_copy" @click="isMenuOpen=false"
                                                        width="24" class="fill-gray-500 hover:fill-green-700"
                                                        height="24" viewBox="0 0 24 24">
                                                        <path
                                                            d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81V16.18C2 19.83 4.17 22 7.81 22H16.18C19.82 22 21.99 19.83 21.99 16.19V7.81C22 4.17 19.83 2 16.19 2ZM16.78 9.7L11.11 15.37C10.97 15.51 10.78 15.59 10.58 15.59C10.38 15.59 10.19 15.51 10.05 15.37L7.22 12.54C6.93 12.25 6.93 11.77 7.22 11.48C7.51 11.19 7.99 11.19 8.28 11.48L10.58 13.78L15.72 8.64C16.01 8.35 16.49 8.35 16.78 8.64C17.07 8.93 17.07 9.4 16.78 9.7Z" />
                                                    </svg>
                                                    <svg wire:click.stop="set_copy_id(null)" width="24"
                                                        class="fill-gray-500 hover:fill-gray-700" height="24"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81V16.18C2 19.83 4.17 22 7.81 22H16.18C19.82 22 21.99 19.83 21.99 16.19V7.81C22 4.17 19.83 2 16.19 2ZM15.36 14.3C15.65 14.59 15.65 15.07 15.36 15.36C15.21 15.51 15.02 15.58 14.83 15.58C14.64 15.58 14.45 15.51 14.3 15.36L12 13.06L9.7 15.36C9.55 15.51 9.36 15.58 9.17 15.58C8.98 15.58 8.79 15.51 8.64 15.36C8.35 15.07 8.35 14.59 8.64 14.3L10.94 12L8.64 9.7C8.35 9.41 8.35 8.93 8.64 8.64C8.93 8.35 9.41 8.35 9.7 8.64L12 10.94L14.3 8.64C14.59 8.35 15.07 8.35 15.36 8.64C15.65 8.93 15.65 9.41 15.36 9.7L13.06 12L15.36 14.3Z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </x-dropdown.dropdown-list.item>
                                    @endif

                                    @if ($can_delete)
                                        <x-dropdown.dropdown-list.item
                                            wire:click.prevent="set_delete_id({{ $service->id }})"
                                            class="flex flex-row items-center justify-between px-3 py-2 cursor-pointer hover:bg-rp-neutral-100">
                                            <p>Delete</p>
                                            @if ($service->id == $actions_id['delete'])
                                                <div class="flex flex-row gap-2">
                                                    <svg wire:click.stop="commit_delete" @click="isMenuOpen=false"
                                                        width="24" class="fill-gray-500 hover:fill-red-700"
                                                        height="24" viewBox="0 0 24 24">
                                                        <path
                                                            d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81V16.18C2 19.83 4.17 22 7.81 22H16.18C19.82 22 21.99 19.83 21.99 16.19V7.81C22 4.17 19.83 2 16.19 2ZM16.78 9.7L11.11 15.37C10.97 15.51 10.78 15.59 10.58 15.59C10.38 15.59 10.19 15.51 10.05 15.37L7.22 12.54C6.93 12.25 6.93 11.77 7.22 11.48C7.51 11.19 7.99 11.19 8.28 11.48L10.58 13.78L15.72 8.64C16.01 8.35 16.49 8.35 16.78 8.64C17.07 8.93 17.07 9.4 16.78 9.7Z" />
                                                    </svg>
                                                    <svg wire:click.stop="set_delete_id(null)" width="24"
                                                        class="fill-gray-500 hover:fill-green-700" height="24"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M16.19 2H7.81C4.17 2 2 4.17 2 7.81V16.18C2 19.83 4.17 22 7.81 22H16.18C19.82 22 21.99 19.83 21.99 16.19V7.81C22 4.17 19.83 2 16.19 2ZM15.36 14.3C15.65 14.59 15.65 15.07 15.36 15.36C15.21 15.51 15.02 15.58 14.83 15.58C14.64 15.58 14.45 15.51 14.3 15.36L12 13.06L9.7 15.36C9.55 15.51 9.36 15.58 9.17 15.58C8.98 15.58 8.79 15.51 8.64 15.36C8.35 15.07 8.35 14.59 8.64 14.3L10.94 12L8.64 9.7C8.35 9.41 8.35 8.93 8.64 8.64C8.93 8.35 9.41 8.35 9.7 8.64L12 10.94L14.3 8.64C14.59 8.35 15.07 8.35 15.36 8.64C15.65 8.93 15.65 9.41 15.36 9.7L13.06 12L15.36 14.3Z" />
                                                    </svg>
                                                </div>
                                            @endif
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
    @if ($services->hasPages())
        <div class="flex items-center justify-end w-full gap-8">
            <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                <button wire:click="previousPage" {{ $services->onFirstPage() ? 'disabled' : '' }}
                    class="{{ $services->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg width="7" height="13" viewBox="0 0 7 13" fill="none">
                        <path d="M6 11.5001L1 6.50012L6 1.50012" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <button class="h-full px-4 py-2 bg-white border-r cursor-default">{{ $element }}</button>
                    @else
                        <button wire:click="gotoPage({{ $element }})"
                            class="h-full border-r px-4 py-2 {{ $element == $services->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
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

            <div class="mt-4">
                <p class="font-normal">Showing {{ $services->firstItem() }} ~ {{ $services->lastItem() }} items of
                    {{ $services->total() }} total results.</p>
            </div>
        </div>
    @endif

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

    {{-- Loader --}}
    <x-loader.black-screen wire:loading wire:target='commit_copy,commit_delete'>
        <x-loader.clock />
    </x-loader.black-screen>
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