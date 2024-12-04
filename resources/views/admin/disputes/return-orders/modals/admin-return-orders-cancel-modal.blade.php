<x-modal x-model="true">
    <x-modal.form-modal title="Cancel Order">
        <div>
            <x-input.input-group class="mb-6">
                <x-slot:label>Cancellation Reason*</x-slot:label>
                <x-dropdown.select wire:model='reason'>
                    <x-dropdown.select.option value="" selected hidden>Please Select</x-dropdown.select.option>
                    @foreach ($this->get_cancel_reasons as $key => $reason_option)
                        <x-dropdown.select.option wire:key="reason-{{ $key }}"
                            value="{{ $reason_option->slug }}">{{ $reason_option->name }}</x-dropdown.select.option>
                    @endforeach
                </x-dropdown.select>
                @error('reason')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>

            <x-input.input-group class="mb-6">
                <x-slot:label>Comment*</x-slot:label>
                <x-input.textarea wire:model='comment' />
                @error('comment')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>


            <x-input.input-group class="mb-6">
                <x-slot:label>Upload Images</x-slot:label>
                <livewire:components.input.interactive-upload-images :uploaded_images="$uploaded_images" :max="5"
                    function="updateImages" color="text-primary-500" />
                @error('uploaded_images')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
                @error('uploaded_images.*')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>

        </div>
        <x-slot:action_buttons>
            <x-button.outline-button wire:click="$dispatch('closeModal')" class="w-1/2"
                color="primary">cancel</x-button.outline-button>
            <x-button.filled-button wire:click='cancel' wire:target='cancel' wire:loading.attr='disabled'
                :disabled="$button_clickable == false" wire:loading.class='cursor-progress' class="w-1/2"
                color="primary">submit</x-button.filled-button>
        </x-slot:actions>
    </x-modal.form-modal>
</x-modal>
