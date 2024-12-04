<x-main.content class="!px-16 !py-10" x-data="{ modalVisible: false }">
    <x-main.action-header>
        <x-slot:title>Inquiry Details</x-slot:title>
        <x-slot:actions>
            <x-button.outline-button color="primary" class="w-32"
                @click="modalVisible=true">delete</x-button.outline-button>
        </x-slot:actions>
    </x-main.action-header>

    <x-layout.details.more-details class="mb-4">
        <x-layout.details.more-details.section title="Basic Details" title_text_color="primary">
            <div class="space-y-2">
                <x-layout.details.more-details.data-field field="Full Name" value="{{ $inquiry->full_name }}" />
                <x-layout.details.more-details.data-field field="Email" value="{{ $inquiry->email }}" />
                <x-layout.details.more-details.data-field field="Subject" value="{{ $inquiry->subject }}" />
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Content" title_text_color="primary">
            <p>{{ $inquiry->message }}</p>
        </x-layout.details.more-details.section>
    </x-layout.details.more-details>

    <x-input.input-group x-data="{ counter: 0 }" x-init="counter = $refs.message.value.length" class="mb-4">
        <x-slot:label>Message</x-slot:label>
        <x-input.textarea wire:model='message' x-ref='message' x-on:keyup="counter = $refs.message.value.length"
            maxlength="2000" rows="10"></x-input.textarea>
        <p class="text-right text-[11px]"><span x-html="counter"></span>/<span x-html="$refs.message.maxLength"></span>
        </p>
    </x-input.input-group>

    <div class="flex justify-end">
        <x-button.filled-button wire:click='onSend' wire:target='onSend' wire:loading.attr='disabled'
            wire:loading.class='opacity-50' color="primary" class="w-36">send</x-button.filled-button>
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

    {{-- Confirmation Modal --}}
    <x-modal x-model="modalVisible">
        <x-modal.confirmation-modal>
            <x-slot:title>Confirmation</x-slot:title>
            <x-slot:message>
                Are you sure you want to delete this inquiry?
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button class="flex-1" @click="modalVisible=false;" wire:target='delete'
                    wire:loading.attr='disabled' wire:loading.class='opacity-50'
                    color="primary">cancel</x-button.outline-button>
                <x-button.filled-button class="flex-1" color="primary" wire:click='delete' wire:target='delete'
                    wire:loading.attr='disabled' wire:loading.class='opacity-50'>
                    yes</x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>
    </x-modal>

    <x-loader.black-screen wire:loading.block wire:target="onSend" />
</x-main.content>
