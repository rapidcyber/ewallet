<div
    class="absolute flex flex-col gap-8 bg-white p-10 rounded-2xl max-w-[90%] max-h-[95%] overflow-y-auto {{ $return_order->dispute ? 'w-[1288px]' : 'w-[1180px]' }}">
    {{-- CLOSE BUTTON --}}
    <button class="absolute top-6 right-6" @click="$dispatch('closeModal')">
        <x-icon.close />
    </button>

    {{-- HEADING --}}
    <div>
        <h3 class="text-2xl font-bold mb-2">View Resolution</h3>
    </div>

    {{-- TABLE --}}
    <table>
        <tr class="border-b  font-bold">
            <th class="text-left text-lg  font-bold py-[10px]">Buyer</th>
            <th class="text-left text-lg  font-bold py-[10px]">Order Details</th>
            <th class="text-left text-lg  font-bold py-[10px]">Total Amount</th>
            <th class="text-left text-lg  font-bold py-[10px]">Return Details</th>
        </tr>
        {{-- DATA --}}
        <tr>
            <td class="rounded-l-lg bg-white py-6 align-top">
                <div class="flex gap-[8px] items-center">
                    @if ($return_order->product_order->buyer->media->isNotEmpty())
                        <img src="{{ $this->get_media_url($return_order->product_order->buyer->media->first(), 'thumbnail') }}"
                            alt="" class="w-[32px] h-[32px] object-cover rounded-full">
                    @else
                        <img src="{{ url('images/user/default-avatar.png') }}" alt=""
                            class="w-[32px] h-[32px] object-cover rounded-full">
                    @endif
                    <span class="font-bold text-[13px]">{{ $return_order->product_order->buyer->name }}</span>
                </div>
            </td>
            <td class="bg-white py-6 align-top">
                <div class="flex gap-3">
                    <div class="w-16 h-16">
                        <img src="{{ $this->get_media_url($return_order->product_order->product->first_image, 'thumbnail') }}"
                            alt="" class="w-full h-full object-cover rounded-md">
                    </div>
                    <div class="w-[calc(100%-64px)]">
                        <h5 class="text-[13px] font-bold">{{ $return_order->product_order->product->name }}</h5>
                        {{-- <p class="text-[11px]">Silver, 64GB</p> --}}
                        <p class="text-[11px]">Order Number:</p>
                        <p class="text-[11px] text-rp-red-600">{{ $return_order->product_order->order_number }}</p>
                    </div>
                </div>
            </td>
            <td class="bg-white py-6 align-top">
                <div>
                    <h5 class="text-[13px] font-bold">P
                        {{ number_format($return_order->product_order->amount * $return_order->product_order->quantity, 2) }}
                    </h5>
                    <p class="text-[11px] w-fit px-2 py-[2px] text-rp-red-600 border border-rp-red-600 rounded-[5px]">
                        COD</p>
                    <p class="text-[11px]">Paid Price + Shipping Fee: P
                        {{ number_format($return_order->product_order->amount * $return_order->product_order->quantity + $return_order->product_order->shipping_fee, 2) }}
                    </p>
                    {{-- <p class="text-[11px]">Refund Amount: 0</p> --}}
                </div>
            </td>
            <td class="bg-white py-6 align-top">
                <div class="text-rp-neutral-600">
                    <h5 class="text-[13px] font-bold">Resolved</h5>
                    <p
                        class="text-[11px] w-fit px-2 py-[2px] text-rp-green-600 border border-rp-green-600 bg-rp-green-200 rounded-[5px]">
                        {{ $return_order->status->name }}</p>
                    <p class="text-[11px]">Return Order Number: <span
                            class="text-rp-red-500">{{ $return_order->id }}</span>
                    </p>
                    <p class="text-[11px]">Return Reason: {{ $return_order->reason->name }}</p>
                </div>
            </td>
        </tr>
    </table>
    <div class="grid grid-cols-2 gap-10">
        {{-- process history --}}
        <div class="flex-1">
            <h3 class="mb-5 font-bold text-[19px]">History</h3>
            <div class="flex flex-col gap-8 max-h-[370px] overflow-y-auto">
                @foreach ($return_order->logs as $key => $log)
                    <div class="tracker-dets flex gap-[11px] {{ $loop->first ? '' : 'opacity-70' }}"
                        wire:key="log-{{ $key }}">
                        <span
                            class="text-[11px] min-w-[120px]">{{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}</span>
                        <div class="relative pl-10 grow">
                            <x-icon.circle />
                            <h5 class="font-bold">{{ $log->title }}</h5>
                            <p class="text-[11px]">{{ $log->description ?? '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- resolution/disputes --}}
        <div class="flex-1">
            <h3 class="mb-5">Final Resolution: <span
                    class="text-rp-green-600">{{ $return_order->status->name }}</span>
            </h3>
            <div
                class="relative p-5 h-[370px] overflow-y-auto rounded-lg border border-rp-neutral-200 bg-rp-neutral-100">
                @if (!$return_order->dispute)
                    <p
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 font-bold text-rp-neutral-600 text-[13px]">
                        No dispute details to show</p>
                @else
                    <div class="flex flex-col gap-4">
                        @if ($return_order->dispute?->decision)
                            @if ($return_order->dispute?->decision?->type == 'cancel' and $return_order->cancellation)
                                <div class="relative pl-10 pb-4 border-b last:border-none">
                                    <x-icon.circle />
                                    <h5 class="font-bold text-[13px] mb-3">Dispute decision - <span>{{ ucwords(str_replace('_', ' ', $return_order->dispute?->decision?->type)) }}</span></h5>
                                    <div class="flex text-[11px] mb-2">
                                        <p class="min-w-[72px] font-bold">Reason:</p>
                                        <p>{{ $return_order->cancellation->reason->name }}</p>
                                    </div>
                                    <div class="flex text-[11px] mb-2">
                                        <p class="min-w-[72px] font-bold">Comments:</p>
                                        <p>{{ $return_order->cancellation->comment }}</p>
                                    </div>
                                    <div>
                                        <p class="font-bold text-[11px] mb-1">Attached Images (<span>{{ $return_order->cancellation->media->count() }}</span>)</p>
                                        <div class="flex gap-2">
                                            @foreach ($return_order->cancellation->media as $image)
                                                <img src="{{ $this->get_media_url($image) }}" alt=""
                                                    class="w-16 h-[53px] rounded-lg object-contain">
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="relative pl-10 pb-4 border-b last:border-none">
                                    <x-icon.circle />
                                    <h5 class="font-bold text-[13px] mb-3">Dispute decision - <span>{{ ucwords(str_replace('_', ' ', $return_order->dispute?->decision?->type)) }}</span></h5>
                                    <div class="flex text-[11px] mb-2">
                                        <p class="min-w-[72px] font-bold">Reason:</p>
                                        <p>{{ $return_order->dispute->decision->comment ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <p class="font-bold text-[11px] mb-1">Attached Images (<span>{{ $return_order->dispute->decision->media->count() }}</span>)</p>
                                        <div class="flex gap-2">
                                            @foreach ($return_order->dispute->decision->media as $image)
                                                <img src="{{ $this->get_media_url($image) }}" alt=""
                                                    class="w-16 h-[53px] rounded-lg object-contain">
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                        @if ($return_order->dispute?->response)    
                            <div class="relative pl-10 pb-4 border-b last:border-none">
                                <x-icon.circle />
                                <h5 class="font-bold text-[13px] mb-3">Your Response</h5>
                                <div class="flex text-[11px] mb-2">
                                    <p class="min-w-[72px] font-bold">Comments:</p>
                                    <p>{{ $return_order->dispute?->response->comment }}</p>
                                </div>
                                <div>
                                    <p class="font-bold text-[11px] mb-1">Attached Images (<span>{{ $return_order->dispute->response->media->count() }}</span>)</p>
                                    <div class="flex gap-2">
                                        @foreach ($return_order->dispute->response->media as $image)
                                            <img src="{{ $this->get_media_url($image) }}" alt="" class="w-16 h-[53px] rounded-lg object-contain">
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($return_order->dispute)    
                            <div class="relative pl-10 pb-4 border-b last:border-none">
                                <x-icon.circle />
                                <h5 class="font-bold text-[13px] mb-3">Buyer Dispute Details</h5>
                                {{-- <p class="text-[11px] min-w-[72px] font-bold">Dispute Reason: <span class="font-normal">Received wrong item</span></p> --}}
                                <div class="flex text-[11px] mb-2">
                                    <p class="min-w-[72px] font-bold">Comments:</p>
                                    <p>{{ $return_order->dispute->comment }}</p>
                                </div>
                                <div>
                                    <p class="font-bold text-[11px] mb-1">Attached Images (<span>{{ $return_order->dispute->media->count() }}</span>)</p>
                                    <div class="flex gap-2">
                                        @foreach ($return_order->dispute->media as $image)    
                                            <img src="{{ $this->get_media_url($image) }}" alt="" class="w-16 h-[53px] rounded-lg object-contain">
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    <x-button.filled-button @click="$dispatch('closeModal')">Confirm</x-button.filled-button>
</div>
