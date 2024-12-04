<x-main.content x-data="{
    open: false,
    confirmationModal: {
        visible: $wire.entangle('visible'),
        actionType: $wire.entangle('actionType'),
        name: '',
    }
}">
    <x-main.action-header>
        <x-slot:title>
            @if (in_array($booking->status->slug, ['inquiry', 'quoted']))
                Inquiry Details
            @else
                Booking Details
            @endif
        </x-slot:title>
        <x-slot:actions>
            @switch($booking->status->slug)
                @case('inquiry')
                    <x-button.filled-button
                        href="{{ route('merchant.seller-center.services.show.bookings.quotation.create', ['merchant' => $booking->service->merchant->account_number, 'service' => $booking->service, 'type' => $bookingType, 'booking' => $booking]) }}">send
                        quotation</x-button.filled-button>
                    <x-button.outline-button
                        @click="confirmationModal.name='{{ $booking->entity->name }}';confirmationModal.actionType='decline';confirmationModal.visible=true">decline</x-button.outline-button>
                @break
                @case('quoted')
                    <x-button.filled-button @click="open=true">view quotation</x-button.filled-button>
                    <x-button.outline-button
                        @click="confirmationModal.name='{{ $booking->entity->name }}';confirmationModal.actionType='decline';confirmationModal.visible=true">decline</x-button.outline-button>
                @break
                @case('booked')
                    <x-button.filled-button
                        @click="confirmationModal.name='{{ $booking->entity->name }}';confirmationModal.actionType='accept';confirmationModal.visible=true">accept
                        booking</x-button.filled-button>
                    <x-button.outline-button
                        @click="confirmationModal.name='{{ $booking->entity->name }}';confirmationModal.actionType='decline';confirmationModal.visible=true">decline</x-button.outline-button>
                @break
                @case('in_progress')
                    <x-button.filled-button
                        @click="confirmationModal.name='{{ $booking->entity->name }}';confirmationModal.actionType='fulfill';confirmationModal.visible=true">fulfill</x-button.filled-button>
                    @if ($booking->invoice_id != null)
                        <x-button.outline-button @click="open=true">view quotation</x-button.outline-button>
                    @endif
                    <x-button.outline-button
                        @click="confirmationModal.name='{{ $booking->entity->name }}';confirmationModal.actionType='decline';confirmationModal.visible=true">decline</x-button.outline-button>
                @break
            @endswitch
        </x-slot:actions>
    </x-main.action-header>

    <div class="flex">
        {{-- Profile Overview --}}
        <x-layout.details.profile-overview class="h-full w-[25%] max-w-[25%]">
            <div class="h-auto mb-2 w-36">
                @if ($entity_profile_picture = $booking->entity->media->first())
                    <img src="{{ $this->get_media_url($entity_profile_picture, 'thumbnail') }}" alt="{{ $entity_profile_picture->name }}"
                        class="object-cover w-full h-auto rounded-full aspect-square">
                @else
                    <img class="object-cover w-full h-auto rounded-full aspect-square"
                        src="{{ url('/images/user/default-avatar.png') }}" alt="">
                @endif
            </div>
            <div class="w-full">
                <p class="text-sm">Client:</p>
                <h1 class="w-full mb-2 text-2xl font-bold 2xl:text-4xl">{{ $booking->entity->name }}</h1>
                <p class="text-sm">
                    @php
                        $formatted_phone_number = '+' . $booking->entity->phone_number;
                    @endphp
                    {{ '(' .
                        substr($formatted_phone_number, 0, 3) .
                        ') ' .
                        substr($formatted_phone_number, 3, 3) .
                        '-' .
                        substr($formatted_phone_number, 6, 3) .
                        '-' .
                        substr($formatted_phone_number, 9) }}
                </p>
                <p class="text-sm">{{ $booking->entity->email }}</p>
            </div>
        </x-layout.details.profile-overview>

        {{-- More Details --}}
        <x-layout.details.more-details class="w-[75%] max-w-[75%]">
            <x-layout.details.more-details.section title="Basic Details">
                <div class="space-y-2">
                    <div class="flex items-center w-full gap-2 break-words">
                        <p class="w-1/3 text-base">Status</p>
                        <div class="w-2/3">
                            @switch($booking->status->slug)
                                @case('inquiry')
                                @case('quoted')
                                    {{-- STATUS --}}
                                    <x-status color="blue" class="w-32">
                                        @if ($booking->invoice)
                                            Quotation sent
                                        @else
                                            Inquiry
                                        @endif
                                    </x-status>
                                @break
                                @case('booked')
                                    {{-- STATUS --}}
                                    <x-status color="yellow" class="w-28">Pending</x-status>
                                @break
                                @case('in_progress')
                                    {{-- STATUS --}}
                                    <x-status color="primary" class="w-28">In progress</x-status>
                                @break
                                @case('fulfilled')
                                    {{-- STATUS --}}
                                    <x-status color="green" class="w-28">Fulfilled</x-status>
                                @break
                                @case('cancelled')
                                @case('declined')
                                    {{-- STATUS --}}
                                    <x-status color="red" class="w-28">{{ $booking->status->name }}</x-status>
                                @break
                            @endswitch

                        </div>
                    </div>
                    @if ($booking->service_date)
                        <x-layout.details.more-details.data-field field="Date" value="{{ $booking->service_date }}" />
                    @endif
                    <x-layout.details.more-details.data-field field="Location"
                        value="{{ $booking->location->address }}" />
                </div>
            </x-layout.details.more-details.section>

            @if (in_array($booking->status->slug, ['inquiry', 'quoted']))
                <x-layout.details.more-details.section title="Message">
                    <p class="break-words">
                        {{ $booking->message }}</p>
                </x-layout.details.more-details.section>
            @endif

            @if ($booking->form_answers->isNotEmpty())
                <x-layout.details.more-details.section title="Service Details">
                    @foreach ($booking->form_answers as $answer)
                        <div class="mb-5">
                            <p class="mb-3 font-bold">{{ $answer->question }}</p>

                            @switch($answer->type)
                                @case('paragraph')
                                @case('dropdown')
                                    <p>{{ $answer->answer['selected'][0] }}</p>
                                @break

                                @case('checkbox')
                                    <div class="space-y-2">
                                        @foreach ($answer->answer['choices'] as $choice)
                                            <div class="flex items-center gap-2">
                                                @if (in_array($choice, $answer->answer['selected']))
                                                    <x-input type="checkbox" checked disabled class="pointer-events-none" />
                                                @else
                                                    <x-input type="checkbox" disabled class="pointer-events-none" />
                                                @endif
                                                <p>{{ $choice }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @break

                                @case('multiple')
                                    <div class="space-y-2">
                                        @foreach ($answer->answer['choices'] as $choice)
                                            <div class="flex items-center gap-2">
                                                @if (in_array($choice, $answer->answer['selected']))
                                                    <x-input type="radio" checked disabled class="pointer-events-none" />
                                                @else
                                                    <x-input type="radio" disabled class="pointer-events-none" />
                                                @endif
                                                <p>{{ $choice }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @break

                                @default
                            @endswitch
                        </div>
                    @endforeach
                </x-layout.details.more-details.section>
            @endif


            @if (in_array($booking->status->slug, ['booked', 'in_progress']))
                <x-layout.details.more-details.section title="Message">
                    <p class="break-words">{{ $booking->message }}</p>
                </x-layout.details.more-details.section>
            @endif
            @if ($booking->media->isNotEmpty())
                <x-layout.details.more-details.section title="Pictures">
                    <div class="grid grid-cols-6 gap-3">
                        @foreach ($booking->media as $booking_image)
                            <div class="relative pt-[100%] w-full">
                                <div class="absolute top-0 left-0 w-full h-full">
                                    <img class="object-cover w-full h-full rounded-xl"
                                        src="{{ $this->get_media_url($booking_image) }}" alt="{{ $booking_image->name }}"
                                        alt="">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-layout.details.more-details.section>
            @endif
        </x-layout.details.more-details>
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

    {{-- ----------- MODAL STARTS HERE ---------- --}}

    @if ($booking->invoice_id != null)

        <x-modal x-model="open">
            <div class="absolute flex flex-col gap-6 bg-white py-12 px-4 rounded-2xl w-[420px] max-w-[90%] max-h-[95%] overflow-y-auto"
                @click.outside="open=false">
                {{-- CLOSE BUTTON --}}
                <button class="absolute top-6 right-4" @click="open=false">
                    <x-icon.close />
                </button>
                @foreach ($booking->invoice->items as $invoice)
                    {{-- MODAL QUOTATIONS --}}
                    <div class="flex items-center justify-between gap-2">
                        <div class="max-w-[250px]">
                            {{-- NAME --}}
                            <p class="text-base text-rp-neutral-800">{{ $invoice->name }}</p>
                            {{-- PRICE --}}
                            <p class=" text-[12px] font-bold">₱ {{ number_format($invoice->price, 2) }}<span
                                    class="text-[12px] font-normal ml-1">
                                    {{ 'x' . $invoice->quantity }}</span>
                            </p>
                        </div>
                        <p class="text-rp-neutral-800 text-nowrap">
                            ₱ {{ number_format($invoice->price * $invoice->quantity, 2) }}</p>
                    </div>
                @endforeach

                <div class="flex items-center justify-between">
                    {{-- SUBTOTAL --}}
                    <p class="text-base text-rp-neutral-800">Subtotal</p>
                    <p class="font-bold text-rp-neutral-800">₱
                        {{ number_format($sub_total, 2) }}
                    </p>
                </div>
                @foreach ($booking->invoice->inclusions->sortBy('deduct') as $inclusion)
                    @switch($inclusion->deduct)
                        @case(0)
                            <div class="flex items-center justify-between gap-2">

                                <div class="max-w-[250px]">
                                    {{-- NAME --}}
                                    <p class="text-base text-rp-neutral-800">{{ $inclusion->name }}</p>
                                    {{-- PRICE --}}
                                    <p class=" text-[12px] font-bold">₱ {{ number_format($inclusion->amount, 2) }}<span
                                            class="text-[12px] font-normal ml-1">
                                        </span>
                                    </p>

                                </div>
                                <p class="text-rp-neutral-800 text-nowrap ">
                                    ₱{{ number_format($inclusion->amount, 2) }}</p>
                            </div>
                        @break

                        @case(1)
                            <div class="flex items-center justify-between gap-2">

                                <div class="max-w-[250px]">
                                    {{-- NAME --}}
                                    <p class="text-base text-rp-neutral-800">{{ $inclusion->name }}</p>
                                    {{-- PRICE --}}
                                    <p class=" text-[12px] font-bold">₱ {{ number_format($inclusion->amount, 2) }}<span
                                            class="text-[12px] font-normal ml-1">
                                        </span>
                                    </p>

                                </div>
                                <p class="text-rp-red-700 text-nowrap ">
                                    - ₱{{ number_format($inclusion->amount, 2) }}</p>
                            </div>
                        @break

                        @default
                    @endswitch
                @endforeach




                {{-- <div class="flex items-center justify-between"> --}}
                {{-- Inclusions --}}
                {{-- <p class="text-base text-rp-neutral-800">{{ $inclusion->name }}</p>
                <p class="text-sm text-rp-neutral-800">{{ $inclusion->amount }}</p>
            </div> --}}


                <div class="flex items-center justify-between">
                    {{-- TOTAL --}}
                    <p class="text-base font-bold text-rp-neutral-800">Total</p>
                    <p class="text-2xl font-bold text-rp-neutral-800">₱ {{ number_format($total, 2) }}</p>
                </div>
                @if ($booking->invoice->minimum_partial)
                    <div class="flex items-center justify-between">
                        {{-- PARTIAL AMOUNT --}}
                        <p class="text-base text-rp-neutral-800">Minimum partial payment allowed:</p>
                        <p class="text-base text-rp-neutral-800">₱
                            {{ number_format($booking->invoice->minimum_partial, 2) }}</p>
                    </div>
                @endif

            </div>
        </x-modal>
    @endif



    {{-- Confirmatin --}}
    <x-modal x-model="confirmationModal.visible">
        <x-modal.confirmation-modal title="Confirmation">
            <x-slot:message>
                You are about to <span x-text="confirmationModal.actionType"></span> this booking by <span
                    x-text="confirmationModal.name"></span>
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button wire:target='change_status' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' @click="confirmationModal.visible=false;" class="flex-1">go
                    back</x-button.outline-button>
                <x-button.filled-button wire:target='change_status' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' wire:click='change_status' class="flex-1">
                    Confirm
                </x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>
    </x-modal>
</x-main.content>
