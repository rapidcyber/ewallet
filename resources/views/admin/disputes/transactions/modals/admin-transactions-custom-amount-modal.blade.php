<x-modal x-model="true">
    <x-modal.form-modal title="Pay Custom Amount">
        <x-input.input-group>
            <x-slot:label>Amount:</x-slot:label>
            <x-input type="number" wire:model='amount' />
            @error('amount')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </x-input.input-group>
        <x-slot:action_buttons>
            <x-button.outline-button wire:click="$dispatch('closeModal')" class="w-1/2"
                color="primary">cancel</x-button.outline-button>
            <x-button.filled-button wire:click='pay_custom' wire:target='pay_custom' wire:loading.attr='disabled'
                wire:loading.class='cursor-progress' class="w-1/2"
                color="primary">confirm</x-button.filled-button>
        </x-slot:actions>
    </x-modal.form-modal>

    {{-- Loader --}}
    <x-loader.black-screen wire:loading wire:target='pay_custom'>
        <x-loader.clock />
    </x-loader.black-screen>
</x-modal>
