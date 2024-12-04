<x-main.content x-data="{
    confirmationModal: {
        visible: $wire.entangle('visible'),
        actionType: ''
    }
}">
    <x-main.action-header>
        <x-slot:title>Product Details</x-slot:title>
        <x-slot:actions>
            @if ($can_edit)
                <a  href="{{ route('merchant.seller-center.assets.edit', ['merchant' => $merchant, 'product' => $product]) }}">
                    <x-button.filled-button class="w-36">edit</x-button.filled-button>
                </a>
            @endif
            @if ($can_delete)
                <x-button.outline-button class="w-36" @click="confirmationModal.visible=true;confirmationModal.actionType='delete';">delete</x-button.outline-button>
            @endif
        </x-slot:actions>
    </x-main.action-header>

    <x-layout.details.more-details>
        <x-layout.details.more-details.section title="Basic Details">
            <div class="space-y-2">
                <div class="flex gap-2 break-words w-full">
                    <p class="text-base w-1/3">Status</p>
                    <div class="text-base font-bold w-2/3">
                        @switch($product->approval_status)
                            @case('review')
                                <x-status color="yellow" class="w-36">Pending Approval</x-status>
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
                <x-layout.details.more-details.data-field field="Enlistment Date" value="{{ \Carbon\Carbon::parse($product->created_at)->format('Y-m-d') }}" />
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Product Description">
            <p class="break-words">{{ $product->description }}</p>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Location and Stocks">
            <x-layout.details.more-details.data-field field="Allowed on-demand?" value="{{ $product->on_demand ? 'Yes' : 'No' }}" />
            @foreach ($product->warehouses as $warehouse)
                <x-layout.details.more-details.data-field field="{{ $warehouse->name }}" value="{{ $warehouse->pivot->stocks }} Stocks" />
            @endforeach
        </x-layout.details.more-details.section>

        <x-layout.details.more-details.section title="Pricing and Specifications">
            <x-layout.details.more-details.data-field field="Price" value="{{ \Number::currency($product->price, 'PHP') }}" />
            <x-layout.details.more-details.data-field field="Condition" value="{{ $product->condition->name }}" />
            <x-layout.details.more-details.data-field field="Package Weight" value="{{ $product->productDetail->weight }}kg" />
            <x-layout.details.more-details.data-field field="Package Length" value="{{ $product->productDetail->length }}cm" />
            <x-layout.details.more-details.data-field field="Package Width" value="{{ $product->productDetail->width }}cm" />
            <x-layout.details.more-details.data-field field="Package Height" value="{{ $product->productDetail->height }}cm" />
        </x-layout.details.more-details.section>

        @vite(['resources/js/swiper-products-services-details-pictures.js'])
        <x-layout.details.more-details.section title="Pictures" class="relative">
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
                    @foreach ($product->media as $media)
                        <div class="swiper-slide {{-- w-60 h-80 --}}" wire:key='media-{{ $media->id }}'>
                            <img src="{{ $this->get_media_url($media) }}" alt="{{ $media->name }}" class="w-full h-full object-cover"/>
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
                <x-button.outline-button wire:target='delete' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' @click="confirmationModal.visible=false;"
                    class="w-1/2">Go Back</x-button.outline-button>
                <x-button.filled-button wire:target='delete'
                    wire:loading.attr='disabled' wire:loading.class='cursor-progress' wire:click='delete'
                    class="w-1/2" x-text="confirmationModal.actionType"></x-button.filled-button>
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