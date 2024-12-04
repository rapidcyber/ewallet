@push('style')
    <style>
        .tracker-dets:not(:first-child) span,
        .tracker-dets:not(:first-child) h5,
        .tracker-dets:not(:first-child) p {
            color: #BBC5CD;
        }

        .tracker-dets:not(:first-child) svg circle {
            fill: #BBC5CD;
        }
    </style>
@endpush

<x-main.content x-data="{action: ''}">
    {{-- HEAD --}}
    <x-main.action-header>
        <x-slot:title>Order Details</x-slot:title>
        <x-slot:actions>
            @switch($productOrder->shipping_status->name)
                @case('Pending')
                    <x-button.filled-button @click="action='pack'; $wire.set('show_modal', true)">Pack & Print</x-button.filled-button>
                    <div class="relative" x-data="{ drop: false }">
                        <x-button.outline-button @click="drop = !drop">More Action</x-button.outline-button>
                        <x-dropdown.dropdown-list x-cloak x-show="drop" class="absolute top-[100%] right-0 w-36"
                            @click.outside="drop = false">
                            <x-dropdown.dropdown-list.item wire:click="download_documents('all')">Print
                                Docs</x-dropdown.dropdown-list.item>
                            <x-dropdown.dropdown-list.item wire:click="open_cancel_order_modal">Cancel Order</x-dropdown.dropdown-list.item>
                        </x-dropdown.dropdown-list>
                    </div>
                @break

                @case('Packed')
                    <x-button.filled-button @click="action='ship'; $wire.set('show_modal', true)">Arrange Shipment</x-button.filled-button>
                    <div class="relative" x-data="{ drop: false }">
                        <x-button.outline-button @click="drop = !drop">More Action</x-button.outline-button>
                        <x-dropdown.dropdown-list x-cloak x-show="drop" class="absolute top-[100%] right-0 w-36"
                            @click.outside="drop = false">
                            <x-dropdown.dropdown-list.item>Recreate Package</x-dropdown.dropdown-list.item>
                            <x-dropdown.dropdown-list.item wire:click="download_documents('awb')">Print
                                AWB</x-dropdown.dropdown-list.item>
                            <x-dropdown.dropdown-list.item wire:click="download_documents('packing-list')">Print Packing
                                List</x-dropdown.dropdown-list.item>
                            <x-dropdown.dropdown-list.item wire:click="download_documents('pick-list')">Print Pick
                                List</x-dropdown.dropdown-list.item>
                            <x-dropdown.dropdown-list.item wire:click="download_documents('all')">Print
                                All</x-dropdown.dropdown-list.item>
                            <x-dropdown.dropdown-list.item wire:click="open_cancel_order_modal">Cancel Order</x-dropdown.dropdown-list.item>
                        </x-dropdown.dropdown-list>
                    </div>
                @break

                @case('Ready to Ship')
                    <div class="relative" x-data="{ drop: false }">
                        <x-button.outline-button @click="drop = !drop">More Action</x-button.outline-button>
                        <x-dropdown.dropdown-list x-cloak x-show="drop" class="absolute top-[100%] right-0 w-36"
                            @click.outside="drop = false">
                            <x-dropdown.dropdown-list.item @click="action='recreate'; $wire.set('show_modal', true)">Recreate Package</x-dropdown.dropdown-list.item>
                            <x-dropdown.dropdown-list.item wire:click="download_documents('awb')">Print
                                AWB</x-dropdown.dropdown-list.item>
                            <x-dropdown.dropdown-list.item wire:click="download_documents('packing-list')">Print Packing
                                List</x-dropdown.dropdown-list.item>
                            <x-dropdown.dropdown-list.item wire:click="download_documents('pick-list')">Print Pick
                                List</x-dropdown.dropdown-list.item>
                            <x-dropdown.dropdown-list.item wire:click="download_documents('all')">Print
                                All</x-dropdown.dropdown-list.item>
                            <x-dropdown.dropdown-list.item wire:click="open_cancel_order_modal">Cancel Order</x-dropdown.dropdown-list.item>
                        </x-dropdown.dropdown-list>
                    </div>
                @break

                @case('Unpaid')
                @case('Completed')
                    <x-button.filled-button>Contact Buyer</x-button.filled-button>
                @break

                @default
            @endswitch
        </x-slot:actions>
    </x-main.action-header>

    <div class="grid grid-cols-2 gap-[30px]">
        {{-- 1st Column --}}
        <div class="flex flex-col gap-[30px]">
            {{-- customer information --}}
            <div class="px-5 py-6 rounded-lg bg-white break-words">
                <h2 class="text-rp-neutral-700 font-bold text-[19px] mb-5">Customer Information</h2>
                <div class="flex justify-between items-center gap-2 text-sm 2xl:text-base">
                    <div class="w-1/3 flex items-center gap-2 px-1">
                        <div class="min-w-10 h-10">
                            @if ($productOrder->buyer->media->isNotEmpty())
                                <img src="{{ $this->get_media_url($productOrder->buyer->media->first(), 'thumbnail') }}"
                                    alt="" class="rounded-full w-full h-full object-cover">
                            @else
                                <img src="{{ url('images/user/default-avatar.png') }}" alt=""
                                    class="rounded-full w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="w-[calc(100%-40px)]">
                            <p>{{ $productOrder->buyer->name }}</p>
                        </div>
                    </div>
                    <div class="w-1/3 px-1">
                        <p>Phone Number:</p>
                        <p class="text-rp-red-500">{{ $this->format_phone_number($productOrder->buyer->phone_number, $productOrder->buyer->phone_iso) }}</p>
                    </div>
                    <div class="w-1/3 px-1">
                        <p>Email:</p>
                        <p class="text-rp-red-500">{{ $productOrder->buyer->email }}</p>
                    </div>
                </div>
            </div>
            {{-- Order Information --}}
            <div class="px-5 py-6 flex flex-col gap-5 rounded-lg bg-white break-words">
                <h2 class="text-rp-neutral-700 font-bold text-[19px]">Order Information</h2>
                <div class="flex gap-3">
                    <div class="w-16 h-16">
                        <img src="{{ $this->get_media_url($productOrder->product->first_image, 'thumbnail') }}" alt=""
                            class="rounded-md w-full h-full object-cover">
                    </div>
                    <div class="flex-1 text-rp-neutral-600 text-sm 2xl:text-base">
                        <h5 class="font-bold">{{ $productOrder->product->name }}</h5>
                        {{-- <p class="">Green, Microfiber, Small</p> --}}
                    </div>
                </div>
                <div class="w-full flex justify-between gap-1">
                    <div class="w-1/4 text-sm 2xl:text-base">
                        <p>Quantity:</p>
                        <p class="text-rp-red-500">x{{ $productOrder->quantity }}</p>
                    </div>
                    <div class="w-1/4 text-sm 2xl:text-base">
                        <p>Product Price:</p>
                        <p class="text-rp-red-500">{{ \Number::currency($productOrder->amount, 'PHP') }}</p>
                    </div>
                    <div class="w-1/4 text-sm 2xl:text-base">
                        <p>Order Number:</p>
                        <p class="text-rp-red-500">{{ $productOrder->order_number }}</p>
                    </div>
                    <div class="w-1/4 text-sm 2xl:text-base">
                        <p>Order Date:</p>
                        <p class="text-rp-red-500">
                            {{ \Carbon\Carbon::parse($productOrder->created_at)->timezone('Asia/Manila')->format('M d, Y') }}
                        </p>
                    </div>
                </div>
            </div>
            {{-- Tracking Details --}}
            <x-card.tracking-details :logs="$productOrder->logs" :delivery_partner="$productOrder->shipping_option->name" :tracking_number="$productOrder->tracking_number" :delivery_status="$delivery_status" />
            </div>

        {{-- 2nd Column --}}
        <div class="flex flex-col gap-[30px]">
            {{-- order status --}}
            <div class="p-6 rounded-lg bg-white col-start-1 break-words">
                <h2 class="text-rp-neutral-700 font-bold text-[19px] mb-8">Order Status</h2>
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between">
                        <p class="w-1/2">Order date received:</p>
                        <p class="text-rp-neutral-700 w-1/2 text-right font-bold">
                            {{ \Carbon\Carbon::parse($productOrder->created_at)->timezone('Asia/Manila')->format('F d, Y') }}
                        </p>
                    </div>
                    <div class="flex justify-between">
                        <p class="w-1/2">Status:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold capitalize">
                            {{ $productOrder->shipping_status->name }}
                        </p>
                    </div>
                    @if ($productOrder->shipping_status->name == 'Cancellation')
                        <div class="flex justify-between">
                            <p class="w-1/2">Cancel Date:</p>
                            <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold capitalize">
                                {{ \Carbon\Carbon::parse($productOrder->processed_at)->timezone('Asia/Manila')->format('F d, Y') }}
                            </p>
                        </div>
                        <div class="flex justify-between">
                            <p class="w-1/2">Reason for cancellation:</p>
                            <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold capitalize">
                                {{ $productOrder->termination_reason }}</p>
                        </div>
                    @endif
                    @if ($productOrder->shipping_status->name == 'Failed Delivery')
                        <div class="flex justify-between">
                            <p class="w-1/2">Reason for failed delivery:</p>
                            <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold capitalize">
                                {{ $productOrder->termination_reason }}</p>
                        </div>
                    @endif
                    @if (in_array($productOrder->shipping_status->name, ['Unpaid', 'Pending', 'Packed', 'Ready to Ship']))
                        <div class="flex justify-between">
                            <p class="w-1/2">Countdown:</p>
                            @if ($countdown != 'Expired')
                                <div
                                    class="border border-rp-red-600 text-rp-red-600 bg-rp-red-200 rounded-[5px] w-[151px] text-center">
                                    {{ $countdown }}</div>
                            @else
                                <div
                                    class="border border-rp-neutral-600 text-rp-neutral-600 bg-rp-neutral-200 rounded-[5px] w-[151px] text-center">
                                    Expired</div>
                            @endif
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <p>Documents:</p>
                        <div class="flex justify-end gap-x-3 flex-wrap max-w-[180px] text-[16px]">
                            <div
                                class="flex items-center {{ $productOrder->documents?->awb_downloaded ? 'opacity-50' : '' }}">
                                <x-icon.document />
                                <span class="ml-1">AWB</span>
                            </div>
                            <div
                                class="flex items-center {{ $productOrder->documents?->pick_list_downloaded ? 'opacity-50' : '' }}">
                                <x-icon.document />
                                <span class="ml-1">Pick List</span>
                            </div>
                            <div
                                class="flex items-center {{ $productOrder->documents?->packing_list_downloaded ? 'opacity-50' : '' }}">
                                <x-icon.document />
                                <span class="ml-1">Packing List</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- cancel details --}}
            @if ($productOrder->shipping_status->name == 'Cancellation' and $productOrder->cancellation)
                <div class="p-7 rounded-lg bg-white col-start-1">
                    <h2 class="text-rp-neutral-700 font-bold text-[19px] mb-5">Cancel Details</h2>
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between">
                            <p>Cancelled by</p>
                            <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                                {{ ucwords(str_replace('_', ' ',$productOrder->cancellation->cancelled_by)) }}</p>
                        </div>
                        <div class="flex justify-between">
                            <p>Reason for cancellation:</p>
                            <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                                {{ $productOrder->cancellation->reason->name }}</p>
                        </div>
                        <div class="flex justify-between items-start">
                            <p>Comments</p>
                            <p class="w-2/3 text-rp-neutral-700 font-bold text-right break-words">{{ $productOrder->cancellation->comment }}</p>
                        </div>
                        <div>
                            <p>Attached Images ({{ $productOrder->cancellation->media->count() }})</p>
                            <div class="flex gap-2">
                                @foreach ($productOrder->cancellation->media as $key => $image)
                                    <img src="{{ $this->get_media_url($image) }}" alt="image-{{ $key }}"
                                        class="w-[123px] h-[102px] rounded-md object-cover">
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- delivery details --}}
            <div class="p-7 rounded-lg bg-white col-start-1">
                <h2 class="text-rp-neutral-700 font-bold text-[19px] mb-5">Delivery Details</h2>
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between">
                        <p>Delivery Mode:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                            {{ ucwords(str_replace('_', ' ',$productOrder->delivery_type)) }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p>Warehouse:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                            {{ $productOrder->warehouse?->name }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p>Address:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                            {{ $productOrder->location?->address }}</p>
                    </div>
                </div>
            </div>
            {{-- payment summary --}}
            <div class="p-7 rounded-lg bg-white col-start-1">
                <h2 class="text-rp-neutral-700 font-bold text-[19px] mb-4">Payment Summary</h2>
                <div class="flex flex-col gap-2">
                    <div class="flex justify-between">
                        <p class="w-1/2">Payment Method:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                            {{ $productOrder->payment_option->name }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p class="w-1/2">Product subtotal:</p>
                        <p class="text-rp-neutral-700 max-w-[230px] text-right font-bold">
                            {{ \Number::currency($productOrder->amount * $productOrder->quantity, 'PHP') }}</p>
                    </div>
                    <div class="flex justify-between">
                        <p class="w-1/2">Total:</p>
                        <p class="text-rp-red-500 max-w-[230px] text-[19px] text-right font-bold">
                            {{ \Number::currency($productOrder->amount * $productOrder->quantity + $productOrder->shipping_fee, 'PHP') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirmation Modal --}}
    <x-modal x-model="$wire.show_modal">
        <template x-if="action === 'pack'">
            <x-modal.confirmation-modal>
                <x-slot:title>Mark as Packed?</x-slot:title>
                <x-slot:message>
                </x-slot:message>
                <x-slot:action_buttons>
                    <x-button.outline-button class="flex-1"
                        @click="$wire.set('show_modal',false)">cancel</x-button.outline-button>
                    <x-button.filled-button class="flex-1"
                        @click="$wire.pack_and_print">proceed</x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.confirmation-modal>
        </template>
        <template x-if="action === 'ship'">
            <x-modal.confirmation-modal class="text-pretty">
                <x-slot:title>Mark as Ready to Ship?</x-slot:title>
                <x-slot:message>
                </x-slot:message>
                <x-slot:action_buttons>
                    <x-button.outline-button class="flex-1"
                        @click="$wire.set('show_modal',false)">cancel</x-button.outline-button>
                    <x-button.filled-button class="flex-1"
                        @click="$wire.arrange_shipment">proceed</x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.confirmation-modal>
        </template>
        <template x-if="action === 'recreate'">
            <x-modal.confirmation-modal class="text-pretty">
                <x-slot:title>Recreate package?</x-slot:title>
                <x-slot:message>
                    The package will be marked as pending again.
                </x-slot:message>
                <x-slot:action_buttons>
                    <x-button.outline-button class="flex-1"
                        @click="$wire.set('show_modal',false)">cancel</x-button.outline-button>
                    <x-button.filled-button class="flex-1"
                        @click="$wire.recreate_package">proceed</x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.confirmation-modal>
        </template>
    </x-modal>

    @if ($show_cancel_modal)
        <livewire:merchant.seller-center.logistics.orders.modals.merchant-cancel-order-modal :merchant="$merchant" :order_id="$productOrder->id" />
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

    <x-loader.black-screen wire:loading.block class="z-10" wire:target="open_cancel_order_modal,pack_and_print,arrange_shipment,download_documents" class="z-30" />
</x-main.content>
