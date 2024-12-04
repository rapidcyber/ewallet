<x-main.content class="!px-16 !py-10">
    <livewire:components.layout.admin.merchant-details-header :merchant="$merchant" />

    <x-layout.details.more-details class="mt-8">
        <x-layout.details.more-details.section title="Personal Details" title_text_color="primary">
            <div class="space-y-2">
                <div class="flex gap-2 break-words w-full">
                    <p class="text-base w-1/3">Status</p>
                    <div class="text-base font-bold w-2/3">
                        @switch($merchant->status)
                            @case('pending')
                                <x-status color="primary" class="w-36">{{ ucfirst($merchant->status) }}</x-status>
                                @break
                            @case('verified')
                                <x-status color="green" class="w-36">{{ ucfirst($merchant->status) }}</x-status>
                                @break
                            @case('rejected')
                                <x-status color="red" class="w-36">{{ ucfirst($merchant->status) }}</x-status>
                                @break
                            @case('deactivated')
                                <x-status color="red" class="w-36">{{ ucfirst($merchant->status) }}</x-status>
                                @break
                            @default
                        @endswitch
                    </div>
                </div> 
                <x-layout.details.more-details.data-field field="Business Name" value="{{ $merchant->name }}" />
                <x-layout.details.more-details.data-field field="Industry" value="{{ $merchant->category->name }}" />
                <x-layout.details.more-details.data-field field="Business Website" value="{{ $merchant->details->website ?? '-' }}" />
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Contact Details" title_text_color="primary">
            <div class="space-y-2">
                <x-layout.details.more-details.data-field field="Phone number" value="{{ $this->format_phone_number($merchant->phone_number, $merchant->phone_iso) }}" />
                <x-layout.details.more-details.data-field field="Telephone number" value="{{ isset($merchant->details->landline_number) ? $this->format_phone_number($merchant->details->landline_number, $merchant->details->landline_iso) : '-' }}" />
                <x-layout.details.more-details.data-field field="Email" value="{{ $merchant->email }}" />
                {{-- <x-layout.details.more-details.data-field field="Business address" value="Apt 1, Express St., Bicol, Philippines" /> --}}
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Documents" title_text_color="primary">
            <x-layout.details.more-details.data-field field="DTI/SEC Number" value="{{ $merchant->details->dti_sec ?? '-' }}" />
            <div class="flex gap-3">
                @if ($dti_sec = $merchant->media->where('collection_name', 'dti_sec')->first())
                    <div class="flex-1 space-y-2">
                        <p>DTI/SEC</p>
                        <div class="h-96 w-full">
                            @if ($dti_sec->mime_type == 'application/pdf')
                                <iframe src="{{ $this->get_media_url($dti_sec) }}" class="h-full w-full"></iframe>
                            @else
                                <img src="{{ $this->get_media_url($dti_sec) }}" alt="" class="h-full w-full object-contain"/>
                            @endif
                        </div>
                    </div>
                @endif
                @if ($bir_cor = $merchant->media->where('collection_name', 'bir_cor')->first())
                    <div class="flex-1 space-y-2">
                        <p>BIR COR</p>
                        <div class="h-96 w-full">
                            @if ($bir_cor->mime_type == 'application/pdf')
                                <iframe src="{{ $this->get_media_url($bir_cor) }}" class="h-full w-full"></iframe>
                            @else
                                <img src="{{ $this->get_media_url($bir_cor) }}" alt="" class="h-full w-full object-contain"/>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </x-layout.details.more-details.section>
    </x-layout.details.more-details>

    {{-- Toast Notification --}}
    @if (session()->has('success'))
        @dd(session()->get('success'));
        <x-toasts.success />
    @endif

    @if (session()->has('error'))
        <x-toasts.error />
    @endif

    @if (session()->has('warning'))
        <x-toasts.warning />
    @endif
</x-main.content>
