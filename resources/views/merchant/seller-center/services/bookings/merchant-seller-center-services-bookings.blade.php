<x-main.content x-data="{
    showQuotationModal: $wire.entangle('showQuotationModal'),
    confirmationModal: {
        visible: $wire.entangle('visible'),
        actionType: $wire.entangle('actionType'),
        name: '',
    },
}">
    <x-main.title class="mb-8">Service Bookings and Inquiries</x-main.title>

    <div class="grid grid-cols-6 gap-3 mb-8">
        <x-card.filter-card wire:click="$set('activeBox', 'all')" label="All" data="{{ $this->all_count }}"
            :isActive="$this->activeBox === 'all'" />
        <x-card.filter-card wire:click="$set('activeBox', 'inquiries')" label="Inquiries"
            data="{{ $this->inquiries_count }}" :isActive="$this->activeBox === 'inquiries'" />
        <x-card.filter-card wire:click="$set('activeBox', 'pending')" label="Pending" data="{{ $this->pending_count }}"
            :isActive="$this->activeBox === 'pending'" />
        <x-card.filter-card wire:click="$set('activeBox', 'in_progress')" label="In progress"
            data="{{ $this->in_progress_count }}" :isActive="$this->activeBox === 'in_progress'" />
        <x-card.filter-card wire:click="$set('activeBox', 'fulfilled')" label="Fulfilled"
            data="{{ $this->fulfilled_count }}" :isActive="$this->activeBox === 'fulfilled'" />
        <x-card.filter-card wire:click="$set('activeBox', 'cancelled')" label="Cancelled"
            data="{{ $this->cancelled_count }}" :isActive="$this->activeBox === 'cancelled'" />
    </div>

    <x-layout.search-container class="mb-8">
        <x-input.search wire:model.live.debounce.250ms='searchTerm' icon_position="left" />
        <div class="flex gap-3">
            <x-dropdown.select-date wire:model.live='dateFilter' wire:loading.attr="disabled"
                wire:loading.class='opacity-50' class="flex-1">
                <x-dropdown.select.option value="" selected>Select Date</x-dropdown.select.option>
                <x-dropdown.select.option value="today">Today</x-dropdown.select.option>
                <x-dropdown.select.option value="next_day">Next Day</x-dropdown.select.option>
                <x-dropdown.select.option value="next_week">Next Week</x-dropdown.select.option>
                <x-dropdown.select.option value="next_month">Next Month</x-dropdown.select.option>
                <x-dropdown.select.option value="next_3_months">Next 3 Months</x-dropdown.select.option>
                <x-dropdown.select.option value="next_6_months">Next 6 Months</x-dropdown.select.option>
                <x-dropdown.select.option value="next_year">Next Year</x-dropdown.select.option>
            </x-dropdown.select-date>
        </div>
    </x-layout.search-container>

    {{-- Table --}}
    <div class="overflow-auto rounded-lg">
        <x-table.rounded>
            <x-slot:table_header>
                <x-table.rounded.th>User</x-table.rounded.th>
                <x-table.rounded.th>Location</x-table.rounded.th>
                <x-table.rounded.th class="min-w-56 max-w-56">Time slot</x-table.rounded.th>
                <x-table.rounded.th>
                    <div class="flex items-center">
                        <p>Date Scheduled</p>
                        <div wire:click='sort("date_scheduled")' class="cursor-pointer">
                            <x-icon.sort />
                        </div>
                    </div>
                </x-table.rounded.th>
                <x-table.rounded.th>
                    <div class="flex items-center">
                        <p>Status</p>
                        <div wire:click='sort("status")' class="cursor-pointer">
                            <x-icon.sort />
                        </div>
                    </div>
                </x-table.rounded.th>
                <x-table.rounded.th class="min-w-64 max-w-64 2xl:min-w-80 2xl:max-w-80">Actions</x-table.rounded.th>
            </x-slot:table_header>
            <x-slot:table_data>
                <tr>
                    <td class="pt-8"></td>
                </tr>
                @foreach ($bookings as $booking)
                    @php
                        $bookingType = $booking->status->slug === 'inquiry' ? 'inquiries' : 'bookings';
                    @endphp
                    <x-table.rounded.row>
                        <x-table.rounded.td>
                            <div class="flex items-center">
                                <div class="h-12 min-w-12">
                                    @if ($booking->entity->media->count() > 0)
                                        <img src="{{ $this->get_media_url($booking->entity->media->first(), 'thumbnail') }}"
                                            class="object-cover w-full h-full rounded-full" alt="">
                                    @else
                                        <img src="{{ url('images/user/default-avatar.png') }}"
                                            class="object-cover w-full h-full rounded-full" alt="">
                                    @endif
                                </div>
                                <div class="px-2 max-w-44">
                                    <p class="truncate">{{ $booking->entity->name }}</p>
                                </div>
                            </div>
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            {{ $booking->location?->address ?? '-' }}
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            @if (empty($booking->slots) == false)
                                @foreach ($booking->slots as $key => $slot)
                                    <p>{{ \Carbon\Carbon::parse($slot['start_time'])->format('g:i A') }} -
                                        {{ \Carbon\Carbon::parse($slot['end_time'])->format('g:i A') }}</p>
                                @endforeach
                            @else
                                -
                            @endif
                        </x-table.rounded.td>
                        <x-table.rounded.td>
                            @if (empty($booking->service_date) == false)
                                {{ \Carbon\Carbon::parse($booking->service_date)->format('F j, Y') }}
                            @else
                                -
                            @endif
                        </x-table.rounded.td>
                        @switch($booking->status->name)
                            @case('Fulfilled')
                                <x-table.rounded.td>
                                    <x-status color="green" class="w-24">Fulfilled</x-status>
                                </x-table.rounded.td>
                                <x-table.rounded.td class="min-w-64 max-w-64 2xl:min-w-80 2xl:max-w-80">
                                    <div class="flex items-center w-full gap-1">
                                        <div class="flex w-5/6 gap-1 xl:flex-col 2xl:flex-row">
                                            <x-button.filled-button class="xl:w-full 2xl:w-1/2" 
                                                href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">View</x-button.filled-button>
                                        </div>
                                        <div class="w-max">
                                            <a
                                                href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">
                                                <x-icon.chevron-right class="w-full" />
                                            </a>
                                        </div>
                                    </div>
                                </x-table.rounded.td>
                            @break

                            @case('Inquiry')
                                <x-table.rounded.td>
                                    <x-status color="blue" class="w-24">
                                        Inquiry
                                    </x-status>
                                </x-table.rounded.td>
                                <x-table.rounded.td class="min-w-64 max-w-64 2xl:min-w-80 2xl:max-w-80">
                                    <div class="flex items-center w-full gap-1">
                                        <div class="flex w-5/6 gap-1 xl:flex-col 2xl:flex-row">
                                            <x-button.filled-button class="xl:w-full 2xl:w-1/2"
                                                href="{{ route('merchant.seller-center.services.show.bookings.quotation.create', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">
                                                SEND QUOTATION
                                            </x-button.filled-button>
                                            <x-button.outline-button
                                                class="xl:w-full 2xl:w-1/2"
                                                @click="$wire.set('booking_id',{{ $booking->id }});confirmationModal.name='{{ $booking->entity->name }}';confirmationModal.actionType='decline';confirmationModal.visible=true"
                                            >DECLINE</x-button.outline-button>
                                        </div>
                                        <div class="w-max">
                                            <a
                                                href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">
                                                <x-icon.chevron-right class="w-full" />
                                            </a>
                                        </div>
                                    </div>
                                </x-table.rounded.td>
                            @break

                            @case('Quoted')
                                <x-table.rounded.td>
                                    <x-status color="blue" class="w-24">
                                        Quotation Sent
                                    </x-status>
                                </x-table.rounded.td>
                                <x-table.rounded.td class="min-w-64 max-w-64 2xl:min-w-80 2xl:max-w-80">
                                    <div class="flex items-center w-full gap-1">
                                        <div class="flex w-5/6 gap-1 xl:flex-col 2xl:flex-row">
                                            <x-button.filled-button wire:click='view_quotation({{ $booking->id }})'
                                                class="xl:w-full 2xl:w-1/2">
                                                VIEW QUOTATION
                                            </x-button.filled-button>
                                            <x-button.outline-button
                                                @click="$wire.set('booking_id',{{ $booking->id }});confirmationModal.name='{{ $booking->entity->name }}';confirmationModal.actionType='decline';confirmationModal.visible=true"
                                                class="xl:w-full 2xl:w-1/2">DECLINE</x-button.outline-button>
                                        </div>
                                        <div class="w-max">
                                            <a
                                                href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">
                                                <x-icon.chevron-right class="w-full" />
                                            </a>
                                        </div>
                                    </div>
                                </x-table.rounded.td>
                            @break

                            @case('Booked')
                                <x-table.rounded.td>
                                    <x-status color="yellow" class="w-24">Pending</x-status>
                                </x-table.rounded.td>
                                <x-table.rounded.td class="min-w-64 max-w-64 2xl:min-w-80 2xl:max-w-80">
                                    <div class="flex items-center w-full gap-1">
                                        <div class="flex w-5/6 gap-1 xl:flex-col 2xl:flex-row">
                                            <x-button.filled-button
                                                @click="$wire.set('booking_id',{{ $booking->id }});confirmationModal.name='{{ $booking->entity->name }}';confirmationModal.actionType='accept';confirmationModal.visible=true"
                                                class="xl:w-full 2xl:w-1/2">accept</x-button.filled-button>
                                            <x-button.outline-button
                                                @click="$wire.set('booking_id',{{ $booking->id }});confirmationModal.name='{{ $booking->entity->name }}';confirmationModal.actionType='decline';confirmationModal.visible=true"
                                                class="xl:w-full 2xl:w-1/2">decline</x-button.outline-button>
                                        </div>
                                        <div class="w-max">
                                            <a
                                                href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">
                                                <x-icon.chevron-right class="w-full" />
                                            </a>
                                        </div>
                                    </div>
                                </x-table.rounded.td>
                            @break

                            @case('In Progress')
                                <x-table.rounded.td>
                                    <x-status color="primary" class="w-24">In progress</x-status>
                                </x-table.rounded.td>
                                <x-table.rounded.td class="min-w-64 max-w-64 2xl:min-w-80 2xl:max-w-80">
                                    <div class="flex items-center w-full gap-1">
                                        <div class="flex w-5/6 gap-1 xl:flex-col 2xl:flex-row">
                                            <x-button.filled-button class="xl:w-full 2xl:w-1/2"
                                                href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">
                                                view
                                            </x-button.filled-button>
                                            <x-button.outline-button
                                                @click="$wire.set('booking_id',{{ $booking->id }});confirmationModal.name='{{ $booking->entity->name }}';confirmationModal.actionType='cancel';confirmationModal.visible=true"
                                                class="xl:w-full 2xl:w-1/2">cancel</x-button.outline-button>
                                        </div>
                                        <div class="w-max">
                                            <a
                                                href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">
                                                <x-icon.chevron-right class="w-full" />
                                            </a>
                                        </div>
                                    </div>
                                </x-table.rounded.td>
                            @break

                            @case('Cancelled')
                                <x-table.rounded.td>
                                    <x-status color="red" class="w-24">Cancelled</x-status>
                                </x-table.rounded.td>
                                <x-table.rounded.td class="min-w-64 max-w-64 2xl:min-w-80 2xl:max-w-80">
                                    <div class="flex items-center w-full gap-1">
                                        <div class="flex w-5/6 gap-1 xl:flex-col 2xl:flex-row">
                                            <x-button.filled-button class="w-full"
                                                href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">view</x-button.filled-button>
                                        </div>
                                        <div class="w-max">
                                            <a
                                                href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">
                                                <x-icon.chevron-right class="w-full" />
                                            </a>
                                        </div>
                                    </div>
                                </x-table.rounded.td>
                            @break

                            @case('Declined')
                                <x-table.rounded.td>
                                    <x-status color="red" class="w-24">Declined</x-status>
                                </x-table.rounded.td>
                                <x-table.rounded.td class="min-w-64 max-w-64 2xl:min-w-80 2xl:max-w-80">
                                    <div class="flex items-center w-full gap-1">
                                        <div class="flex w-5/6 gap-1 xl:flex-col 2xl:flex-row">
                                            <x-button.filled-button class="w-full"
                                                href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">view</x-button.filled-button>
                                        </div>
                                        <div class="w-max">
                                            <a
                                                href="{{ route('merchant.seller-center.services.show.bookings.details', ['merchant' => $merchant, 'service' => $service, 'type' => $bookingType, 'booking' => $booking]) }}">
                                                <x-icon.chevron-right class="w-full" />
                                            </a>
                                        </div>
                                    </div>
                                </x-table.rounded.td>
                            @break

                            @default
                                <x-table.rounded.td>
                                    {{ $booking->status->name }}
                                </x-table.rounded.td>
                        @endswitch
                    </x-table.rounded.row>
                @endforeach
            </x-slot:table_data>
        </x-table.rounded>
    </div>

    {{-- Pagination --}}
    <div class="flex items-center justify-center w-full gap-8">
        @if ($bookings->hasPages())
            <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                <button wire:click="previousPage" {{ $bookings->onFirstPage() ? 'disabled' : '' }}
                    class="{{ $bookings->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                        <button class="h-full px-4 py-2 bg-white border-r cursor-default">{{ $element }}</button>
                    @else
                        <button wire:click="gotoPage({{ $element }})"
                            class="h-full bg-white border-r px-4 py-2 {{ $element == $bookings->currentPage() ? 'cursor-default' : 'cursor-pointer' }}">{{ $element }}</button>
                    @endif
                @endforeach

                <button wire:click="nextPage" {{ !$bookings->hasMorePages() ? 'disabled' : '' }}
                    class="{{ !$bookings->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
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

    {{-- VIEW QUOTATION MODAL --}}
    <x-modal x-model="showQuotationModal">
        <div class="absolute flex flex-col gap-6 bg-white py-12 px-4 rounded-2xl w-[420px] max-w-[90%] max-h-[95%] overflow-y-auto"
            @click.outside="showQuotationModal=false">
            {{-- CLOSE BUTTON --}}
            <button class="absolute top-6 right-6" @click="showQuotationModal=false">
                <x-icon.close />
            </button>

            @if (!empty($invoice_details))
                {{-- MODAL QUOTATIONS --}}
                @foreach ($invoice_details['items'] as $key => $item)
                    <div class="flex items-center justify-between gap-2" wire:key='item-{{ $key }}'>
                        <div class="max-w-[250px]">
                            {{-- NAME --}}
                            <p class="text-base text-rp-neutral-800">{{ $item['name'] }}</p>
                            {{-- PRICE --}}
                            <p class=" text-[12px] font-bold">₱ {{ number_format($item['price'], 2) }}<span
                                    class="text-[12px] font-normal ml-1"> x{{ $item['quantity'] }}</span></p>
                        </div>
                        <p class="text-rp-neutral-800 text-nowrap">₱ {{ number_format($item['total'], 2) }}</p>
                    </div>
                @endforeach

                <div class="flex items-center justify-between">
                    {{-- SUBTOTAL --}}
                    <p class="text-base text-rp-neutral-800">Subtotal</p>
                    <p class="text-sm font-bold text-rp-neutral-800">
                        ₱{{ number_format($invoice_details['sub_total'], 2) }}</p>
                </div>

                {{-- INCLUSIONS --}}
                @foreach ($invoice_details['inclusions'] as $inclusion)
                    <div class="flex items-center justify-between">
                        <p class="text-base text-rp-neutral-800">
                            @if ($inclusion['name'] == 'vat')
                                VAT (12%)
                            @else
                                {{ $inclusion['name'] }}
                            @endif
                        </p>
                        <p class="text-sm text-rp-neutral-800">
                            @if ($inclusion['deduct'] == true)
                                - ₱{{ number_format($inclusion['amount'], 2) }}
                            @else
                                ₱{{ number_format($inclusion['amount'], 2) }}
                            @endif
                        </p>
                    </div>
                @endforeach

                <div class="flex items-center justify-between">
                    {{-- TOTAL --}}
                    <p class="text-base font-bold text-rp-neutral-800">Total</p>
                    <p class="text-2xl font-bold text-rp-neutral-800">
                        ₱{{ number_format($invoice_details['total'], 2) }}</p>
                </div>

                @if (!empty($invoice_details['partial_amount']))
                    <div class="flex items-center justify-between">
                        {{-- PARTIAL AMOUNT --}}
                        <p class="text-base text-rp-neutral-800">Minimum partial payment allowed:</p>
                        <p class="text-base text-rp-neutral-800">
                            ₱{{ number_format($invoice_details['partial_amount'], 2) }}</p>
                    </div>
                @endif
            @endif
        </div>
    </x-modal>

    {{-- CONFIRMATION MODAL --}}
    <x-modal x-model="confirmationModal.visible">
        <x-modal.confirmation-modal title="Confirmation">
            <x-slot:message>
                You are about to <span x-text="confirmationModal.actionType"></span> this booking by <span
                    x-text="confirmationModal.name"></span>
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button wire:target='change_status' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress'
                    @click="confirmationModal.visible=false;$wire.set('booking_id',null)" color="red"
                    class="w-1/2">Go
                    Back</x-button.outline-button>
                <x-button.filled-button wire:target='change_status' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' wire:click='change_status' color="red"
                    class="w-1/2">proceed</x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>
    </x-modal>
</x-main.content>
