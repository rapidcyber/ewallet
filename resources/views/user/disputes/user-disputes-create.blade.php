<x-main.content>
    <x-main.title class="mb-8">File a Dispute</x-main.title>

    <div class="flex flex-col w-full gap-4 px-4 py-5 bg-white rounded-lg mb-11">

        {{-- Select Category --}}
        <x-input.input-group>
            <x-slot:label>
                <span class="text-[#E31C79]">*</span>Select your concern category
            </x-slot:label>
            <x-dropdown.select wire:model.blur='category'>
                <x-dropdown.select.option value="" hidden selected>Select Category</x-dropdown.select.option>
                @foreach ($this->categories as $category_option)
                    <x-dropdown.select.option
                        value="{{ $category_option->slug }}">{{ $category_option->name }}</x-dropdown.select.option>
                @endforeach
            </x-dropdown.select>
            @error('category')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </x-input.input-group>

        {{-- Email Address --}}
        <x-input.input-group>
            <x-slot:label>
                <span class="text-[#E31C79]">*</span>Your email address
            </x-slot:label>
            <x-input wire:model.blur='email' type="text" maxlength="255">
                <x-slot:icon>
                    <x-icon.mail />
                </x-slot:icon>
            </x-input>
            @error('email')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </x-input.input-group>

        <div class="flex flex-row w-full gap-2">
            {{-- Transaction Number --}}
            <x-input.input-group class="flex-1">
                <x-slot:label>
                    <span class="text-[#E31C79]">*</span>Transaction Number
                </x-slot:label>
                <x-input wire:model.blur='transaction_number' type="text" maxlength="12" />
                @error('transaction_number')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>

            {{-- Transaction Date --}}
            <x-input.input-group class="flex-1">
                <x-slot:label>
                    Transaction Date
                </x-slot:label>
                <x-input value="{{ $transaction_date }}" type="text" readonly disabled class="!bg-rp-neutral-100" />
            </x-input.input-group>

            {{-- Transaction Amount --}}
            <x-input.input-group class="flex-1">
                <x-slot:label>
                    Transaction Amount
                </x-slot:label>
                <x-input value="{{ $transaction_amount }}" type="text" readonly disabled class="!bg-rp-neutral-100" />
            </x-input.input-group>
        </div>

        {{-- Message --}}
        <x-input.input-group>
            <x-slot:label><span class="text-[#E31C79]">*</span>Message</x-slot:label>
            <x-input.textarea wire:model.blur='message' rows="5"/>
            @error('message')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </x-input.input-group>

        <x-input.input-group>
            <x-slot:label>Attachments</x-slot:label>
            <livewire:components.input.interactive-upload-images :uploaded_images="$uploaded_files" :max="5"
                :function="'updateUploadedFiles'" />
            @error('uploaded_files')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
        </x-input.input-group>
    </div>

    <div class="flex justify-center">
        <x-button.filled-button wire:click='submit' wire:target="submit" wire:loading.attr='disabled' class="w-[171px]" :disabled="$button_disabled">
            submit
        </x-button.filled-button>
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

    {{-- Loader --}}
    <x-loader.black-screen wire:loading wire:target="submit">
        <x-loader.clock />
    </x-loader.black-screen>
</x-main.content>
