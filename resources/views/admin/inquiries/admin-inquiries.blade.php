<x-main.content class="!px-16 !py-10" x-data="{
    confirmationModal: {
        visible: $wire.entangle('confirmationModalVisible'),
        actionType: $wire.entangle('actionType'),
    },

    groupConfirmationModal: {
        visible: $wire.entangle('groupConfirmationModalVisible'),
        actionType: $wire.entangle('groupActionType'),
    }
}">

    <x-main.title class="mb-8">Inquiries</x-main.title>

    {{-- Filters --}}
    <div class="grid grid-cols-3 gap-3 mb-8">
        <button wire:click='$set("activeBox", "UNANSWERED")'
            class="{{ $activeBox === 'UNANSWERED' ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2">
                <x-icon.hourglass width="24" height="24"
                    fill="{{ $activeBox === 'UNANSWERED' ? '#ffff' : '#7f56d9' }}" />
                <p class="w-28 {{ $activeBox === 'UNANSWERED' ? 'text-white' : 'text-rp-neutral-600' }}">Unanswered</p>
            </div>
            <span class="font-bold">{{ $this->count_unanswered }}</span>
        </button>

        <button wire:click='$set("activeBox", "RESPONDED")'
            class="{{ $activeBox === 'RESPONDED' ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2">
                <x-icon.check width="24" height="24"
                    fill="{{ $activeBox === 'RESPONDED' ? '#ffff' : '#7f56d9' }}" />
                <p class="w-28 {{ $activeBox === 'RESPONDED' ? 'text-white' : 'text-rp-neutral-600' }}">Responded</p>
            </div>
            <span class="font-bold">{{ $this->count_responded }}</span>
        </button>

        <button wire:click='$set("activeBox", "TRASH")'
            class="{{ $activeBox === 'TRASH' ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2">
                <x-icon.filled-trash fill="{{ $activeBox === 'TRASH' ? '#ffff' : '#7f56d9' }}" />
                <p class="w-28 {{ $activeBox === 'TRASH' ? 'text-white' : 'text-rp-neutral-600' }}">Trash</p>
            </div>
            <span class="font-bold">{{ $this->count_trash }}</span>
        </button>

    </div>

    <x-layout.search-container class="mb-8">
        <x-input.search wire:model.live.debounce.300ms='searchTerm' />
    </x-layout.search-container>

    {{-- Table --}}
    <div class="overflow-auto p-3 bg-white rounded-2xl">
        <x-table.standard class="">
            <x-slot:table_header>
                <x-table.standard.th class="w-10">
                    <x-input type="checkbox" wire:model.live="selectAll"
                        wire:change="handleSelectAllCheckbox($event.target.checked, {{ $inquiries->pluck('id') }})" />
                </x-table.standard.th>

                @if (count($checkedInquiries) > 0)
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        ({{ count($checkedInquiries) }} selected)
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
                    <x-table.standard.th class="w-40 min-w-40 max-w-40">
                        {{-- Blank --}}
                    </x-table.standard.th>
                    <x-table.standard.th class="w-52 min-w-52 max-w-52 2xl:w-72 2xl:min-w-72 2xl:max-w-72">
                        <div class="flex items-center gap-2">
                            @if ($activeBox === 'TRASH')
                                <x-button.outline-button
                                    @click="groupConfirmationModal.visible=true;groupConfirmationModal.actionType='restore'"
                                    color="primary">restore</x-button.outline-button>
                            @else
                                <x-button.outline-button
                                    @click="groupConfirmationModal.visible=true;groupConfirmationModal.actionType='delete'"
                                    color="primary">delete</x-button.outline-button>
                            @endif
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
                        Subject
                    </x-table.standard.th>
                    <x-table.standard.th class="w-52 min-w-52 max-w-52">
                        Content
                    </x-table.standard.th>
                    <x-table.standard.th class="w-40 min-w-40 max-w-40">
                        <div class="flex flex-row items-center">
                            <span>Inquiry Date</span>
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
                @if ($inquiries->count() > 0)
                    @foreach ($inquiries as $key => $inquiry)
                        <x-table.standard.row wire:key='inquiry-{{ $key }}'>
                            <x-table.standard.td>
                                <x-input type="checkbox" wire:model.live="checkedInquiries" value="{{ $inquiry->id }}"
                                    wire:change="handleSingleSelectCheckbox({{ $inquiries->count() }})" />
                            </x-table.standard.td>
                            <x-table.standard.td class="w-52 min-w-52 max-w-52">
                                {{ $inquiry->full_name }}
                            </x-table.standard.td>
                            <x-table.standard.td class="w-52 min-w-52 max-w-52">
                                {{ $inquiry->email }}
                            </x-table.standard.td>
                            <x-table.standard.td class="w-52 min-w-52 max-w-52">
                                {{ $inquiry->subject }}
                            </x-table.standard.td>
                            <x-table.standard.td class="w-52 min-w-52 max-w-52 truncate">
                                {{ $inquiry->message }}
                            </x-table.standard.td>
                            <x-table.standard.td class="w-40 min-w-40 max-w-40">
                                {{ \Carbon\Carbon::parse($inquiry->created_at)->timezone('Asia/Manila')->format('Y-m-d') }}
                            </x-table.standard.td>
                            <x-table.standard.td class="w-40 min-w-40 max-w-40">
                                @if ($inquiry->status == 1)
                                    <x-status color="green">Responded</x-status>
                                @else
                                    <x-status color="primary">Unanswered</x-status>
                                @endif
                            </x-table.standard.td>
                            <x-table.standard.td class="w-52 min-w-52 max-w-52 2xl:w-72 2xl:min-w-72 2xl:max-w-72">
                                <div class="flex items-center w-full gap-1">
                                    @if ($activeBox === 'TRASH')
                                        @if (count($checkedInquiries) === 0)
                                            <x-button.outline-button
                                                @click="$wire.set('selected_inquiry_id', {{ $inquiry->id }});confirmationModal.actionType='restore';confirmationModal.visible=true"
                                                color="primary"
                                                class="xl:w-full 2xl:w-1/2">restore</x-button.outline-button>
                                        @endif
                                    @else
                                        @if (count($checkedInquiries) === 0)
                                            <x-button.filled-button
                                                href="{{ route('admin.inquiries.show', ['inquiry' => $inquiry]) }}"
                                                color="primary" class="xl:w-full 2xl:w-1/2">reply</x-button.filled-button>
                                            <x-button.outline-button
                                                @click="$wire.set('selected_inquiry_id', {{ $inquiry->id }});confirmationModal.actionType='delete';confirmationModal.visible=true"
                                                color="primary" class="xl:w-full 2xl:w-1/2">delete</x-button.outline-button>
                                        @endif
                                        <div class="w-max">
                                            <a  href="{{ route('admin.inquiries.show', ['inquiry' => $inquiry]) }}"><x-icon.chevron-right
                                                    class="w-full" /></a>
                                        </div>
                                    @endif
                                </div>
                            </x-table.standard.td>
                        </x-table.standard.row>
                    @endforeach
                @endif
            </x-slot:table_data>
        </x-table.standard>
    </div>

    {{-- Pagination --}}
    <div class="w-full flex items-center justify-center gap-8">
        @if ($inquiries->hasPages())
            <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                <button wire:click="previousPage" {{ $inquiries->onFirstPage() ? 'disabled' : '' }}
                    class="{{ $inquiries->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                            class="h-full border-r px-4 py-2 {{ $element == $inquiries->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                    @endif
                @endforeach

                <button wire:click="nextPage" {{ !$inquiries->hasMorePages() ? 'disabled' : '' }}
                    class="{{ !$inquiries->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    {{-- Confirmation Modal --}}
    <x-modal x-model="confirmationModal.visible">
        <x-modal.confirmation-modal>
            <x-slot:title>Confirmation</x-slot:title>
            <x-slot:message>
                Are you sure you want to <span x-text="confirmationModal.actionType"></span> this inquiry?
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button class="flex-1" @click="confirmationModal.visible=false;"
                    wire:target='change_status' wire:loading.attr='disabled' wire:loading.class='opacity-50'
                    color="primary">cancel</x-button.outline-button>
                <x-button.filled-button class="flex-1" color="primary" x-text="confirmationModal.actionType"
                    wire:target='change_status' wire:loading.attr='disabled' wire:loading.class='opacity-50'
                    wire:click='change_status' />
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>
    </x-modal>

    {{-- Group Confirmation Modal --}}
    <x-modal x-model="groupConfirmationModal.visible">
        <x-modal.confirmation-modal>
            <x-slot:title>Confirmation</x-slot:title>
            <x-slot:message>
                Are you sure you want to <span x-text="groupConfirmationModal.actionType"></span> the selected
                inquiries?
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button class="flex-1" @click="groupConfirmationModal.visible=false;"
                    wire:target='group_change_status' wire:loading.attr='disabled' wire:loading.class='opacity-50'
                    color="primary">cancel</x-button.outline-button>
                <x-button.filled-button class="flex-1" color="primary" x-text="groupConfirmationModal.actionType"
                    wire:target='group_change_status' wire:loading.attr='disabled' wire:loading.class='opacity-50'
                    wire:click='group_change_status' />
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

    <x-loader.black-screen wire:loading wire:target="change_status,group_change_status" class="z-10"/>
</x-main.content>
