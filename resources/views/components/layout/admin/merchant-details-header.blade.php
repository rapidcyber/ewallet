{{-- Expects Merchant Model as Props --}}
<div x-data="{
    confirmationModal: {
        visible: $wire.entangle('visible'),
        actionType: $wire.entangle('actionType')
    }
}">
    <x-main.action-header>
        <x-slot:title>Merchant Details</x-slot:title>
        <x-slot:actions>
            @switch($merchant->status)
                @case('pending')
                    <x-button.primary-gradient-button @click="confirmationModal.visible=true;confirmationModal.actionType='approve';" class="w-36">approve</x-button.primary-gradient-button>
                    <x-button.outline-button @click="confirmationModal.visible=true;confirmationModal.actionType='deny';" color="primary" class="w-36">deny</x-button.outline-button>
                    @break
                @case('verified')
                    <x-button.primary-gradient-button @click="confirmationModal.visible=true;confirmationModal.actionType='deactivate';" class="w-36">deactivate</x-button.primary-gradient-button>
                    @break
                @case('rejected')
                    <x-button.primary-gradient-button @click="confirmationModal.visible=true;confirmationModal.actionType='reactivate';" class="w-36">reactivate</x-button.primary-gradient-button>
                    @break
                @case('deactivated')
                    <x-button.primary-gradient-button @click="confirmationModal.visible=true;confirmationModal.actionType='reactivate';" class="w-36">reactivate</x-button.primary-gradient-button>
                    @break
                @default
                    
            @endswitch
            
        </x-slot:actions>
    </x-main.action-header>

    <div class="flex flex-col items-center mb-4">
        <div class="w-[140px] h-[140px] 2xl:w-[179px] 2xl:h-[179px] rounded-full mb-5">
            @if ($merchant_logo = $merchant->media()->where('collection_name', 'merchant_logo')->first())
                <img src="{{ $this->get_media_url($merchant_logo) }}" alt="{{ $merchant->name . ' Logo' }}" class="w-full h-full rounded-full object-cover" />
            @else
                <img src="{{ url('images/user/default-avatar.png') }}" alt="Default Avatar" class="w-full h-full rounded-full object-cover" />
            @endif
        </div>
        <h1 class="text-[23.04px] font-bold text-rp-neutral-700">{{ $merchant->name }}</h1>
        <p>Merchant since: {{ \Carbon\Carbon::parse($merchant->created_at)->timezone('Asia/Manila')->format('Y-m-d') }}</p>
    </div>

    <x-tab alignment="middle">
        <x-tab.tab-item href="{{ route('admin.manage-merchants.show.basic-details', ['merchant' => $merchant]) }}" color="primary" :isActive="request()->routeIs('admin.manage-merchants.show.basic-details')" class="w-56">Basic Details</x-tab.tab-item>
        <x-tab.tab-item href="{{ route('admin.manage-merchants.show.transactions.cash-inflow', ['merchant' => $merchant]) }}" color="primary" :isActive="request()->routeIs('admin.manage-merchants.show.transactions.*')" class="w-56">Transactions</x-tab.tab-item>
        <x-tab.tab-item href="{{ route('admin.manage-merchants.show.disputes.return-orders.index', ['merchant' => $merchant]) }}" color="primary" :isActive="request()->routeIs('admin.manage-merchants.show.disputes.*')" class="w-56">Disputes</x-tab.tab-item>
        <x-tab.tab-item href="{{ route('admin.manage-merchants.show.products.index', ['merchant' => $merchant]) }}" color="primary" :isActive="request()->routeIs('admin.manage-merchants.show.products.*')" class="w-56">Products</x-tab.tab-item>
        <x-tab.tab-item href="{{ route('admin.manage-merchants.show.services.index', ['merchant' => $merchant]) }}" color="primary" :isActive="request()->routeIs('admin.manage-merchants.show.services.*')" class="w-56">Services</x-tab.tab-item>
    </x-tab>

    <x-modal x-model="confirmationModal.visible">
        <x-modal.confirmation-modal title="Confirmation">
            <x-slot:message>
                Are you sure you want to <span x-text="confirmationModal.actionType"></span> this merchant?
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button wire:target='change_status' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' @click="confirmationModal.visible=false;" color="primary"
                    class="w-1/2">Go Back</x-button.outline-button>
                <x-button.filled-button wire:target='change_status'
                    wire:loading.attr='disabled' wire:loading.class='cursor-progress' wire:click='change_status'
                    color="primary" class="w-1/2" x-text="confirmationModal.actionType"></x-button.filled-button>
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
</div>