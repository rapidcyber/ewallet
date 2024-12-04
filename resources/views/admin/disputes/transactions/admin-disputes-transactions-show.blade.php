<x-main.content class="!px-16 !py-10">
    <x-main.title class="mb-8">Dispute Details</x-main.title>

    <x-layout.details.more-details class="mb-8">
        <x-layout.details.more-details.section title="User Details" title_text_color="primary">
            <div class="space-y-2">
                <x-layout.details.more-details.data-field field="Full Name" value="{{ $dispute->transaction->sender->name }}" />
                <x-layout.details.more-details.data-field field="Number" value="{{ $this->format_phone_number($dispute->transaction->sender->phone_number, $dispute->transaction->sender->phone_iso) }}" />
                <x-layout.details.more-details.data-field field="Email" value="{{ $dispute->transaction->sender->email }}" />
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="General Dispute Details" title_text_color="primary">
            <div class="space-y-2">
                <div class="flex gap-2 break-words w-full">
                    <p class="text-base w-1/3">Status</p>
                    <div class="text-base font-bold w-2/3">
                        @switch($dispute->status)
                            @case('pending')
                                <x-status color="neutral" class="w-max">Pending</x-status>
                                @break
                            @case('partially-paid')
                                <x-status color="green" class="w-max">Resolved - Partially Paid</x-status>
                                @break
                            @case('fully-paid')
                                <x-status color="green" class="w-max">Resolved - Fully Paid</x-status>
                                @break
                            @case('denied')
                                <x-status color="red" class="w-max">Denied</x-status>
                                @break
                            @default
                                <x-status color="red" class="w-max">{{ $dispute->status }}</x-status>
                        @endswitch
                    </div>
                </div> 
                <x-layout.details.more-details.data-field field="Category" value="{{ $dispute->reason->name }}" />
                <x-layout.details.more-details.data-field field="Transaction Date" value="{{ \Carbon\Carbon::parse($dispute->transaction->created_at)->timezone('Asia/Manila')->format('m/d/Y') }}" />                
                <x-layout.details.more-details.data-field field="Transaction Amount" value="{{ \Number::currency($dispute->transaction->amount, 'PHP') }}" />
                <x-layout.details.more-details.data-field field="Transaction Reference Number" value="{{ $dispute->transaction->ref_no }}" />
                <x-layout.details.more-details.data-field field="Date Created" value="{{ \Carbon\Carbon::parse($dispute->created_at)->timezone('Asia/Manila')->format('m/d/Y') }}" />
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Message" title_text_color="primary">
            <div class="space-y-2">
               <p>{{ $dispute->comment }}</p>
            </div>
        </x-layout.details.more-details.section>

        @if ($dispute->media->count() > 0)
            <x-layout.details.more-details.section title="Attachments" title_text_color="primary">
                <div class="space-y-3">
                    @foreach ($dispute->media as $image)
                        {{-- Attachment 1 --}}
                        <div class="flex">
                            <div class="w-44 h-28">
                                <img src="{{ $this->get_media_url($image) }}" class="w-full h-full object-cover rounded-[4px]" alt="Sofa" />
                            </div>
                            <div class="px-2">
                                <strong>{{ $image->name }}</strong>
                                <p>{{ $image->human_readable_size }}</p>
                            </div>
                        </div>
                    @endforeach
                    
                </div>
            </x-layout.details.more-details.more-section>
        @endif
    </x-layout.details.more-details>

    @if ($this->action_allowed)    
        <div>
            <p>Decisions:</p>
            <div class="flex items-center gap-3">
                <x-button.primary-gradient-button class="w-[244px]" wire:click="set_action('pay_full')">pay full transaction amount</x-button.primary-gradient-button>
                <x-button.primary-gradient-button class="w-[244px]" wire:click="set_action('pay_custom')">pay custom amount</x-button.primary-gradient-button>
                <x-button.primary-gradient-button class="w-[244px]" wire:click="set_action('deny')">deny</x-button.primary-gradient-button>
            </div>
        </div>

        @switch($action)
            @case('pay_full')
                <livewire:admin.disputes.transactions.modals.admin-transactions-full-amount-modal :dispute_id="$dispute->id" />
                @break
            @case('pay_custom')
                <livewire:admin.disputes.transactions.modals.admin-transactions-custom-amount-modal :dispute_id="$dispute->id" />
                @break
            @case('deny')
                <livewire:admin.disputes.transactions.modals.admin-transactions-deny-modal :dispute_id="$dispute->id" />
                @break
        @endswitch
    @endif

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
    <x-loader.black-screen wire:loading wire:target='set_action,closeModal'>
        <x-loader.clock />
    </x-loader.black-screen>
</x-main.content>