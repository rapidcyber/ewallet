@props(['returnOrderCount', 'disputesCount', 'user'])

<div {{ $attributes->merge(['class' => 'w-full flex gap-3']) }} wire:ignore>

    <a  href="{{ route('admin.manage-users.show.disputes.return-orders.index', ['user' => $user->id]) }}"
        class="{{ request()->routeIs('admin.manage-users.show.disputes.return-orders.index') ? 'text-primary-500 bg-primary-100 border-primary-500' : 'text-rp-neutral-600 bg-white border-transparent hover:bg-primary-100' }} flex flex-col justify-center px-4 py-4 rounded-xl border-2 transition duration-300 w-1/2">
        <span class="text-base break-words">Return Order Disputes</span>
        <span class="text-3.5xl font-bold break-words">{{ $returnOrderCount ?? 0 }}</span>
    </a>

    <a  href="{{ route('admin.manage-users.show.disputes.transactions.index', ['user' => $user->id]) }}"
        class="{{ request()->routeIs('admin.manage-users.show.disputes.transactions.index') ? 'text-primary-500 bg-primary-100 border-primary-500' : 'text-rp-neutral-600 bg-white border-transparent hover:bg-primary-100' }} flex flex-col justify-center px-4 py-4 rounded-xl border-2 transition duration-300 w-1/2">
        <span class="text-base break-words">Transaction Disputes</span>
        <span class="text-3.5xl font-bold break-words">{{ $disputesCount ?? 0 }}</span>
    </a>

</div>
