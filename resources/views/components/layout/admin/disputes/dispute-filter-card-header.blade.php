@props(['return_order_disputes', 'transaction_disputes'])

<div {{ $attributes->merge(['class' => "w-full flex gap-3" ]) }} wire:ignore>

    <a  href="{{ route('admin.disputes.return-orders.index') }}" class="{{ request()->routeIs('admin.disputes.return-orders.index') ? 'text-primary-500 bg-primary-100 border-primary-500' : 'text-rp-neutral-600 bg-white border-transparent hover:bg-primary-100' }} flex flex-col justify-center px-4 py-4 rounded-xl border-2 transition duration-300 w-1/2">
        <span class="text-base break-words">Return Order Disputes</span>
        <span class="text-3.5xl font-bold break-words">{{ $return_order_disputes ?? 0 }}</span>
    </a>

    <a  href="{{ route('admin.disputes.transactions.index') }}" class="{{ request()->routeIs('admin.disputes.transactions.index') ? 'text-primary-500 bg-primary-100 border-primary-500' : 'text-rp-neutral-600 bg-white border-transparent hover:bg-primary-100' }} flex flex-col justify-center px-4 py-4 rounded-xl border-2 transition duration-300 w-1/2">
        <span class="text-base break-words">Transaction Disputes</span>
        <span class="text-3.5xl font-bold break-words">{{ $transaction_disputes ?? 0 }}</span>
    </a>

</div>