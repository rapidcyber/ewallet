<x-main.content>
    <x-main.action-header>
        <x-slot:title>Employees</x-slot:title>
        <x-slot:actions>
            @if ($color === 'primary')
                <x-button.filled-button href="{{ route('admin.employees.create') }}" :color="$color">
                    add employee
                </x-button.filled-button>
            @else
                <x-button.filled-button
                    href="{{ route('merchant.financial-transactions.employees.create', ['merchant' => $merchant->account_number]) }}">add
                    employee</x-button.filled-button>
            @endif
        </x-slot:actions>
    </x-main.action-header>

    {{-- Search --}}
    <div>
        <x-layout.search-container class="mb-7">
            <x-input.search icon_position="left" wire:model.live='searchTerm' />
        </x-layout.search-container>
        @if (!$employees)
            {{-- If no employee --}}
            <div class="w-[27rem] flex flex-col items-center mt-14 mx-auto space-y-4">
                <div>
                    <img src="{{ url('images/placeholder/no-employee.svg') }}" alt="No Employee">
                </div>
                <p class="text-center">Whoops! It seems you haven't added any employees yet.</p>
                <x-button.filled-button>click here to add one!</x-button.filled-button>
            </div>
        @else
            <div class="grid w-full grid-cols-6 gap-2">

                @foreach ($employees as $key => $employee)
                    <div wire:key="employee-{{ $key }}" :key="{{ 'employee-' . $key }}"
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
                                @if ($color === 'primary')
                                    <a class="block"
                                        href="{{ route('admin.employees.show', ['employee' => $employee->id]) }}">
                                        <x-dropdown.dropdown-list.item>
                                            View
                                        </x-dropdown.dropdown-list.item>
                                    </a>
                                @else    
                                    <a class="block"
                                        href="{{ route('merchant.financial-transactions.employees.show', ['merchant' => $merchant->account_number, 'employee' => $employee->id]) }}">
                                        <x-dropdown.dropdown-list.item>
                                            View
                                        </x-dropdown.dropdown-list.item>
                                    </a>
                                @endif
                                @if ($employee->access_level->slug !== 'owner' || $employee->user_id != auth()->id())
                                    <x-dropdown.dropdown-list.item @click="isEmployeeDeleteModalVisible=true;">
                                        Delete
                                    </x-dropdown.dropdown-list.item>
                                @endif
                            </x-dropdown.dropdown-list>
                        </div>

                        <div class="flex flex-col items-center w-full">
                            <div class="w-32 h-32">
                                <img src="{{ url('images/user/default-avatar.png') }}"alt=""
                                    class="object-cover w-full h-full rounded-full">
                            </div>

                            <p class="w-full overflow-hidden text-xl font-bold text-center break-words truncate">
                                {{ $employee->user->name }}</p>
                            </p>
                            <span class="w-full text-sm text-center truncate">{{ $employee->occupation }}</span>
                        </div>
                        <div class="flex flex-col text-sm text-center mt-7">
                            <span class="w-full truncate">{{ $this->format_phone_number($employee->user->phone_number, $employee->user->phone_iso) }}</span>
                            <span class="overflow-hidden break-words truncate">{{ $employee->user->email }}</span>
                            <p class="truncate">Access level: <span class="{{ $color === 'primary' ? 'text-primary-600' : 'text-rp-red-500' }}">{{ $employee->access_level->name }}</span></p>
                        </div>
                        {{-- Confirmation Modal --}}
                        <x-modal x-model="isEmployeeDeleteModalVisible">
                            <x-modal.confirmation-modal>
                                <x-slot:title>Remove Employee?</x-slot:title>
                                <x-slot:message>
                                    This employee will be removed from this merchant.
                                </x-slot:message>
                                <x-slot:action_buttons>
                                    <x-button.outline-button :color="$color" class="flex-1"
                                        @click="isEmployeeDeleteModalVisible=false;">cancel</x-button.outline-button>
                                    <x-button.filled-button :color="$color" class="flex-1"
                                        @click="isEmployeeDeleteModalVisible=false;$wire.delete({{ $employee->id }})">proceed</x-button.filled-button>
                                </x-slot:action_buttons>
                            </x-modal.confirmation-modal>
                        </x-modal>
                    </div>
                @endforeach
            </div>
            {{-- Pagination --}}
            @if ($employees->hasPages())
                <div class="flex items-center justify-center w-full gap-8">
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
                </div>
            @endif
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

    <x-loader.black-screen wire:loading wire:target='delete'>
        <x-loader.clock />
    </x-loader.black-screen>
</x-main.content>
