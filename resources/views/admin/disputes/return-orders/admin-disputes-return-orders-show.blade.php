<x-main.content class="!px-16 !py-10">
    <x-main.title class="mb-8">Return Dispute Details</x-main.title>
    <div class="space-y-5">
        <div class="flex flex-col w-full px-4 py-3 text-sm break-words bg-white rounded-md">
            <div class="flex flex-row justify-between w-full pb-2 border-b">
                <div class="flex flex-col max-w-[50%]">
                    <p>Buyer: <span class="text-primary-600">{{ $return_order->product_order->buyer->name }}</span></p>
                    <p>Merchant: <span
                            class="text-primary-600">{{ $return_order->product_order->product->merchant->name }}</span>
                    </p>
                </div>
                <div class="max-w-[50%]">
                    <p>{{ $return_order->product_order->processed_at ? 'Delivered to buyer on ' . \Carbon\Carbon::parse($return_order->product_order->processed_at)->timezone('Asia/Manila')->format('F d, Y') : 'No date available' }}
                    </p>
                    <p>{{ 'Return requested on ' . \Carbon\Carbon::parse($return_order->created_at)->timezone('Asia/Manila')->format('F d, Y') }}
                    </p>
                </div>
            </div>
            <div class="flex flex-row justify-between py-3 break-words">
                {{-- Order details --}}
                <div class="flex flex-row w-5/12 gap-3">
                    <div class="flex-[20%]">
                        <img
                            src={{ $return_order->product_order->product->first_image ? $this->get_media_url($return_order->product_order->product->first_image, 'thumbnail') : '/images/guest/sofabed.png' }} />
                    </div>
                    <div class="flex-[80%] overflow-hidden">
                        <h2 class="overflow-hidden text-lg font-bold truncate">
                            {{ $return_order->product_order->product->name }}</h2>
                        <p class="truncate">{{ $return_order->product_order->product->description }}</p>
                        <div class="flex flex-col mt-3">
                            <div>
                                <span>Order Number:</span>
                                <span class="text-primary-600">{{ $return_order->product_order->order_number }}</span>
                            </div>
                            <div>
                                <span>Tracking Number:</span>
                                <span
                                    class="text-primary-600">{{ $return_order->product_order->tracking_number }}</span>
                            </div>
                            <div>
                                <span>Return Request Number:</span>
                                <span class="text-primary-600">{{ $return_order->id }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total --}}
                <div class="flex flex-col justify-center w-4/12 p-4">
                    <div class="flex flex-col gap-2">
                        <p class="text-rp-neutral-500">Paid Price + Shipping Fee: <span class="text-rp-neutral-700">₱
                                {{ number_format(($return_order->product_order->amount * $return_order->product_order->quantity) + $return_order->product_order->shipping_fee, 2) }}</span>
                        </p>
                        <p class="text-rp-neutral-500">
                            Refund Amount: 
                            <span class="text-rp-neutral-700">₱ 
                                @if (in_array($return_order->status->name, ['Refunded Only', 'Returned and Refunded']))
                                    {{ number_format($return_order->product_order->amount + $return_order->product_order->quantity, 2) }}
                                @else
                                    0.00
                                @endif
                            </span>
                        </p>
                        <p class="text-rp-neutral-500">Return Reason: <span
                                class="text-rp-neutral-700">{{ $return_order->reason->name }}</span></p>
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex items-center w-3/12">
                    <div class="flex flex-row items-center justify-between w-full">
                        @if ($return_order->status->parent_status?->name == 'Resolved')
                            <div class="flex flex-col items-center">
                                <x-status color="green" class="w-44">Resolved</x-status>
                                <p class="font-light">{{ $return_order->status->name }}</p>
                            </div>
                        @elseif ($return_order->status->parent_status?->name == 'Dispute In Progress')
                            @if ($return_order->status->name == 'Pending Response')
                                <div class="flex flex-col items-center">
                                    <x-status color="neutral"
                                        class="w-44">Pending Merchant Response</x-status>
                                </div>
                            @else
                                <div class="flex flex-col items-center">
                                    <x-status color="red"
                                        class="w-44">{{ $return_order->status->name }}</x-status>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if ($return_order->dispute?->decision)
            <div class="flex flex-col w-full px-4 py-3 text-sm break-words bg-white rounded-md">
                <h2 class="mb-3 text-lg font-bold text-rp-neutral-700">Dispute Decision -
                    {{ ucwords(str_replace('_', ' ', $return_order->dispute?->decision?->type)) }}</h2>
                @if ($return_order->dispute?->decision?->type == 'cancel')
                    <div class="space-y-3">
                        <div class="flex gap-1">
                            <p class="w-[85px] font-bold">Comments:</p>
                            <p class="w-[calc(100%-85px)]">
                                {{ $return_order->cancellation?->comment ?? 'N/A' }}</p>
                        </div>
                        <div>
                            @php
                                $count = $return_order->cancellation?->media->count();
                            @endphp
                            <p class="mb-1 font-bold">
                                {{ 'Attached Images (' . $count . ')' }}
                            </p>
                            @if ($count > 0)
                                <div class="grid grid-cols-9 gap-2">
                                    @foreach ($return_order->cancellation?->media as $image)
                                        <div class="relative pt-[100%] w-full">
                                            <div class="absolute top-0 left-0 w-full h-full">
                                                <img class="object-cover w-full h-full rounded-xl"
                                                    src="{{ $this->get_media_url($image) }}" alt="">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @else    
                    <div class="space-y-3">
                        <div class="flex gap-1">
                            <p class="w-[85px] font-bold">Comments:</p>
                            <p class="w-[calc(100%-85px)]">
                                {{ $return_order->dispute?->decision?->comment ?? 'N/A' }}</p>
                        </div>
                        <div>
                            @php
                                $count = $return_order->dispute?->decision?->media->count();
                            @endphp
                            <p class="mb-1 font-bold">
                                {{ 'Attached Images (' . $count . ')' }}
                            </p>
                            @if ($count > 0)
                                <div class="grid grid-cols-9 gap-2">
                                    @foreach ($return_order->dispute?->decision?->media as $image)
                                        <div class="relative pt-[100%] w-full">
                                            <div class="absolute top-0 left-0 w-full h-full">
                                                <img class="object-cover w-full h-full rounded-xl"
                                                    src="{{ $this->get_media_url($image) }}" alt="">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if ($return_order->dispute)
            <div class="flex flex-col w-full px-4 py-3 text-sm break-words bg-white rounded-md">
                <h2 class="mb-3 text-lg font-bold text-rp-neutral-700">Buyer Dispute Details</h2>
                <div class="space-y-3">
                    <div class="flex gap-1">
                        <p class="w-[85px] font-bold">Comments:</p>
                        <p class="w-[calc(100%-85px)]">
                            {{ $return_order->dispute->comment }}</p>
                    </div>
                    <div>
                        <p class="mb-1 font-bold">
                            {{ $return_order->dispute->media->count() > 0 ? 'Attached Images (' . $return_order->dispute->media->count() . ')' : 'No images attached' }}
                        </p>
                        @if ($return_order->dispute->media->count() > 0)
                            <div class="grid grid-cols-9 gap-2">
                                @foreach ($return_order->dispute->media as $image)
                                    <div class="relative pt-[100%] w-full">
                                        <div class="absolute top-0 left-0 w-full h-full">
                                            <img class="object-cover w-full h-full rounded-xl"
                                                src="{{ $this->get_media_url($image) }}" alt="">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($return_order->dispute?->response)
            <div class="flex flex-col w-full px-4 py-3 text-sm break-words bg-white rounded-md">
                <h2 class="mb-3 text-lg font-bold text-rp-neutral-700">Merchant Response</h2>
                <div class="space-y-3">
                    <div class="flex gap-1">
                        <p class="w-[85px] font-bold">Comments:</p>
                        <p class="w-[calc(100%-85px)]">
                            {{ $return_order->dispute?->response->comment }}</p>
                        </p>
                    </div>
                    <div>
                        <p class="mb-1 font-bold">
                            {{ $return_order->dispute?->response->media->count() > 0 ? 'Attached Images (' . $return_order->dispute?->response->media->count() . ')' : 'No images attached' }}
                        </p>
                        @if ($return_order->dispute?->response->media->count() > 0)
                            <div class="grid grid-cols-9 gap-2">
                                @foreach ($return_order->dispute?->response->media as $image)
                                    <div class="relative pt-[100%] w-full">
                                        <div class="absolute top-0 left-0 w-full h-full">
                                            <img class="object-cover w-full h-full rounded-xl"
                                                src="{{ $this->get_media_url($image) }}" alt="">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($return_order->status->name === 'Pending Resolution')    
            <div>
                <p>Decisions:</p>
                <x-button.primary-gradient-button wire:click="$set('action','refund_only')" class="w-[244px]">Refund Only</x-button.primary-gradient-button>
                <x-button.primary-gradient-button wire:click="$set('action','return_only')" class="w-[244px]">Return Only</x-button.primary-gradient-button>
                <x-button.primary-gradient-button wire:click="$set('action','return_and_refund')" class="w-[244px]">Return and Refund</x-button.primary-gradient-button>
                <x-button.primary-gradient-button wire:click="$set('action','cancel')" class="w-[244px]">Cancel</x-button.primary-gradient-button>
            </div>
        @endif
    </div>

    @if ($action)
        @switch($action)
            @case('refund_only')
                <livewire:admin.disputes.return-orders.modals.admin-return-orders-refund-modal :merchant="$merchant" :return_order_id="$return_order->id" />
                @break
            @case('return_only')
                <livewire:admin.disputes.return-orders.modals.admin-return-orders-return-modal :merchant="$merchant" :return_order_id="$return_order->id" />
            @break
            @case('return_and_refund')
                <livewire:admin.disputes.return-orders.modals.admin-return-orders-return-refund-modal :merchant="$merchant" :return_order_id="$return_order->id" />
                @break
            @case('cancel')
                <livewire:admin.disputes.return-orders.modals.admin-return-orders-cancel-modal :merchant="$merchant" :return_order_id="$return_order->id" />
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
    <x-loader.black-screen wire:loading wire:target='action'>
        <x-loader.clock />
    </x-loader.black-screen>
</x-main.content>
