<x-main.content class="!px-16 !py-10" x-data="{
    transactionDetailsModal: {
        visible: false,
    }
}" x-init="function() {
    Livewire.on('showTransactionDetails', function() {
        transactionDetailsModal.visible = true;
    });
}">

    <livewire:components.layout.admin.merchant-details-header :merchant="$merchant" />

    <div class="mt-8">
        <div class="flex">
            {{-- 1st Column: Left Sidebar --}}
            <x-layout.admin.merchant-details.transactions.left-sidebar :merchant="$merchant" class="w-60" />

            {{-- 2nd Column: Table --}}
            <div class="w-[calc(100%-240px)] pl-4">

                {{-- Search --}}
                <x-layout.search-container class="mb-5">
                    <x-input.search wire:model.live='searchTerm' icon_position="left" />
                </x-layout.search-container>


                @if (!empty($employees))
                    <div class="grid w-full grid-cols-5 gap-2">
                        @foreach ($employees as $key => $employee)
                            <div wire:key="employee-{{ $key }}" :key=""
                                class="relative flex flex-col justify-between h-auto px-3 py-4 break-words bg-white border rounded-xl"
                                x-data="{
                                    isAccountMenuOpen: false,
                                    isEmployeeDeleteModalVisible: false,
                                    closeMenu: function() {
                                        this.isAccountMenuOpen = false;
                                    },
                                }">

                                <div class="absolute cursor-pointer right-3" @click="isAccountMenuOpen=true;">
                                    <div>
                                        <x-icon.kebab-menu />
                                    </div>
                                    <x-dropdown.dropdown-list x-cloak x-show="isAccountMenuOpen"
                                        @click.away="isAccountMenuOpen=false;"
                                        class="z-10 absolute top-[100%] -right-28 rounded-md border w-36">
                                        <x-dropdown.dropdown-list.item>
                                            <a class="block"
                                                href="{{ route('admin.manage-merchants.show.transactions.employees.details', ['merchant' => $merchant, 'employee' => $employee]) }}">
                                                View
                                            </a>
                                        </x-dropdown.dropdown-list.item>
                                        @if ($merchant->id === 1 && $merchant->user_id !== $employee->user_id)
                                            <x-dropdown.dropdown-list.item @click="isEmployeeDeleteModalVisible=true;">
                                                Delete
                                            </x-dropdown.dropdown-list.item>
                                        @endif
                                    </x-dropdown.dropdown-list>
                                </div>

                                <div class="flex flex-col items-center w-full">
                                    <div class="w-28 h-28">
                                        <img src="{{ url('images/user/default-avatar.png') }}"alt=""
                                            class="object-cover w-full h-full rounded-full">
                                    </div>

                                    <p class="w-full overflow-hidden text-xl font-bold text-center break-words truncate">
                                        {{ $employee->user->name }}</p>
                                    </p>
                                    <span class="w-full text-sm text-center truncate">{{ $employee->occupation }}</span>
                                </div>
                                <div class="flex flex-col text-sm text-center mt-7">
                                    <span class="w-full truncate">
                                        {{ '(+' .
                                            substr($employee->user->phone_number, 0, 2) .
                                            ') ' .
                                            substr($employee->user->phone_number, 2, 3) .
                                            '-' .
                                            substr($employee->user->phone_number, 5, 3) .
                                            '-' .
                                            substr($employee->user->phone_number, 8) }}
                                    </span>
                                    <span class="overflow-hidden break-words truncate">{{ $employee->user->email }}</span>
                                    <p class="truncate">Access level: <span
                                            class="text-primary-600 ">{{ $employee->access_level->name }}</span></p>
                                </div>
                                {{-- Confirmation Modal --}}
                                @if ($merchant->id === 1 && $merchant->user_id !== $employee->user_id)
                                    <x-modal x-model="isEmployeeDeleteModalVisible">
                                        <x-modal.confirmation-modal>
                                            <x-slot:title>Confirmation</x-slot:title>
                                            <x-slot:message>
                                                Are you sure you want to delete this Employee?
                                            </x-slot:message>
                                            <x-slot:action_buttons>
                                                <x-button.outline-button class="flex-1" color="primary"
                                                    @click="isEmployeeDeleteModalVisible=false;">cancel</x-button.outline-button>
                                                <x-button.filled-button class="flex-1" color="primary"
                                                    @click="isEmployeeDeleteModalVisible=false;$wire.delete({{ $employee->id }})">yes</x-button.filled-button>
                                            </x-slot:action_buttons>
                                        </x-modal.confirmation-modal>
                                    </x-modal>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="flex items-end justify-end w-full gap-8 mt-4">
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
                                            class="h-full bg-white border-r px-4 py-2 {{ $element == $employees->currentPage() ? 'cursor-default' : 'cursor-pointer' }}">{{ $element }}</button>
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
        </div>
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
