<x-main.content>
    <x-main.title class="mb-8">Return Dispute Details</x-main.title>

    <div class="flex flex-col w-full px-4 py-3 mb-6 text-sm break-words bg-white rounded-md">
        <div class="flex flex-row justify-between w-full pb-2 border-b">
            <div class="flex flex-col max-w-[50%]">
                <p>Buyer: <span class="text-primary-600">{{ $this->return_order->product_order->buyer->name }}</span></p>
                <p>Merchant: <span
                        class="text-primary-600">{{ $this->return_order->product_order->product->merchant->name }}</span>
                </p>
            </div>
            <div class="max-w-[50%]">
                <p>{{ $this->return_order->product_order->processed_at ? 'Delivered to buyer on ' . \Carbon\Carbon::parse($this->return_order->product_order->processed_at)->format('F d, Y') : 'No date available' }}
                </p>
                <p>{{ 'Return requested on ' . \Carbon\Carbon::parse($this->return_order->created_at)->format('F d, Y') }}
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
                        {{ $this->return_order->product_order->product->name }}</h2>
                    <p class="truncate">{{ $this->return_order->product_order->product->description }}</p>
                    <div class="flex flex-col mt-3">
                        <div>
                            <span>Order Number:</span>
                            <span class="text-primary-600">{{ $this->return_order->product_order->order_number }}</span>
                        </div>
                        <div>
                            <span>Tracking Number:</span>
                            <span
                                class="text-primary-600">{{ $this->return_order->product_order->tracking_number }}</span>
                        </div>
                        <div>
                            <span>Return Request Number:</span>
                            <span class="text-primary-600">{{ $this->return_order->id }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total --}}
            <div class="flex flex-col justify-center w-4/12 p-4">
                <div class="flex flex-col gap-2">
                    <p class="text-rp-neutral-500">Paid Price + Shipping Fee: <span class="text-rp-neutral-700">₱
                            {{ number_format($this->return_order->product_order->amount + $this->return_order->product_order->shipping_fee, 2) }}</span>
                    </p>
                    {{-- <p class="text-rp-neutral-500">Refund Amount: <span class="text-rp-neutral-700">0</span></p> --}}
                    <p class="text-rp-neutral-500">Return Reason: <span
                            class="text-rp-neutral-700">{{ $this->return_order->reason->name }}</span></p>
                </div>
            </div>

            {{-- Status --}}
            <div class="flex items-center w-3/12">
                <div class="flex flex-row items-center justify-between w-full">
                    @if ($return_order->status->parent_status)
                        @switch($return_order->status->parent_status->slug)
                            @case('return_in_progress')
                                <div class="flex flex-col items-center">
                                    <x-status color="yellow"
                                        class="w-44">{{ $return_order->status->parent_status->name }}</x-status>
                                </div>
                            @break

                            @case('dispute_in_progress')
                                <div class="flex flex-col items-center">
                                    <x-status color="yellow"
                                        class="w-44">{{ $return_order->status->parent_status->name }}</x-status>
                                    <p class="font-light">{{ $return_order->status->name }}</p>
                                </div>
                            @break

                            @case('rejected')
                                <div class="flex flex-col items-center">
                                    <x-status color="red"
                                        class="w-44">{{ $return_order->status->parent_status->name }}</x-status>
                                </div>
                            @break

                            @case('resolved')
                                <div class="flex flex-col items-center">
                                    <x-status color="green"
                                        class="w-44">{{ $return_order->status->parent_status->name }}</x-status>
                                    <p class="font-light">{{ $return_order->status->name }}</p>
                                </div>
                            @break
                        @endswitch
                    @else
                        @switch($return_order->status->slug)
                            @case('return_initiated')
                                <x-status color="neutral" class="w-44">
                                    <p>Pending Merchant Approval</p>
                                </x-status>
                            @break

                            @case('return_in_progress')
                                <x-status color="yellow" class="w-44">
                                    <p>{{ $return_order->status->name }}</p>
                                </x-status>
                            @break

                            @case('rejected')
                                <x-status color="red" class="w-44">
                                    <p>{{ $return_order->status->name }}</p>
                                </x-status>
                            @break

                            @default
                        @endswitch
                    @endif
                    {{-- <div class="cursor-pointer">
                        <x-icon.thin-chevron-right />
                    </div> --}}
                </div>
            </div>
        </div>
    </div>

    <div class="w-full px-4 py-3 mb-6 break-words bg-white rounded-md">
        <h2 class="mb-3 overflow-hidden text-lg font-bold truncate">Buyer Dispute Details</h2>
        <div class="space-y-3">
            <div class="flex text-sm">
                <p class="font-bold w-[85px]">Comments: </p>
                <p class="w-[calc(100%-85px)]">{{ $this->return_order->comment }}</p>
            </div>
            <div class="text-sm">
                <p class="mb-1 font-bold">
                    {{ $return_order->media->count() > 0 ? 'Attached Images (' . $return_order->media->count() . ')' : 'No images attached' }}
                </p>
                @if ($return_order->media->count() > 0)
                    <div class="grid grid-cols-9 gap-2">
                        @foreach ($return_order->media as $image)
                            <div class="relative pt-[100%] w-full">
                                <div class="absolute top-0 left-0 w-full h-full">
                                    <img class="object-cover w-full h-full rounded-xl"
                                        src="{{ $this->get_media_url($image, 'thumbnail') }}" alt="">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($return_order->dispute?->response)
        <div class="w-full px-4 py-3 mb-6 break-words bg-white rounded-md">
            <h2 class="mb-3 overflow-hidden text-lg font-bold truncate">Merchant Response</h2>
            <div class="space-y-3">
                <div class="flex text-sm">
                    <p class="font-bold w-[85px]">Comments:</p>
                    <p class="w-[calc(100%-85px)]">
                        {{ $return_order->dispute ? $return_order->dispute->comment : 'No response' }}
                    </p>
                </div>
                <div class="text-sm">
                    @if ($return_order->dispute)
                        <p class="mb-1 font-bold">
                            {{ $return_order->dispute->media->count() > 0 ? 'Attached Images (' . $return_order->dispute->media->count() . ')' : 'No images attached' }}
                        </p>
                        @if ($return_order->dispute->media->count() > 0)
                            <div class="grid grid-cols-9 gap-2">
                                @foreach ($return_order->dispute->media as $image)
                                    <div class="relative pt-[100%] w-full">
                                        <div class="absolute top-0 left-0 w-full h-full">
                                            <img class="object-cover w-full h-full rounded-xl"
                                                src="{{ $this->get_media_url($image, 'thumbnail') }}" alt="">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif

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
</x-main.content>
