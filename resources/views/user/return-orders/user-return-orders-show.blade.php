<x-main.content x-data="{
    confirmationModal: {
        visible: $wire.entangle('confirmationModalVisible'),
    },
}">
    <x-main.action-header>
        <x-slot:title>Return Order Details</x-slot:title>
        <x-slot:actions>
            @if ($returnOrder->status->isRejected())
                <x-button.outline-button wire:click='showModal'>
                    file a dispute
                </x-button.outline-button>

                @if ($show_modal)
                    <livewire:user.return-orders.user-return-orders-disputes-modal :return_order_id="$returnOrder->id" />
                @endif
            @endif
            @if ($returnOrder->status->isCancellable())
                <x-button.filled-button @click="confirmationModal.visible=true">
                    cancel request
                </x-button.filled-button>

                <x-modal x-model="confirmationModal.visible">
                    <x-modal.confirmation-modal title="Confirmation">
                        <x-slot:message>
                            Are you sure you want to cancel the return request?
                        </x-slot:message>
                        <x-slot:action_buttons>
                            <x-button.outline-button wire:target='cancel_request' wire:loading.attr='disabled'
                                wire:loading.class='cursor-progress' @click="confirmationModal.visible=false;"
                                class="w-1/2">Go Back</x-button.outline-button>
                            <x-button.filled-button wire:target='cancel_request' wire:loading.attr='disabled'
                                wire:loading.class='cursor-progress' wire:click='cancel_request' class="w-1/2">
                                Proceed
                            </x-button.filled-button>
                        </x-slot:action_buttons>
                    </x-modal.confirmation-modal>

                </x-modal>
            @endif
        </x-slot:actions>
    </x-main.action-header>

    <div class="flex flex-row gap-5 mb-8">
        {{-- 1st col --}}
        <div class="w-3/5 flex flex-col">
            {{-- Order --}}
            <div class="flex flex-col px-4 py-3 bg-white rounded-lg w-full text-sm mb-7 flex-1">
                <div class="flex flex-row justify-between pb-2 border-b items-center ">
                    <div class="flex flex-row gap-2 items-center border-gray-600">
                        <div class="w-8 h-8">
                            @if ($logo = $returnOrder->product_order->product->merchant->logo)
                                <img src="{{ $this->get_media_url($logo, 'thumbnail') }}"
                                    class="w-full h-full object-cover rounded-full" alt="Default Profile Picture" />
                            @else
                                <img src="{{ url('images/user/default-avatar.png') }}"
                                    class="w-full h-full object-cover rounded-full" alt="Default Profile Picture" />
                            @endif
                        </div>
                        <p class="text-rp-red-500">{{ $returnOrder->product_order->product->merchant->name }}</p>
                        <div>
                            <x-icon.message />
                        </div>
                        <p>({{ $returnOrder->product_order->quantity }} item)</p>
                    </div>
                </div>
                <div class="flex flex-row py-3 justify-between break-words">
                    {{-- Order details --}}
                    <div class="flex flex-row w-6/12">
                        <div class="w-[70px] h-[70px]">
                            <img src="{{ $this->get_media_url($returnOrder->product_order->product->first_image, 'thumbnail') }}"
                                alt="{{ $returnOrder->product_order->product->name }}"
                                class="w-full h-full object-cover" />
                        </div>
                        <div class="overflow-hidden px-2">
                            <h3 class="font-bold text-rp-neutral-700 text-lg truncate overflow-hidden">
                                {{ $returnOrder->product_order->product->name }}</h3>
                            {{-- <p class="truncate">Green, Microfiber, Small</p> --}}
                        </div>
                    </div>
                    {{-- Total --}}
                    <div class="flex flex-col justify-center w-3/12 break-words px-2">
                        <p>Total: P {{ number_format($returnOrder->product_order->amount, 2) }}</p>
                        <p class="text-rp-red-500">x {{ $returnOrder->product_order->quantity }}</p>
                    </div>
                    {{-- Status --}}
                    <div class="flex flex-col justify-center w-3/12 px-2">
                        <x-status color="neutral" class="w-auto" :color="$returnOrder->status->name == 'Return Initiated'
                            ? 'neutral'
                            : ($returnOrder->status->name == 'Return In Progress'
                                ? 'yellow'
                                : ($returnOrder->status->name == 'Rejected' ||
                                $returnOrder->status->parent_status?->name == 'Rejected'
                                    ? 'red'
                                    : ($returnOrder->status->name == 'Resolved' ||
                                    $returnOrder->status->parent_status?->name == 'Resolved'
                                        ? 'green'
                                        : 'yellow')))">
                            {{ $returnOrder->status->parent_status?->name == 'Rejected' ? 'Rejected' : $returnOrder->status->name }}
                        </x-status>
                    </div>
                </div>
            </div>
            {{-- Return Details --}}
            <div class="bg-white rounded-lg p-7 break-words flex-1 mb-7">
                <h2 class="text-[19px] text-rp-neutral-700 font-bold mb-3">Return Details</h2>
                <div class="mb-4">
                    <p class="text-base text-rp-neutral-700">Return Request Number: <span
                            class="text-rd-red-500">{{ $returnOrder->id }}</span></p>
                    <p class="text-[13px] text-rp-neutral-500">Return requested on
                        <span>{{ \Carbon\Carbon::parse($returnOrder->created_at)->timezone('Asia/Manila')->format('F j, Y') }}</span>
                    </p>
                    <p class="text-[13px] text-rp-neutral-500">Paid Price + Shipping Fee: <span
                            class="text-rp-neutral-700">{{ \Number::currency($returnOrder->product_order->amount + $returnOrder->product_order->shipping_fee, 'PHP') }}</span>
                    </p>
                    {{-- <p class="text-[13px] text-rp-neutral-500">Refund Amount: <span class="text-rp-neutral-700">P10,000</span></p> --}}
                    <p class="text-[13px] text-rp-neutral-500">Return Reason: <span
                            class="text-rp-neutral-700">{{ $returnOrder->reason->name }}</span></p>
                </div>
                <div>
                    <div class="flex text-[13px] mb-2">
                        <p class="min-w-[86px] font-bold text-rp-neutral-700">Comments:</p>
                        <p>{{ $returnOrder->comment }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-[13px] mb-1 text-rp-neutral-700">Attached Images
                            (<span>{{ $returnOrder->media->count() }}</span>)</p>
                        <div class="flex gap-2">
                            @foreach ($returnOrder->media as $key => $image)
                                <img src="{{ $this->get_media_url($image) }}" alt="image-{{ $key }}"
                                    class="w-[123px] h-[102px] rounded-md object-cover">
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rejection Details --}}
            @if ($returnOrder->status->parent_status?->slug == 'rejected' and $returnOrder->rejection)
                <div class="bg-white rounded-lg p-7 break-words flex-1">
                    <h2 class="text-[19px] text-rp-neutral-700 font-bold mb-3">Rejection Details</h2>
                    <div class="flex text-[13px] mb-2">
                        <p class="min-w-[86px] font-bold text-rp-neutral-700">Rejection Reason: </p>
                        <p>{{ $returnOrder->rejection->reason->name }}</p>
                    </div>
                    <div class="flex text-[13px] mb-2">
                        <p class="min-w-[86px] font-bold text-rp-neutral-700">Comments: </p>
                        <p>{{ $returnOrder->rejection->comment }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-[13px] mb-1 text-rp-neutral-700">Attached Images
                            (<span>{{ $returnOrder->rejection->media->count() }}</span>)</p>
                        <div class="flex gap-2">
                            @foreach ($returnOrder->rejection->media as $key => $image)
                                <img src="{{ $this->get_media_url($image) }}" alt="image-{{ $key }}"
                                    class="w-[123px] h-[102px] rounded-md object-cover">
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- 2nd col --}}
        <div class="w-2/5 flex flex-col">

            {{-- Delivery Details --}}
            <div class="bg-white rounded-lg p-7 break-words mb-7">
                <h2 class="text-[19px] text-rp-neutral-700 font-bold mb-3">Deliver Details</h2>
                <p>Order Number: <span class="text-rp-red-500">{{ $returnOrder->product_order->order_number }}</span>
                </p>
                <p class="font-light text-sm text-rp-neutral-500 mb-4">Delivered
                    {{ \Carbon\Carbon::parse($returnOrder->product_order->processed_at)->timezone('Asia/Manila')->format('F j, Y') }}
                </p>

                <div class="space-y-4">
                    <div class="flex gap-3 items-center">
                        <div>
                            <x-icon.phone fill="#D3DADE" />
                        </div>
                        <p>{{ $this->format_phone_number($returnOrder->product_order->buyer->phone_number, $returnOrder->product_order->buyer->phone_iso) }}</p>
                    </div>
                    <div class="flex gap-3 items-center">
                        <div>
                            <x-icon.location fill="#D3DADE" />
                        </div>
                        <div>{{ $returnOrder->product_order->location->address }}</div>
                    </div>
                </div>
            </div>

            {{-- Payment Summary --}}
            <div class="p-7 rounded-lg bg-white break-words">
                <h2 class="text-[19px] text-rp-neutral-700 font-bold">Delivery Details</h2>
                <p class="text-[13px] text-rp-neutral-500 mb-4">Paid via
                    <span>{{ $returnOrder->product_order->payment_option->name }}</span>
                </p>
                <ul class="flex flex-col gap-1">
                    <li class="flex justify-between items-center text-rp-neutral-700">
                        <p>Product subtotal:</p>
                        <p class="font-bold">{{ \Number::currency($returnOrder->product_order->amount * $returnOrder->product_order->quantity, 'PHP') }}</p>
                    </li>
                    <li class="flex justify-between items-center text-rp-neutral-700">
                        <p>Shipping Fee:</p>
                        <p class="font-bold">
                            {{ $returnOrder->product_order->shipping_fee > 0 ? Illuminate\Support\Number::currency($returnOrder->product_order->shipping_fee, 'PHP') : 'Free' }}
                        </p>
                    </li>
                    {{-- <li class="flex justify-between items-center text-rp-neutral-700">
                        <p>Tax:</p>
                        <p class="font-bold">P<span>534.03</span></p>
                    </li> --}}
                    <li class="flex justify-between items-center text-rp-neutral-700">
                        <p>Total:</p>
                        <p class="font-bold text-[19px] text-rp-red-500">
                            <span>{{ \Number::currency(($returnOrder->product_order->amount * $returnOrder->product_order->quantity) + $returnOrder->product_order->shipping_fee, 'PHP') }}</span>
                        </p>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <x-card.tracking-details class="!bg-transparent w-[550px] mx-auto" :logs="$returnOrder->product_order->logs" :delivery_partner="$returnOrder->product_order->shipping_option->name"
        :tracking_number="$returnOrder->product_order->tracking_number" :delivery_status="$returnOrder->product_order->shipping_status->name" />

    <x-loader.black-screen wire:loading wire:target="showModal,closeModal,cancel_request" class="z-30">
        <x-loader.clock />
    </x-loader.black-screen>
</x-main.content>
