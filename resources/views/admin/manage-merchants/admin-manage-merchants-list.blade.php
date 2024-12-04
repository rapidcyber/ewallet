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



    <x-main.title class="mb-8">Manage Merchants</x-main.title>

    {{-- Filters --}}
    <div class="grid grid-cols-4 gap-3 mb-8">
        <button wire:click='$set("filter", "all")'
            class="{{ $filter === 'all' ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2">
                <x-icon.user width="24" height="24" fill="{{ $filter === 'all' ? '#ffff' : '#7f56d9' }}" />
                <p class="{{ $filter === 'all' ? 'text-white' : 'text-rp-neutral-600' }}">All merchants</p>
            </div>
            <span class="font-bold">{{ $this->all_merchants }}</span>
        </button>

        <button wire:click='$set("filter", "pending")'
            class="{{ $filter === 'pending' ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2">
                <x-icon.hourglass width="24" height="24"
                    fill="{{ $filter === 'pending' ? '#ffff' : '#7f56d9' }}" />
                <p class="{{ $filter === 'pending' ? 'text-white' : 'text-rp-neutral-600' }}">Pending</p>
            </div>
            <span class="font-bold">{{ $this->pending_merchants }}</span>
        </button>

        <button wire:click='$set("filter", "verified")'
            class="{{ $filter === 'verified' ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2">
                <x-icon.check width="24" height="24" fill="{{ $filter === 'verified' ? '#ffff' : '#7f56d9' }}" />
                <p class="{{ $filter === 'verified' ? 'text-white' : 'text-rp-neutral-600' }}">Active</p>
            </div>
            <span class="font-bold">{{ $this->active_merchants }}</span>
        </button>

        <button wire:click='$set("filter", "rejected")'
            class="{{ $filter === 'rejected' ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2">
                <x-icon.close-filled fill="{{ $filter === 'rejected' ? '#ffff' : '#7f56d9' }}" />
                <div class="w-28 {{ $filter === 'rejected' ? 'text-white' : 'text-rp-neutral-600' }}">
                    <p class="">Denied/</p>
                    <p>Deactivated</p>
                </div>
            </div>
            <span class="font-bold">{{ $this->rejected_merchants }}</span>
        </button>

    </div>

    <x-layout.search-container class="mb-8">
        <x-input.search wire:model.live='searchTerm' />
    </x-layout.search-container>

    {{-- Table --}}
    <div class="overflow-auto p-3 bg-white rounded-2xl">
        <x-table.standard>
            <x-slot:table_header>
                <x-table.standard.th class="w-max">
                    <x-input type="checkbox" wire:model.live="selectAll" wire:change="handleSelectAllCheckbox($event.target.checked, {{$merchants->getCollection()}})" />
                </x-table.standard.th>
                @if (count($checkedMerchants) > 0)
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        ({{ count($checkedMerchants) }} selected)
                    </x-table.standard.th>
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        {{-- Blank --}}
                    </x-table.standard.th>
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        {{-- Blank --}}
                    </x-table.standard.th>
                    <x-table.standard.th class="w-32 min-w-32">
                        {{-- Blank --}}
                    </x-table.standard.th>
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        {{-- Blank --}}
                    </x-table.standard.th>
                    <x-table.standard.th class="w-32 max-w-32 min-w-32">
                        {{-- Blank --}}
                    </x-table.standard.th>
                    <x-table.standard.th class="min-w-52 max-w-52 2xl:min-w-80 2xl:max-w-80">
                        <div class="flex items-center gap-2">
                            <x-button.filled-button @click="groupConfirmationModal.actionType='activate';groupConfirmationModal.visible=true" color="primary" class="w-1/2" size="sm">activate</x-button.filled-button>
                            <x-button.outline-button @click="groupConfirmationModal.actionType='deactivate';groupConfirmationModal.visible=true" color="primary" class="w-1/2" size="sm">deactivate</x-button.outline-button>
                        </div>
                    </x-table.standard.th>
                @else
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        Name
                    </x-table.standard.th>
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        Email
                    </x-table.standard.th>
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        Contact Number
                    </x-table.standard.th>
                    <x-table.standard.th class="w-32 min-w-32 max-w-32">
                        Country
                    </x-table.standard.th>
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        <div class="flex flex-row items-center">
                            <span>Registration Date</span>
                            <button wire:click="toggleSortDirection">
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
                @foreach ($merchants as $merchant)
                    <x-table.standard.row wire:key="{{ $merchant->id }}">
                        <x-table.standard.td class="w-max">
                            <x-input type="checkbox" wire:model.live="checkedMerchants" value="{{ $merchant->id }}" wire:change="handleSingleSelectCheckbox({{$merchants->getCollection()}})" />
                        </x-table.standard.td>
                        <x-table.standard.td class="w-52 min-w-52 max-w-52">
                            <a  href="{{ route('admin.manage-merchants.show.basic-details', $merchant->id) }}" class="flex flex-row items-center gap-2 hover:underline">
                                <div class="w-10 h-10 min-w-10 min-h-10 rounded-full">
                                    @if ($photo = $merchant->media->first())
                                        <img src="{{ $this->get_media_url($photo, 'thumbnail') }}" alt=""
                                            class="w-full h-full object-cover rounded-full">
                                    @else
                                        <img src="{{ url('images/user/default-avatar.png') }}" alt=""
                                            class="w-full h-full object-cover rounded-full">
                                    @endif
                                </div>
                                <p class="break-words">{{ $merchant->name }}</p>
                            </a>
                        </x-table.standard.td>
                        <x-table.standard.td class="w-52 min-w-52 max-w-52">
                            {{ $merchant->email }}
                        </x-table.standard.td>
                        <x-table.standard.td class="w-52 min-w-52 max-w-52">
                            {{ $this->format_phone_number($merchant->phone_number, $merchant->phone_iso) }}
                        </x-table.standard.td>
                        <x-table.standard.td class="w-32 min-w-32 max-w-32">
                            {{ $merchant->phone_iso }}
                        </x-table.standard.td>
                        <x-table.standard.td class="w-52 min-w-52 max-w-52">
                            {{ \Carbon\Carbon::parse($merchant->created_at)->timezone('Asia/Manila')->format('Y-m-d') }}
                        </x-table.standard.td>
                        @switch($merchant->status)
                            @case('pending')
                                <x-table.standard.td class="w-32 min-w-32 max-w-32">
                                    <x-status color="purple">Pending</x-status>
                                </x-table.standard.td>
                                <x-table.standard.td class="min-w-52 max-w-52 2xl:min-w-80 2xl:max-w-80">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="flex items-center w-full gap-1">
                                            <div class="flex xl:flex-col w-5/6 2xl:flex-row gap-1">
                                                @if (count($checkedMerchants) === 0)
                                                <x-button.filled-button color="primary"
                                                    @click="confirmationModal.visible=true;confirmationModal.actionType='approve';$wire.set('merchant_id',{{ $merchant->id }})"
                                                    class="xl:w-full 2xl:w-1/2">approve</x-button.filled-button>
                                                <x-button.outline-button color="primary"
                                                    @click="confirmationModal.visible=true;confirmationModal.actionType='deny';$wire.set('merchant_id',{{ $merchant->id }})"
                                                    class="xl:w-full 2xl:w-1/2">deny</x-button.outline-button>
                                                @endif
                                            </div>
                                            <div class="w-max">
                                                <a  href="{{ route('admin.manage-merchants.show.basic-details', $merchant->id) }}"><x-icon.chevron-right class="w-full" /></a>
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
                                                @if (count($checkedMerchants) === 0)
                                                <x-button.filled-button color="primary"
                                                    @click="confirmationModal.visible=true;confirmationModal.actionType='deactivate';$wire.set('merchant_id',{{ $merchant->id }})"
                                                    class="xl:w-full 2xl:w-1/2">deactivate</x-button.filled-button>
                                                @endif
                                            </div>
                                            <div class="w-max">
                                                <a  href="{{ route('admin.manage-merchants.show.basic-details', $merchant->id) }}"><x-icon.chevron-right class="w-full" /></a>
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
                                                @if (count($checkedMerchants) === 0)
                                                <x-button.filled-button color="primary"
                                                    @click="confirmationModal.visible=true;confirmationModal.actionType='reactivate';$wire.set('merchant_id',{{ $merchant->id }})"
                                                    class="xl:w-full 2xl:w-1/2">reactivate</x-button.filled-button>
                                                @endif
                                            </div>
                                            <div class="w-max">
                                                <a  href="{{ route('admin.manage-merchants.show.basic-details', $merchant->id) }}"><x-icon.chevron-right class="w-full" /></a>
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
                                                @if (count($checkedMerchants) === 0)
                                                <x-button.filled-button color="primary"
                                                    @click="confirmationModal.visible=true;confirmationModal.actionType='reactivate';$wire.set('merchant_id',{{ $merchant->id }})"
                                                    class="xl:w-full 2xl:w-1/2">reactivate</x-button.filled-button>
                                                @endif    
                                            </div>
                                            <div class="w-max">
                                                <a  href="{{ route('admin.manage-merchants.show.basic-details', $merchant->id) }}"><x-icon.chevron-right class="w-full" /></a>
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
        @if ($merchants->hasPages())
            <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                <button wire:click="previousPage" {{ $merchants->onFirstPage() ? 'disabled' : '' }} @click="$wire.set('checkedMerchants', [])";
                    class="{{ $merchants->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                        <button wire:click="gotoPage({{ $element }})" @click="$wire.set('checkedMerchants', [])";
                            class="h-full  border-r px-4 py-2 {{ $element == $merchants->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                    @endif
                @endforeach

                <button wire:click="nextPage" {{ !$merchants->hasMorePages() ? 'disabled' : '' }} @click="$wire.set('checkedMerchants', [])";
                    class="{{ !$merchants->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                Are you sure you want to <span x-text="confirmationModal.actionType"></span> this merchant?
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button wire:target='approve,deny,deactivate,reactivate' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' @click="confirmationModal.visible=false;" color="primary"
                    class="w-1/2">Go Back</x-button.outline-button>
                <template x-if="confirmationModal.actionType === 'approve'">
                    <x-button.filled-button wire:target='approve,deny,deactivate,reactivate'
                        wire:loading.attr='disabled' wire:loading.class='cursor-progress' wire:click='approve'
                        color="primary" class="w-1/2">approve</x-button.filled-button>
                </template>
                <template x-if="confirmationModal.actionType === 'deny'">
                    <x-button.filled-button wire:target='approve,deny,deactivate,reactivate'
                        wire:loading.attr='disabled' wire:loading.class='cursor-progress' wire:click='deny'
                        color="primary" class="w-1/2">deny</x-button.filled-button>
                </template>
                <template x-if="confirmationModal.actionType === 'deactivate'">
                    <x-button.filled-button wire:target='approve,deny,deactivate,reactivate'
                        wire:loading.attr='disabled' wire:loading.class='cursor-progress' wire:click='deactivate'
                        color="primary" class="w-1/2">deactivate</x-button.filled-button>
                </template>
                <template x-if="confirmationModal.actionType === 'reactivate'">
                    <x-button.filled-button wire:target='approve,deny,deactivate,reactivate'
                        wire:loading.attr='disabled' wire:loading.class='cursor-progress' wire:click='reactivate'
                        color="primary" class="w-1/2">reactivate</x-button.filled-button>
                </template>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>

    </x-modal>

    <x-modal x-model="groupConfirmationModal.visible">
        <x-modal.confirmation-modal title="Confirmation">
            <x-slot:message>
                Are you sure you want to <span x-text="groupConfirmationModal.actionType"></span> these merchants?
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button
                    wire:target='multipleActivate, multipleDeactivate'
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

    <x-loader.black-screen wire:loading wire:target="approve,deny,reactivate,deactivate,multipleActivate,multipleDeactivate" class="z-10">
        <x-loader.clock />
    </x-loader.black-screen>
</x-main.content>
