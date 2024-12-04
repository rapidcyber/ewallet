<x-main.content class="!px-16 !py-10">
    <div>
        <x-main.action-header>
            <x-slot:title>Profile Update Request</x-slot:title>
            <x-slot:actions>
                <x-button.primary-gradient-button @click="$wire.showApproveModal=true" class="w-36">approve</x-button.primary-gradient-button>
                <x-button.outline-button @click="$wire.showDenyModal=true" color="primary" class="w-36">deny</x-button.outline-button>
            </x-slot:actions>
        </x-main.action-header>
    
        <div class="flex flex-col items-center mb-4">
            <div class="w-[140px] h-[140px] 2xl:w-[179px] 2xl:h-[179px] rounded-full mb-5">
                <img src="{{ url('images/user/default-avatar.png') }}" alt="Default Avatar" class="w-full h-full rounded-full object-cover" />
            </div>
            <h1 class="text-[23.04px] font-bold text-rp-neutral-700">{{ $request->user->name }}</h1>
            <p>User since: {{ \Carbon\Carbon::parse($request->user->created_at)->timezone('Asia/Manila')->format('Y-m-d') }}</p>
        </div>
    </div>

    <x-layout.details.more-details class="mt-8">
        <x-layout.details.more-details.section title="Requested Changes" title_text_color="primary">
            <div class="space-y-2">
                @if ($request->first_name !== $request->user->profile->first_name)
                    <x-layout.details.more-details.data-field field="First Name" value="{{ ($request->user->profile->first_name ?? '(none)') . ' → ' . ($request->first_name ?? '(none)') }}" />
                @endif
                @if ($request->middle_name !== $request->user->profile->middle_name)
                    <x-layout.details.more-details.data-field field="Middle Name" value="{{ ($request->user->profile->middle_name ?? '(none)') . ' → ' . ($request->middle_name ?? '(none)') }}" />
                @endif
                @if ($request->surname !== $request->user->profile->surname)
                    <x-layout.details.more-details.data-field field="Surname" value="{{ ($request->user->profile->surname ?? '(none)') . ' → ' . ($request->surname ?? '(none)') }}" />
                @endif
                @if ($request->suffix !== $request->user->profile->suffix)
                    <x-layout.details.more-details.data-field field="Suffix" value="{{ ($request->user->profile->suffix ?? '(none)') . ' → ' . ($request->suffix ?? '(none)') }}" />
                @endif
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="KYC Results" title_text_color="primary">
            <div class="space-y-2">
                <x-layout.details.more-details.data-field field="Liveness Score" value="{{ $request->liveness_score ?? '-' }}" />
                <x-layout.details.more-details.data-field field="Card Sanity Score" value="{{ $request->card_sanity_score ?? '-' }}" />
                <x-layout.details.more-details.data-field field="Selfie Sanity Score" value="{{ $request->selfie_sanity_score ?? '-' }}" />
                <x-layout.details.more-details.data-field field="Card Tampering Score" value="{{ $request->card_tampering_score ?? '-' }}" />
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
    </x-layout.details.more-details>

    <x-modal x-model="$wire.showApproveModal">
        <x-modal.confirmation-modal title="Approve Update Request?">
            <x-slot:message>
                This action will approve the request and update the user's profile.
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button wire:target='approve' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' @click="$wire.showApproveModal=false;" color="primary"
                    class="w-1/2">Go Back</x-button.outline-button>
                <x-button.filled-button wire:target='approve'
                    wire:loading.attr='disabled' wire:loading.class='cursor-progress' wire:click='approve'
                    color="primary" class="w-1/2" :disabled="$button_disabled">proceed</x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>
    </x-modal>

    <x-modal x-model="$wire.showDenyModal">
        <x-modal.confirmation-modal title="Deny Update Request?">
            <x-slot:message>
                This action will reject the request and the user's profile will not be updated.
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button wire:target='deny' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' @click="$wire.showDenyModal=false;" color="primary"
                    class="w-1/2">Go Back</x-button.outline-button>
                <x-button.filled-button wire:target='deny'
                    wire:loading.attr='disabled' wire:loading.class='cursor-progress' wire:click='deny'
                    color="primary" class="w-1/2" :disabled="$button_disabled">proceed</x-button.filled-button>
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
</x-main.content>
