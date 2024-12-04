<x-main.content class="!px-16 !py-10" x-data="{
    confirmationModal: {
        visible: $wire.entangle('visible'),
        actionType: $wire.entangle('actionType')
    }
}">
    <x-main.action-header>
        <x-slot:title>Product Details</x-slot:title>
        <x-slot:actions>
            @switch($product->approval_status)
                @case('review')
                    <x-button.primary-gradient-button @click="confirmationModal.visible=true;confirmationModal.actionType='approve';" class="w-36">approve</x-button.primary-gradient-button>
                    <x-button.outline-button @click="confirmationModal.visible=true;confirmationModal.actionType='reject';" color="primary" class="w-36">reject</x-button.outline-button>
                    @break
                @case('approved')
                    <x-button.outline-button @click="confirmationModal.visible=true;confirmationModal.actionType='suspend';" color="primary" class="w-36">suspend</x-button.outline-button>
                    @break
                @case('rejected')
                    <x-button.outline-button @click="confirmationModal.visible=true;confirmationModal.actionType='reactivate';" color="primary" class="w-36">reactivate</x-button.outline-button>
                    @break
                @case('suspended')
                    <x-button.outline-button @click="confirmationModal.visible=true;confirmationModal.actionType='reactivate';" color="primary" class="w-36">reactivate</x-button.outline-button>
                    @break
                @default
            @endswitch
        </x-slot:actions>
    </x-main.action-header>

    <x-layout.details.more-details>
        <x-layout.details.more-details.section title="Basic Details" title_text_color="primary">
            <div class="space-y-2">
                <div class="flex gap-2 break-words w-full">
                    <p class="text-base w-1/3">Status</p>
                    <div class="text-base font-bold w-2/3">
                        @switch($product->approval_status)
                            @case('review')
                                <x-status color="primary" class="w-36">Pending</x-status>
                                @break
                            @case('approved')
                                @if ($product->is_active)
                                    <x-status color="green" class="w-36">Active</x-status>
                                @else
                                    <x-status color="neutral" class="w-36">Unpublished</x-status>
                                @endif
                                @break
                            @case('rejected')
                                <x-status color="red" class="w-36">Rejected</x-status>
                                @break
                            @case('suspended')
                                <x-status color="red" class="w-36">Suspended</x-status>
                                @break
                            @default
                                
                        @endswitch
                    </div>
                </div> 
                <x-layout.details.more-details.data-field field="Product Name" value="{{ $product->name }}" />
                <x-layout.details.more-details.data-field field="Product SKU" value="{{ $product->sku }}" />
                <x-layout.details.more-details.data-field field="Main Category" value="{{ $product->category->parent_category?->name }}" />
                <x-layout.details.more-details.data-field field="Subcategory" value="{{ $product->category?->name }}" />
                <x-layout.details.more-details.data-field field="Condition" value="{{ $product->condition->name }}" />
                <x-layout.details.more-details.data-field field="Price" value="{{ $product->price }}" />
                <x-layout.details.more-details.data-field field="Stock" value="{{ $product->warehouses->sum('pivot.stocks') }}" />
                {{-- <x-layout.details.more-details.data-field field="Location" value="{{ 'Makati City' }}" /> --}}
                <x-layout.details.more-details.data-field field="Enlistment Date" value="{{ \Carbon\Carbon::parse($product->created_at)->format('Y-m-d') }}" />
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Product Description" title_text_color="primary">
            <p class="break-words">{{ $product->description }}</p>
        </x-layout.details.more-details.section>
       
        <x-layout.details.more-details.section title="Merchant Details" title_text_color="primary">
            <div class="space-y-2">
                <x-layout.details.more-details.data-field field="Merchant Name" value="{{ $product->merchant->name }}" />
                <x-layout.details.more-details.data-field field="Industry" value="{{ $product->merchant->category->name }}" />
                <x-layout.details.more-details.data-field field="Contact Number" value="{{ $this->contact_number }}" />
                <x-layout.details.more-details.data-field field="Email" value="{{ $product->merchant->email }}" />
            </div>
        </x-layout.details.more-details.section>

        @vite(['resources/js/swiper-products-services-details-pictures.js'])

        <x-layout.details.more-details.section title="Pictures" title_text_color="primary" class="relative">
            <div class="absolute top-6 right-0 flex items-center gap-6">
                <div class="swiper-button-products-services-pictures-prev cursor-pointer">
                    <x-icon.thin-chevron-left width="36" height="36" />
                </div>

                <div class="swiper-button-products-services-pictures-next cursor-pointer">
                    <x-icon.thin-chevron-right width="36" height="36" />   
                </div>
            </div>
            <div class="swiper-products-services-details-pictures relative overflow-hidden">
                <div class="swiper-wrapper">
                    @foreach ($product->media as $product_image)
                        <div class="swiper-slide">
                            <img src="{{ $this->get_media_url($product_image) }}" alt="{{ $product_image->name }}" class="w-full h-full object-cover"/>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-layout.details.more-details.section>


    </x-layout.details.more-details>

    <x-modal x-model="confirmationModal.visible">
        <x-modal.confirmation-modal title="Confirmation">
            <x-slot:message>
                Are you sure you want to <span x-text="confirmationModal.actionType"></span> this product?
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
</x-main.content>