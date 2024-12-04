<x-main.content class="!px-16 !py-10">
    <x-main.action-header>
        <x-slot:title>Employees</x-slot:title>
        <x-slot:actions>
            <x-button.filled-button href="{{ route('admin.employees.create') }}" color="primary">add
                employee</x-button.filled-button>
        </x-slot:actions>
    </x-main.action-header>

    {{-- Search --}}
    <div>
        <x-layout.search-container class="mb-7">
            <x-input.search icon_position="left" wire:model.live='searchTerm' />
        </x-layout.search-container>
        @if ($employees->isEmpty())
            {{-- If no employee --}}
            <div class="w-[27rem] flex flex-col items-center mt-14 mx-auto space-y-4">
                <div>
                    <img src="{{ url('images/placeholder/no-employee.svg') }}" alt="No Employee">
                </div>
                <p class="text-center">Whoops! It seems you haven't added any employees yet.</p>
                <x-button.filled-button color="primary" href="{{ route('admin.employees.create') }}">click here to add
                    one!</x-button.filled-button>
            </div>
        @else
            <div class="grid w-full grid-cols-6 gap-2">

                @foreach ($employees as $employee)
                    <div wire:key="" :key=""
                        class="relative flex flex-col justify-between h-auto px-3 py-4 break-words bg-white border rounded-xl"
                        x-data="{
                            isAccountMenuOpen: false,
                            isEmployeeDeleteModalVisible: false,
                            closeMenu: function() {
                                this.isAccountMenuOpen = false;
                            },
                            {{-- handleDeleteEmployee: function(employeeId) {
                                @this.call('delete', employeeId);
                            } --}}
                        }">

                        <div class="absolute cursor-pointer right-3" @click="isAccountMenuOpen=true;">
                            <button>
                                <x-icon.kebab-menu />
                            </button>
                            <x-dropdown.dropdown-list x-cloak x-show="isAccountMenuOpen"
                                @click.away="isAccountMenuOpen=false;"
                                class="z-10 absolute top-[100%] -right-28 rounded-md border w-36">
                                <x-dropdown.dropdown-list.item>
                                    <a class="block"
                                        href="{{ route('admin.employees.show', ['employee' => $employee->id]) }}">
                                        View
                                    </a>
                                </x-dropdown.dropdown-list.item>
                                @if ($employee->user_id !== $merchant->user_id)
                                    <x-dropdown.dropdown-list.item @click="isEmployeeDeleteModalVisible=true;">
                                        Delete
                                    </x-dropdown.dropdown-list.item>
                                @endif
                            </x-dropdown.dropdown-list>
                        </div>

                        <div class="flex flex-col items-center w-full">
                            <div class="w-32 h-32">
                                {{-- @if ($employee->user->profile_picture)
                                    <img src="{{ $this->get_media_url($employee->user->profile_picture, 'thumbnail') }}"
                                        alt="" class="object-cover w-full h-full rounded-full">
                                @else --}}
                                    <img src="{{ url('images/user/default-avatar.png') }}" alt=""
                                        class="object-cover w-full h-full rounded-full">
                                {{-- @endif --}}
                            </div>

                            <p class="w-full overflow-hidden text-xl font-bold text-center break-words truncate">
                                {{ $employee->user->name }}</p>
                            </p>
                            <span class="w-full text-sm text-center truncate">{{ $employee->occupation }}</span>
                        </div>
                        <div class="flex flex-col text-sm text-center mt-7">
                            <span class="w-full truncate">{{ $this->format_phone_number($employee->user->phone_number, $employee->user->phone_iso) }}</span>
                            <span class="overflow-hidden break-words truncate">{{ $employee->user->email }}</span>
                            <p class="truncate">Access level: <span
                                    class="text-primary-600 ">{{ $employee->access_level->name }}</span></p>
                        </div>
                        {{-- Confirmation Modal --}}
                        <x-modal x-model="isEmployeeDeleteModalVisible">
                            <x-modal.confirmation-modal>
                                <x-slot:title>Confirmation</x-slot:title>
                                <x-slot:message>
                                    Are you sure you want to delete this Employee?
                                </x-slot:message>
                                <x-slot:action_buttons>
                                    <x-button.outline-button class="flex-1" @click="isEmployeeDeleteModalVisible=false;"
                                        color="primary">cancel</x-button.outline-button>
                                    <x-button.filled-button class="flex-1" color="primary"
                                        @click="isEmployeeDeleteModalVisible=false;$wire.delete({{ $employee->id }})">yes</x-button.filled-button>
                                </x-slot:action_buttons>
                            </x-modal.confirmation-modal>
                        </x-modal>
                    </div>
                @endforeach
            </div>
            {{-- Pagination --}}
            <div class="flex items-center justify-center w-full gap-8">
                @if ($employees->hasPages())
                    <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                        <button wire:click="previousPage" {{ $employees->onFirstPage() ? 'disabled' : '' }}
                            class="{{ $employees->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                                    class="h-full border-r px-4 py-2 {{ $element == $employees->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                            @endif
                        @endforeach

                        <button wire:click="nextPage" {{ !$employees->hasMorePages() ? 'disabled' : '' }}
                            class="{{ !$employees->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                            <svg  width="7" height="13" viewBox="0 0 7 13"
                                fill="none">
                                <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                @endif
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
</x-main.content>
