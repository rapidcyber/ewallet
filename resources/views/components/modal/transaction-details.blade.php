@props([
    'transactionDetails',
    'titleColor' => 'red',
])

@php
    $titleColors = [
        'red' => 'text-rp-red-600',
        'primary' => 'text-primary-600'
    ];
@endphp
<div class="w-96 relative px-4 py-3 bg-white rounded-lg">
    <div class="absolute cursor-pointer top-4 right-3" @click="visible=false">
        <x-icon.close />
    </div>
    <div class="bg-white py-6 rounded-lg w-full">
        <div class="w-[80%] mx-auto">
            <h3 class="{{ $titleColors[$titleColor] }} text-lg font-bold italic text-center">{{ $transactionDetails['transaction_type'] }}</h3>
            <div class="leading-3 py-5 text-center">
                <span>{{ $transactionDetails['label'] }}</span>
                <p class="text-lg font-bold">{{ $transactionDetails['entity_name'] }}</p>
                <span class="text-sm">{{ $transactionDetails['phone_number'] }}</span>
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex flex-row justify-between">
                    <span>Amount</span>
                    <span>{{ \Number::currency($transactionDetails['amount'], 'PHP') }}</span>
                </div>
    
                @isset($transactionDetails['service_fee'])
                    <div class="flex flex-row justify-between">
                        <span>Service Fee</span>
                        <span>{{ \Number::currency($transactionDetails['service_fee'], 'PHP') }}</span>
                    </div>
                @endisset
    
                <div class="flex flex-row justify-between">
                    <span class="font-bold">Total</span>
                    <span class="text-lg font-bold">{{ isset($transactionDetails['total_amount']) ? \Number::currency($transactionDetails['amount'], 'PHP') : \Number::currency($transactionDetails['amount'], 'PHP') }}</span>
                </div>
            </div>

            <div class="mt-3">
                <p class="text-center text-sm">Transaction No. {{ $transactionDetails['txn_no'] }}</p>
                <p class="text-center text-sm">Reference No. {{ $transactionDetails['ref_no'] }}</p>
                <p class="text-center text-sm">{{ $transactionDetails['created_at'] }}</p>
            </div>
        </div>
    </div>
</div>