<x-main.content class="!px-16 !py-10">
    <livewire:components.layout.admin.user-details-header :user="$user" />

    <x-layout.details.more-details class="mt-8">
        <x-layout.details.more-details.section title="Personal Details" title_text_color="primary">
            <div class="space-y-2">
                <div class="flex gap-2 break-words w-full">
                    <p class="text-base w-1/3">Status</p>
                    <div class="text-base font-bold w-2/3">
                        @switch($user->profile->status)
                            @case('pending')
                                <x-status color="primary" class="w-36">{{ ucfirst($user->profile->status) }}</x-status>
                                @break
                            @case('verified')
                                <x-status color="green" class="w-36">{{ ucfirst($user->profile->status) }}</x-status>
                                @break
                            @case('rejected')
                                <x-status color="red" class="w-36">{{ ucfirst($user->profile->status) }}</x-status>
                                @break
                            @case('deactivated')
                                <x-status color="red" class="w-36">{{ ucfirst($user->profile->status) }}</x-status>
                                @break
                            @default
                        @endswitch
                    </div>
                </div>  
                <x-layout.details.more-details.data-field field="Full Name" value="{{ $user->profile->first_name . ' ' . $user->profile->middle_name . ' ' . $user->profile->surname }}" />
                <x-layout.details.more-details.data-field field="Birthday" value="{{ $user->profile->birth_date ? \Carbon\Carbon::parse($user->profile->birth_date)->format('Y/m/d') : '-' }}" />
                <x-layout.details.more-details.data-field field="Birthplace" value="{{ $user->profile->birth_place ?? '-' }}" />
                <x-layout.details.more-details.data-field field="Nationality" value="{{ $user->profile->nationality ?? '-' }}" />
                {{-- <x-layout.details.more-details.data-field field="Income Source" value="Salary" />
                <x-layout.details.more-details.data-field field="Employment Type" value="Private Non-Profit" />
                <x-layout.details.more-details.data-field field="Occupation" value="CSR" /> --}}
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Contact Details" title_text_color="primary">
            <div class="space-y-2">
                <x-layout.details.more-details.data-field field="Phone number" value="{{ $this->format_phone_number($user->phone_number, $user->phone_iso) }}" />
                <x-layout.details.more-details.data-field field="Telephone number" value="{{ $user->profile->landline_number ? $this->format_phone_number($user->profile->landline_number, $user->profile->landline_iso) : '-' }}" />
                <x-layout.details.more-details.data-field field="Email" value="{{ $user->email }}" />
                {{-- <x-layout.details.more-details.data-field field="Permanent address" value="Apt 1, Express St., Bicol, Philippines" />
                <x-layout.details.more-details.data-field field="Current address" value="Salary" /> --}}
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="KYC Results" title_text_color="primary">
            <div class="space-y-2">
                <x-layout.details.more-details.data-field field="Liveliness Score" value="{{ $user->kyc->liveness_score ?? '-' }}" />
                <x-layout.details.more-details.data-field field="Card Sanity Score" value="{{ $user->kyc?->card_sanity_score ?? '-' }}" />
                <x-layout.details.more-details.data-field field="Selfie Sanity Score" value="{{ $user->kyc?->selfie_sanity_score ?? '-' }}" />
                <x-layout.details.more-details.data-field field="Card Tampering Score" value="{{ $user->kyc?->card_tampering_score ?? '-' }}" />
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Pictures" title_text_color="primary">
            <div class="flex gap-3">
                @if ($this->get_user_selfie)
                    <div class="space-y-2">
                        <p>Selfie</p>
                        <div class="w-60 h-72">
                            <img src="data:image/png;base64,{{ $this->get_user_selfie }}" alt="" class="w-full h-full object-scale-down"/>
                        </div>
                    </div>
                @endif
                @if ($this->get_user_front_id)
                    <div class="space-y-2">
                        <p>Front ID</p>
                        <div class="w-60 h-72">
                            <img src="data:image/png;base64,{{ $this->get_user_front_id }}" alt="" class="w-full h-full object-scale-down"/>
                        </div>
                    </div>
                @endif
                @if ($this->get_user_back_id)
                    <div class="space-y-2">
                        <p>Back ID</p>
                        <div class="w-60 h-72">
                            <img src="data:image/png;base64,{{ $this->get_user_back_id }}" alt="" class="w-full h-full object-scale-down"/>
                        </div>
                    </div>
                @endif
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Account Info" title_text_color="primary">
            <div class="space-y-2">
                <x-layout.details.more-details.data-field field="Username" value="{{ $user->username }}" />
                {{-- <x-layout.details.more-details.data-field field="PIN" value="1234" /> --}}
                <div class="flex gap-2 break-words w-full" x-data="{
                    confirmationModal: {
                        visible: $wire.entangle('visible')
                    }
                }">
                    <p class="text-base w-1/3">Auth status</p>
                    <div class="flex items-center text-base font-bold w-2/3">
                        <p>{{ !empty($user->auth_attempt) && !empty($user->auth_attempt->restricted_until) ? 'Restricted until: ' . \Carbon\Carbon::parse($user->auth_attempt->restricted_until)->timezone('Asia/Manila')->format('F d, Y h:i A') : 'Unrestricted' }}</p>
                        @if (!empty($user->auth_attempt) && !empty($user->auth_attempt->restricted_until) && $user->auth_attempt->restricted_until > now())
                            <button class="ml-5 bg-rp-neutral-200 p-1 text-xs rounded-md"
                                @click="confirmationModal.visible=true">
                                Remove Restriction
                            </button>
                        @endif
                    </div>

                    <x-modal x-model="confirmationModal.visible">
                        <x-modal.confirmation-modal title="Remove restriction?">
                            <x-slot:message>
                                This action will allow the user to attempt to login again.
                            </x-slot:message>
                            <x-slot:action_buttons>
                                <x-button.outline-button wire:target='remove_restriction' wire:loading.attr='disabled'
                                    wire:loading.class='cursor-progress' @click="confirmationModal.visible=false;" color="primary"
                                    class="w-1/2">Go Back</x-button.outline-button>
                                <x-button.filled-button wire:target='remove_restriction'
                                    wire:loading.attr='disabled' wire:loading.class='cursor-progress' wire:click='remove_restriction'
                                    color="primary" class="w-1/2">Remove</x-button.filled-button>
                            </x-slot:action_buttons>
                        </x-modal.confirmation-modal>
                
                    </x-modal>
                </div>
                <x-layout.details.more-details.data-field field="Current Balance" value="{{ \Number::currency($user->latest_balance?->amount ?? '0.00', 'PHP') }}" />
                <x-layout.details.more-details.data-field field="Is Merchant" value="{{ $user->roles->where('name', 'Merchant')->first() ? 'True' : 'False' }}" />
            </div>
        </x-layout.details.more-details.section>
    </x-layout.details.more-details>

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
