<x-main.content class="!px-16 !py-10" x-data="{
    confirmationModal: {
        {{-- Must be entangled --}}
        visible: $wire.entangle('confirmationModalVisible'),
        actionType: ''
    },

    groupConfirmationModal: {
        visible: $wire.entangle('groupConfirmationModalVisible'),
        actionType: ''
    }
}">



    <x-main.title class="mb-8">Manage Users</x-main.title>

    {{-- Filters --}}
    <div class="grid grid-cols-5 gap-3 mb-8">
        <button wire:click='handleFilterBoxClick(1)'
            class="{{ $activeBox === 1 ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2 2xl:gap-4">
                <x-icon.user width="24" height="24" fill="{{ $activeBox === 1 ? '#ffff' : '#7f56d9' }}" />
                <p  class="{{ $activeBox === 1 ? 'text-white' : 'text-rp-neutral-600' }}">All users</p>
            </div>
            <span class="font-bold">{{ $this->allUsersCount }}</span>
        </button>

        <button wire:click='handleFilterBoxClick(2)'
            class="{{ $activeBox === 2 ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2 2xl:gap-4">
                <x-icon.hourglass width="24" height="24" fill="{{ $activeBox === 2 ? '#ffff' : '#7f56d9' }}" />
                <p  class="{{ $activeBox === 2 ? 'text-white' : 'text-rp-neutral-600' }}">Pending</p>
            </div>
            <span class="font-bold">{{ $this->pendingUsersCount }}</span>
        </button>

        <button wire:click='handleFilterBoxClick(3)'
            class="{{ $activeBox === 3 ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2 2xl:gap-4">
                <x-icon.check width="24" height="24" fill="{{ $activeBox === 3 ? '#ffff' : '#7f56d9' }}" />
                <p class="{{ $activeBox === 3 ? 'text-white' : 'text-rp-neutral-600' }}">Active</p>
            </div>
            <span class="font-bold">{{ $this->verifiedUsersCount }}</span>
        </button>

        <button wire:click='handleFilterBoxClick(4)'
            class="{{ $activeBox === 4 ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2 2xl:gap-4">
                <x-icon.close-filled fill="{{ $activeBox === 4 ? '#ffff' : '#7f56d9' }}" />
                <div class="{{ $activeBox === 4 ? 'text-white' : 'text-rp-neutral-600' }} flex flex-col 2xl:flex-row 2xl:gap-2 text-left">
                    <span>Denied /</span>
                    <span>Deactivated</span>
                </div>
            </div>
            <span class="font-bold">{{ $this->deniedUsersCount }}</span>
        </button>

        <a href="{{ route('admin.manage-users.requests.index') }}"
            class="{{ isset($is_active_page) ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2 2xl:gap-4">
                <div class="{{ isset($is_active_page) ? 'text-white' : 'text-rp-neutral-600' }} flex flex-col 2xl:flex-row 2xl:gap-2 text-left">
                    <span>Profile Update</span>
                    <span>Requests</span>
                </div>
            </div>
            <span class="font-bold">{{ $this->profileUpdateRequestsCount }}</span>
        </a>

    </div>

    <x-layout.search-container class="mb-8">
        <x-input.search wire:model.live.debounce.300ms='searchTerm' />
    </x-layout.search-container>

    {{-- Table --}}
    <div class="overflow-auto p-3 bg-white rounded-2xl">
        <x-table.standard>
            <x-slot:table_header>
                <x-table.standard.th class="w-max">
                    <x-input type="checkbox" wire:model.live="selectAll" wire:change="handleSelectAllCheckbox($event.target.checked, {{$users->getCollection()}})" />
                </x-table.standard.th>
                @if (count($checkedUsers) > 0)
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        ({{ count($checkedUsers) }} selected)
                    </x-table.standard.th>
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        {{-- Blank --}}
                    </x-table.standard.th>
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        {{-- Blank --}}
                    </x-table.standard.th>
                    <x-table.standard.th class="w-32 min-w-32 max-w-32">
                        {{-- Blank --}}
                    </x-table.standard.th>
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        {{-- Blank --}}
                    </x-table.standard.th>
                    <x-table.standard.th class="w-32 min-w-32 max-w-32">
                        {{-- Blank --}}
                    </x-table.standard.th>
                    <x-table.standard.th class="min-w-64 max-w-64 2xl:min-w-80 2xl:max-w-80">
                        <div class="flex items-center gap-2">
                            <x-button.filled-button size="sm" @click="groupConfirmationModal.actionType='activate';groupConfirmationModal.visible=true" color="primary" class="w-1/2" size="sm">activate</x-button.filled-button>
                            <x-button.outline-button size="sm" @click="groupConfirmationModal.actionType='deactivate';groupConfirmationModal.visible=true" color="primary" class="w-1/2" size="sm">deactivate</x-button.outline-button>
                        </div>
                    </x-table.standard.th>
                @else
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        Name
                    </x-table.standard.th>
                    <x-table.standard.th class="w-56 min-w-56 max-w-56">
                        Email
                    </x-table.standard.th>
                    <x-table.standard.th class="w-48 min-w-48 max-w-48">
                        Contact Number
                    </x-table.standard.th>
                    <x-table.standard.th class="w-24 min-w-24 max-w-24">
                        Country
                    </x-table.standard.th>
                    <x-table.standard.th class="w-48 min-w-48 max-w-48">
                        <div class="flex flex-row items-center">
                            <span>Registration Date</span>
                            <button wire:click="sortTable">
                                <x-icon.sort />
                            </button>
                        </div>
                    </x-table.standard.th>
                    <x-table.standard.th class="w-32 min-w-32 max-w-32">
                        Status
                    </x-table.standard.th>
                    <x-table.standard.th class="min-w-52 max-w-52 2xl:min-w-80 2xl:max-w-80">
                        Actions
                    </x-table.standard.th>
                @endif
            </x-slot:table_header>
            <x-slot:table_data>
                @foreach ($users as $key => $user)
                    <x-table.standard.row wire:key="{{ $user->id }}">
                        <x-table.standard.td class="w-max">
                            {{-- <input type="checkbox" wire:model.live="checkedUsers" value="'{{ $user->id }}'" @if(in_array($user->id, $this->checkedUsers)) checked @endif /> --}}
                            <x-input type="checkbox" wire:model.live="checkedUsers" value="{{ $user->id }}" wire:change="handleSingleSelectCheckbox({{ $users->getCollection() }})" />
                        </x-table.standard.td>
                        <x-table.standard.td class="w-52 min-w-52 max-w-52">
                            <a  href="{{ route('admin.manage-users.show.basic-details', $user->id) }}" class="flex flex-row items-center gap-2 hover:underline">
                                <div class="w-10 h-10 min-w-10 min-h-10 rounded-full">
                                    {{-- @if ($profile_picture = $user->getFirstMedia('profile_picture'))
                                        <img src="{{ $this->get_media_url($profile_picture) }}" alt=""
                                            class="w-full h-full object-cover rounded-full">
                                    @else --}}
                                        <img src="{{ url('images/user/default-avatar.png') }}" alt=""
                                            class="w-full h-full object-cover rounded-full">
                                    {{-- @endif --}}
                                </div>
                                <p class="break-words">{{ $user->name }}</p>
                            </a>
                        </x-table.standard.td>
                        <x-table.standard.td class="w-56 min-w-56 max-w-56">
                            {{ $user->email }}
                        </x-table.standard.td>
                        <x-table.standard.td class="w-48 min-w-48 max-w-48">
                            {{ $this->format_phone_number($user->phone_number, $user->phone_iso) }}
                        </x-table.standard.td>
                        <x-table.standard.td class="w-24 min-w-24 max-w-24">
                            {{ $user->phone_iso }}
                        </x-table.standard.td>
                        <x-table.standard.td class="w-48 min-w-48 max-w-48">
                            {{ \Carbon\Carbon::parse($user->created_at)->timezone('Asia/Manila')->format('Y-m-d') }}
                        </x-table.standard.td>
                        @switch($user->profile->status)
                            @case('pending')
                                    <x-table.standard.td class="w-32 min-w-32 max-w-32">
                                        <x-status color="purple">Pending</x-status>
                                    </x-table.standard.td>
                                    <x-table.standard.td class="min-w-52 max-w-52 2xl:min-w-80 2xl:max-w-80">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="flex items-center w-full gap-1">
                                                <div class="flex xl:flex-col w-5/6 2xl:flex-row gap-1">
                                                    @if (count($checkedUsers) === 0)
                                                    <x-button.filled-button color="primary"
                                                            @click="confirmationModal.visible=true;confirmationModal.actionType='approve';$wire.set('user_id',{{ $user->id }})"
                                                            class="xl:w-full 2xl:w-1/2">approve</x-button.filled-button>
                                                        <x-button.outline-button color="primary"
                                                            @click="confirmationModal.visible=true;confirmationModal.actionType='deny';$wire.set('user_id',{{ $user->id }})"
                                                            class="xl:w-full 2xl:w-1/2">deny</x-button.outline-button>
                                                    @endif
                                                </div>
                                                <div class="w-max">
                                                    <a  href="{{ route('admin.manage-users.show.basic-details', $user->id) }}"><x-icon.chevron-right class="w-full" /></a>
                                                </div>
                                            </div>

                                        </div>
                                    </x-table.standard.td>
                                @break
    
                                @case('verified')
                                    <x-table.standard.td class="w-32 min-w-32 max-w-32">
                                        <x-status color="green">Active</x-status>
                                    </x-table.standard.td>
                                    <x-table.standard.td class="min-w-52 max-w-52 2xl:min-w-80 2xl:max-w-80">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="flex items-center w-full gap-1">
                                                <div class="flex xl:flex-col w-5/6 2xl:flex-row gap-1">
                                                    @if (count($checkedUsers) === 0)
                                                        <x-button.filled-button color="primary"
                                                            @click="confirmationModal.visible=true;confirmationModal.actionType='deactivate';$wire.set('user_id',{{ $user->id }})"
                                                            class="xl:w-full 2xl:w-1/2">deactivate</x-button.filled-button>
                                                    @endif
                                                </div>
                                                <div class="w-max">
                                                    <a  href="{{ route('admin.manage-users.show.basic-details', $user->id) }}"><x-icon.chevron-right class="w-full" /></a>
                                                </div>
                                            </div>

                                        </div>
                                    </x-table.standard.td>
                                @break
    
                                @case('rejected')
                                    <x-table.standard.td class="w-32 min-w-32 max-w-32">
                                        <x-status color="red">Denied</x-status>
                                    </x-table.standard.td>
                                    <x-table.standard.td class="min-w-52 max-w-52 2xl:min-w-80 2xl:max-w-80">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="flex items-center w-full gap-1">
                                                <div class="flex xl:flex-col w-5/6 2xl:flex-row gap-1">
                                                    @if (count($checkedUsers) === 0)
                                                        <x-button.filled-button color="primary"
                                                            @click="confirmationModal.visible=true;confirmationModal.actionType='reactivate';$wire.set('user_id',{{ $user->id }})"
                                                            class="xl:w-full 2xl:w-1/2">reactivate</x-button.filled-button>
                                                    @endif
                                                </div>
                                                <div class="w-max">
                                                    <a  href="{{ route('admin.manage-users.show.basic-details', $user->id) }}"><x-icon.chevron-right class="w-full" /></a>
                                                </div>
                                            </div>
                                        </div>
                                    </x-table.standard.td>
                                @break
    
                                @case('deactivated')
                                    <x-table.standard.td class="w-32 min-w-32 max-w-32">
                                        <x-status color="red">Deactivated</x-status>
                                    </x-table.standard.td>
                                    <x-table.standard.td class="min-w-52 max-w-52 2xl:min-w-80 2xl:max-w-80">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="flex items-center w-full gap-1">
                                                <div class="flex xl:flex-col w-5/6 2xl:flex-row gap-1">
                                                    @if (count($checkedUsers) === 0)
                                                        <x-button.filled-button color="primary"
                                                            @click="confirmationModal.visible=true;confirmationModal.actionType='reactivate';$wire.set('user_id',{{ $user->id }})"
                                                            class="xl:w-full 2xl:w-1/2">reactivate</x-button.filled-button>
                                                    @endif
                                                </div>
                                                <div class="w-max">
                                                    <a  href="{{ route('admin.manage-users.show.basic-details', $user->id) }}"><x-icon.chevron-right class="w-full" /></a>
                                                </div>
                                            </div>

                                        </div>
                                    </x-table.standard.td>
                                @break
                                @default
                                    -
                                @break
                            @endswitch
                    </x-table.standard.row>
                @endforeach
            </x-slot:table_data>
        </x-table.standard>
    </div>

    {{-- Pagination --}}
    <div class="w-full flex items-center justify-center gap-8">
        @if ($users->hasPages())
            <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                <button wire:click="previousPage" {{ $users->onFirstPage() ? 'disabled' : '' }} @click="$wire.set('checkedUsers', [])"
                    class="{{ $users->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M6 11.5001L1 6.50012L6 1.50012" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <button class="h-full bg-white border-r px-4 py-2 cursor-default">{{ $element }}</button>
                    @else
                        <button wire:click="gotoPage({{ $element }})" @click="$wire.set('checkedUsers', [])"
                            class="h-full px-4 py-2 border-r {{ $element == $users->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer  bg-white' }}">{{ $element }}</button>
                    @endif
                @endforeach
                <button wire:click="nextPage" {{ !$users->hasMorePages() ? 'disabled' : '' }} @click="$wire.set('checkedUsers', [])"
                    class="{{ !$users->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <x-modal x-model="confirmationModal.visible">
        <x-modal.confirmation-modal title="Confirmation">
            <x-slot:message>
                Are you sure you want to <span x-text="confirmationModal.actionType"></span> this user?
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button
                    wire:target='handleApproveButtonClick,handleDenyButtonClick,handleDeactivateButtonClick,handleReactivateButtonClick'
                    wire:loading.attr='disabled' wire:loading.class='cursor-progress'
                    @click="confirmationModal.visible=false;$set('user_id',null)" color="primary" class="w-1/2">Go
                    Back</x-button.outline-button>
                <template x-if="confirmationModal.actionType === 'approve'">
                    <x-button.filled-button
                        wire:target='handleApproveButtonClick,handleDenyButtonClick,handleDeactivateButtonClick,handleReactivateButtonClick'
                        wire:loading.attr='disabled' wire:loading.class='cursor-progress'
                        wire:click='handleApproveButtonClick' color="primary"
                        class="w-1/2">approve</x-button.filled-button>
                </template>
                <template x-if="confirmationModal.actionType === 'deny'">
                    <x-button.filled-button
                        wire:target='handleApproveButtonClick,handleDenyButtonClick,handleDeactivateButtonClick,handleReactivateButtonClick'
                        wire:loading.attr='disabled' wire:loading.class='cursor-progress'
                        wire:click='handleDenyButtonClick' color="primary"
                        class="w-1/2">deny</x-button.filled-button>
                </template>
                <template x-if="confirmationModal.actionType === 'deactivate'">
                    <x-button.filled-button
                        wire:target='handleApproveButtonClick,handleDenyButtonClick,handleDeactivateButtonClick,handleReactivateButtonClick'
                        wire:loading.attr='disabled' wire:loading.class='cursor-progress'
                        wire:click='handleDeactivateButtonClick' color="primary"
                        class="w-1/2">deactivate</x-button.filled-button>
                </template>
                <template x-if="confirmationModal.actionType === 'reactivate'">
                    <x-button.filled-button
                        wire:target='handleApproveButtonClick,handleDenyButtonClick,handleDeactivateButtonClick,handleReactivateButtonClick'
                        wire:loading.attr='disabled' wire:loading.class='cursor-progress'
                        wire:click='handleReactivateButtonClick' color="primary"
                        class="w-1/2">reactivate</x-button.filled-button>
                </template>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>

    </x-modal>

    <x-modal x-model="groupConfirmationModal.visible">
        <x-modal.confirmation-modal title="Confirmation">
            <x-slot:message>
                Are you sure you want to <span x-text="groupConfirmationModal.actionType"></span> these users?
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button
                    wire:target='handleApproveButtonClick,handleDenyButtonClick,handleDeactivateButtonClick,handleReactivateButtonClick'
                    wire:loading.attr='disabled' wire:loading.class='cursor-progress'
                    @click="groupConfirmationModal.visible=false;" color="primary" class="w-1/2">Go
                    Back</x-button.outline-button>
                <template x-if="groupConfirmationModal.actionType === 'activate'">
                    <x-button.filled-button
                        wire:target='multipleActivate, multipleDeactivate'
                        wire:loading.attr='disabled' wire:loading.class='cursor-progress'
                        wire:click='multipleActivate' color="primary"
                        class="w-1/2">activate</x-button.filled-button>
                </template>
                <template x-if="groupConfirmationModal.actionType === 'deactivate'">
                    <x-button.filled-button
                        wire:target='multipleActivate, multipleDeactivate'
                        wire:loading.attr='disabled' wire:loading.class='cursor-progress'
                        wire:click='multipleDeactivate' color="primary"
                        class="w-1/2">deactivate</x-button.filled-button>
                </template>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>

    </x-modal>

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

    <x-loader.black-screen wire:loading wire:target.except="searchTerm" class="z-10"/>
</x-main.content>
