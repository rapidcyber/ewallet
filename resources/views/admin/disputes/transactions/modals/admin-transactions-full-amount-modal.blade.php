<x-modal x-model="true">
    <x-modal.confirmation-modal class="!max-w-md" title="Pay Full Tranasaction Amount?" message="This action will refund the full amount of the transaction. The sender will be refunded a total of {{ \Number::currency($transactionAmount, $dispute->transaction->currency) }}.">
        <x-slot:action_buttons>
            <x-button.outline-button color="primary" class="w-1/2" @click="$wire.dispatch('closeModal')">Cancel</x-button.outline-button>
            <x-button.filled-button color="primary" class="w-1/2" wire:click='pay_full'>Proceed</x-button.filled-button>
        </x-slot:action_buttons>
    </x-modal.confirmation-modal>
    {{-- Loader --}}
    <x-loader.black-screen wire:loading wire:target='pay_full'>
        <x-loader.clock />
    </x-loader.black-screen>
</x-modal>
