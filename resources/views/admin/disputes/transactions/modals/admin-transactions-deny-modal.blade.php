<x-modal x-model="true">
    <x-modal.confirmation-modal title="Deny dispute?" message="The transaction dispute will be marked as denied.">
        <x-slot:action_buttons>
            <x-button.outline-button color="primary" class="w-1/2" @click="$wire.dispatch('closeModal')">Cancel</x-button.outline-button>
            <x-button.filled-button color="primary" class="w-1/2" wire:click='deny_dispute'>Proceed</x-button.filled-button>
        </x-slot:action_buttons>
    </x-modal.confirmation-modal>
    {{-- Loader --}}
    <x-loader.black-screen wire:loading wire:target='deny_dispute'>
        <x-loader.clock />
    </x-loader.black-screen>
</x-modal>
