<x-main.content class="!px-16 !py-10" x-data="{
    confirmationModal: {
        {{-- Must be entangled --}}
        visible: $wire.entangle('confirmationModalVisible'),
        actionType: $wire.entangle('actionType'),
    },

    groupConfirmationModal: {
        visible: $wire.entangle('groupConfirmationModalVisible'),
        actionType: $wire.entangle('groupActionType'),
    }
}">
 
    <livewire:components.layout.admin.merchant-details-header :merchant="$merchant" />

    <div class="mt-8">
        <x-layout.search-container class="mb-8">
            <x-input.search wire:model.live='searchTerm'/>
        </x-layout.search-container>
    
        <div class="bg-white overflow-auto p-3 rounded-xl">
            <x-table.standard class="break-words">
                <x-slot:table_header>
                    <x-table.standard.th class="w-10">
                        <x-input type="checkbox" wire:model.live="selectAll" wire:change="handleSelectAllCheckbox($event.target.checked, {{$services->pluck('id')}})" />
                    </x-table.standard.th>
                    @if (count($checkedServices) > 0)
                        <x-table.standard.th class="w-52 min-w-52 max-w-52">
                            ({{ count($checkedServices) }} selected)
                        </x-table.standard.th>
                        <x-table.standard.th class="w-32 min-w-32 max-w-32">
                            {{-- Blank --}}
                        </x-table.standard.th>
                        {{-- <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        </x-table.standard.th> --}}
                        <x-table.standard.th class="w-40 min-w-40 max-w-40">
                            {{-- Blank --}}
                        </x-table.standard.th>
                        <x-table.standard.th class="w-52 min-w-52 max-w-52 2xl:w-72 2xl:min-w-72 2xl:max-w-72">
                            <div class="flex items-center gap-2">
                                <x-button.filled-button size="sm" @click="groupConfirmationModal.visible=true;groupConfirmationModal.actionType='activate'" color="primary">activate</x-button.filled-button>
                                <x-button.outline-button size="sm" @click="groupConfirmationModal.visible=true;groupConfirmationModal.actionType='deactivate'" color="primary">deactivate</x-button.outline-button>
                            </div>
                        </x-table.standard.th>
                    @else
                        <x-table.standard.th class="w-52 min-w-52 max-w-52">
                            Service Name
                        </x-table.standard.th>
                        {{-- <x-table.standard.th class="w-32 min-w-32 max-w-32">
                            Country
                        </x-table.standard.th> --}}
                        <x-table.standard.th class="w-52 min-w-52 max-w-52">
                            <div class="flex flex-row items-center">
                                <span>Enlistment Date</span>
                                <button wire:click='toggleSortDirection'>
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.standard.th>
                        <x-table.standard.th class="w-40 min-w-40 max-w-40">
                            Status
                        </x-table.standard.th>
                        <x-table.standard.th class="w-52 min-w-52 max-w-52 2xl:w-72 2xl:min-w-72 2xl:max-w-72">
                            Actions
                        </x-table.standard.th>
                    @endif
                </x-slot:table_header>
                <x-slot:table_data>
                    @foreach ($services as $service)
                        <x-table.standard.row wire:key="{{ $service->id }}">
                            <x-table.standard.td>
                                <x-input type="checkbox" wire:model.live="checkedServices" value="{{ $service->id }}" wire:change="handleSingleSelectCheckbox({{$services->getCollection()}})" />
                            </x-table.standard.td>
                            <x-table.standard.td class="w-52 min-w-52 max-w-52">
                                {{ $service->name }}
                            </x-table.standard.td>
                            {{-- <x-table.standard.td class="w-32 min-w-32 max-w-32">
                                Philippines
                            </x-table.standard.td> --}}
                            <x-table.standard.td class="w-52 min-w-52 max-w-52">
                                {{ \Carbon\Carbon::parse($service->created_at)->format('Y-m-d') }}
                            </x-table.standard.td>
                            <x-table.standard.td class="w-40 min-w-40 max-w-40">
                                @switch($service->approval_status)
                                    @case('review')
                                        <x-status color="primary" class="w-32">For Review</x-status>
                                        @break
                                    @case('approved')
                                        @if ($service->is_active)
                                            <x-status color="green" class="w-32">Active</x-status>
                                        @else
                                            <x-status color="neutral" class="w-32">Unpublished</x-status>
                                        @endif
                                        @break
                                    @case('rejected')
                                        <x-status color="red" class="w-32">Rejected</x-status>
                                        @break
                                    @case('suspended')
                                        <x-status color="yellow" class="w-32">Suspended</x-status>
                                        @break
                                    @default
                                        
                                @endswitch
                                
                            </x-table.standard.td>
                            <x-table.standard.td class="w-52 min-w-52 max-w-52 2xl:w-72 2xl:min-w-72 2xl:max-w-72">
                                <div class="flex items-center w-full gap-1">
                                    <div class="flex xl:flex-col w-5/6 2xl:flex-row gap-1">
                                        @switch($service->approval_status)
                                            @case('review')
                                                <x-button.filled-button @click="$wire.set('service_id',{{ $service->id }});confirmationModal.actionType='approve';confirmationModal.visible=true" color="primary" class="xl:w-full 2xl:w-1/2">approve</x-button.filled-button>
                                                <x-button.outline-button @click="$wire.set('service_id',{{ $service->id }});confirmationModal.actionType='deny';confirmationModal.visible=true" color="primary" class="xl:w-full 2xl:w-1/2">deny</x-button.outline-button>
                                                @break
                                            @case('approved')
                                                <x-button.filled-button @click="$wire.set('service_id',{{ $service->id }});confirmationModal.actionType='deactivate';confirmationModal.visible=true" color="primary" class="xl:w-full 2xl:w-1/2">deactivate</x-button.filled-button>
                                                @break
                                            @case('rejected')
                                                <x-button.filled-button @click="$wire.set('service_id',{{ $service->id }});confirmationModal.actionType='reactivate';confirmationModal.visible=true" color="primary" class="xl:w-full 2xl:w-1/2">reactivate</x-button.filled-button>
                                                @break
                                            @case('suspended')
                                                <x-button.filled-button @click="$wire.set('service_id',{{ $service->id }});confirmationModal.actionType='reactivate';confirmationModal.visible=true" color="primary" class="xl:w-full 2xl:w-1/2">reactivate</x-button.filled-button>
                                                @break
                                            @default
                                                <x-button.filled-button @click="$wire.set('service_id',{{ $service->id }});confirmationModal.actionType='approve';confirmationModal.visible=true" color="primary" class="xl:w-full 2xl:w-1/2">approve</x-button.filled-button>
                                                <x-button.outline-button @click="$wire.set('service_id',{{ $service->id }});confirmationModal.actionType='deny';confirmationModal.visible=true" color="primary" class="xl:w-full 2xl:w-1/2">deny</x-button.outline-button>
                                        @endswitch
                                        
                                    </div>
                                    <div class="w-max">
                                        <a  href="{{ route('admin.manage-merchants.show.services.details', ['merchant' => $merchant, 'service' => $service]) }}"><x-icon.chevron-right class="w-full" /></a>
                                    </div>
                                </div>
                            </x-table.standard.td>
                        </x-table.standard.row>
                    @endforeach
                </x-slot:table_data>
            </x-table.standard>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="w-full flex items-center justify-center gap-8">
        @if ($services->hasPages())
            <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                <button wire:click="previousPage" {{ $services->onFirstPage() ? 'disabled' : '' }}
                    class="{{ $services->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                        <button wire:click="gotoPage({{ $element }})"
                            class="h-full bg-white border-r px-4 py-2 {{ $element == $services->currentPage() ? 'cursor-default' : 'cursor-pointer' }}">{{ $element }}</button>
                    @endif
                @endforeach

                <button wire:click="nextPage" {{ !$services->hasMorePages() ? 'disabled' : '' }}
                    class="{{ !$services->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                Are you sure you want to <span x-text="confirmationModal.actionType"></span> this service?
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button
                    wire:target='change_status'
                    wire:loading.attr='disabled' wire:loading.class='cursor-progress'
                    @click="confirmationModal.visible=false;$set('user_id',null)" color="primary" class="w-1/2">Go
                    Back</x-button.outline-button>
                <x-button.filled-button
                    wire:target='change_status'
                    wire:loading.attr='disabled' wire:loading.class='cursor-progress'
                    wire:click='change_status' color="primary"
                    class="w-1/2" x-text="confirmationModal.actionType"></x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>
    </x-modal>

    <x-modal x-model="groupConfirmationModal.visible">
        <x-modal.confirmation-modal title="Confirmation">
            <x-slot:message>
                Are you sure you want to <span x-text="groupConfirmationModal.actionType"></span> these services?
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

    <x-loader.black-screen wire:loading class="z-10"/>
</x-main.content>
