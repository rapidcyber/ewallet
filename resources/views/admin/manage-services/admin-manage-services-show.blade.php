<x-main.content class="!px-16 !py-10" x-data="{
    confirmationModal: {
        visible: $wire.entangle('visible'),
        actionType: $wire.entangle('actionType')
    }
}">
    <x-main.action-header>
        <x-slot:title>Service Details</x-slot:title>
        <x-slot:actions>
            @switch($service->approval_status)
                @case('review')
                    <x-button.primary-gradient-button @click="confirmationModal.visible=true;confirmationModal.actionType='approve';" class="w-36">approve</x-button.primary-gradient-button>
                    <x-button.outline-button @click="confirmationModal.visible=true;confirmationModal.actionType='reject';" color="primary" class="w-36">reject</x-button.outline-button>
                    @break
                @case('approved')
                    <x-button.outline-button @click="confirmationModal.visible=true;confirmationModal.actionType='suspend';" color="primary" class="w-36">suspend</x-button.outline-button>
                    @break
                @case('rejected')
                    <x-button.outline-button @click="confirmationModal.visible=true;confirmationModal.actionType='reactivate';" color="primary" class="w-36">reactivate</x-button.outline-button>
                    @break
                @case('suspended')
                    <x-button.outline-button @click="confirmationModal.visible=true;confirmationModal.actionType='reactivate';" color="primary" class="w-36">reactivate</x-button.outline-button>
                    @break
                @default
            @endswitch
        </x-slot:actions>
    </x-main.action-header>

    <x-layout.details.more-details>
        <x-layout.details.more-details.section title="Basic Details" title_text_color="primary">
            <div class="space-y-2">
                <div class="flex gap-2 break-words w-full">
                    <p class="text-base w-1/3">Status</p>
                    <div class="text-base font-bold w-2/3">
                        @switch($service->approval_status)
                            @case('review')
                                <x-status color="primary" class="w-36">For Review</x-status>
                                @break
                            @case('approved')
                                @if ($service->is_active)
                                    <x-status color="green" class="w-36">Active</x-status>
                                @else
                                    <x-status color="neutral" class="w-36">Unpublished</x-status>
                                @endif
                                @break
                            @case('rejected')
                                <x-status color="red" class="w-36">Rejected</x-status>
                                @break
                            @case('suspended')
                                <x-status color="yellow" class="w-36">Suspended</x-status>
                                @break
                            @default
                                
                        @endswitch
                    </div>
                </div> 
                <x-layout.details.more-details.data-field field="Service Title" value="{{ $service->name }}" />
                <x-layout.details.more-details.data-field field="Main Category" value="{{ $service->category->parent_category?->name }}" />
                <x-layout.details.more-details.data-field field="Subcategory" value="{{ $service->category?->name }}" />
                <x-layout.details.more-details.data-field field="Location" value="{{ $service->location->address }}" />
                <x-layout.details.more-details.data-field field="Enlistment Date" value="{{ \Carbon\Carbon::parse($service->created_at)->format('Y-m-d') }}" />
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Operating Days and Time Slots" title_text_color="primary">
            <div class="space-y-3">
                @foreach ($service_days as $day => $dayTimeslots)
                    <div class="space-y-1">
                        <p class="font-bold">{{ ucfirst($day) }}</p>
                        {{-- Time slots --}}
                        @foreach ($dayTimeslots as $timeslot)
                            <p>{{ \Carbon\Carbon::parse($timeslot['start_time'])->format('h:i A') }} - {{ \Carbon\Carbon::parse($timeslot['end_time'])->format('h:i A') }}</p>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Service Description" title_text_color="primary">
            <p class="break-words">{{ $service->description }}</p>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Service Form" title_text_color="primary">
            @foreach ($service->form_questions as $question)
                <div class="mb-5">
                    <p class="font-bold mb-3">{{ $question->question }}</p>

                    @switch($question->type)
                        @case('dropdown')
                            @foreach ($question->choices as $choice)
                                <p class="break-words">{{ $choice->value }}</p>
                            @endforeach
                            @break
                        @case('multiple')
                            @foreach ($question->choices as $choice)
                                <div class="flex items-center gap-2">
                                    <x-input type="radio" class="pointer-events-none"/>
                                    <p>{{ $choice->value }}</p>
                                </div>
                            @endforeach
                            @break
                        @case('checkbox')
                            @foreach ($question->choices as $choice)
                                <div class="flex items-center gap-2">
                                    <x-input type="checkbox" class="pointer-events-none" />
                                    <p>{{ $choice->value }}</p>
                                </div>
                            @endforeach
                            @break
                        @default
                            
                    @endswitch
                </div>
            @endforeach
        </x-layout.details.more-details.section>

        @if ($service->previous_works->count() > 0)
            <x-layout.details.more-details.section title="Previous Works" title_text_color="primary">
                <div class="grid grid-cols-6 gap-4">
                    @foreach ($service->previous_works as $previous_work)
                        <div class="break-words">
                            <div class="px-3">
                                <div class="relative pt-[100%] w-full">
                                    <div class="absolute top-0 left-0 w-full h-full">
                                        <img class="rounded-xl w-full h-full object-cover" src="{{ $this->get_media_url($previous_work->media->first()) }}"
                                        alt="">
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2">
                                <p class="font-bold">{{ $previous_work->title }}</p>
                                <p class="line-clamp-2 text-sm">{{ $previous_work->description }}</p>
                            </div>
                        </div>  
                    @endforeach
                </div>
            </x-layout.details.more-details.section>
        @endif
        

        <x-layout.details.more-details.section title="Merchant Details" title_text_color="primary">
            <div class="space-y-2">
                <x-layout.details.more-details.data-field field="Merchant Name" value="{{ $service->merchant->name }}" />
                <x-layout.details.more-details.data-field field="Industry" value="{{ $service->merchant->category->name }}" />
                <x-layout.details.more-details.data-field field="Contact Number" value="{{ $this->contact_number }}" />
                <x-layout.details.more-details.data-field field="Email" value="{{ $service->merchant->email }}" />
            </div>
        </x-layout.details.more-details.section>

        @vite(['resources/js/swiper-products-services-details-pictures.js'])

        <x-layout.details.more-details.section title="Pictures" title_text_color="primary" class="relative">
            <div class="absolute top-6 right-0 flex items-center gap-6">
                <div class="swiper-button-products-services-pictures-prev cursor-pointer">
                    <x-icon.thin-chevron-left width="36" height="36" />
                </div>

                <div class="swiper-button-products-services-pictures-next cursor-pointer">
                    <x-icon.thin-chevron-right width="36" height="36" />   
                </div>
            </div>
            <div class="swiper-products-services-details-pictures relative overflow-hidden">
                <div class="swiper-wrapper">
                    @foreach ($service->media as $service_image)
                        <div class="swiper-slide {{-- w-60 h-80 --}}">
                            <img src="{{ $this->get_media_url($service_image) }}" alt="{{ $service_image->name }}" class="w-full h-full object-cover"/>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-layout.details.more-details.section>


    </x-layout.details.more-details>

    <x-modal x-model="confirmationModal.visible">
        <x-modal.confirmation-modal title="Confirmation">
            <x-slot:message>
                Are you sure you want to <span x-text="confirmationModal.actionType"></span> this service?
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button wire:target='change_status' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' @click="confirmationModal.visible=false;" color="primary"
                    class="w-1/2">Go Back</x-button.outline-button>
                <x-button.filled-button wire:target='change_status'
                    wire:loading.attr='disabled' wire:loading.class='cursor-progress' wire:click='change_status'
                    color="primary" class="w-1/2" x-text="confirmationModal.actionType"></x-button.filled-button>
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