<x-main.content>
    <x-main.action-header>
        <x-slot:title>Disputes</x-slot:title>
        <x-slot:actions>
            <x-button.filled-button href="{{ route('user.disputes.create') }}">file a dispute</x-button.filled-button>
        </x-slot:actions>
    </x-main.action-header>

    <div class="grid grid-cols-1 gap-3 mb-8">
        <x-card.filter-card label="Transaction Disputes" data="{{ $this->disputes_count }}" :isActive="true" />
    </div>

    <x-layout.search-container class="mb-8">
        <x-input.search wire:model.live='searchTerm' icon_position="left" />
    </x-layout.search-container>

    <div>
        <x-table.rounded>
            <x-slot:table_header>
                <x-table.rounded.th>Category</x-table.rounded.th>
                <x-table.rounded.th>Transaction Date</x-table.rounded.th>
                <x-table.rounded.th>Transaction Amount</x-table.rounded.th>
                <x-table.rounded.th>Transaction Number</x-table.rounded.th>
                <x-table.rounded.th>
                    <div class="flex items-center">
                        <p>Date Created</p>
                        <button wire:click='toggleSortDirection'>
                            <x-icon.sort />
                        </button>
                    </div>
                </x-table.rounded.th>
                <x-table.rounded.th>Status</x-table.rounded.th>
            </x-slot:table_header>
            <x-slot:table_data>
                <tr><td class="pt-8"></td></tr>
                @foreach ($disputes as $key => $dispute)
                    <x-table.rounded.row wire:click="view_dispute({{ $dispute->id }})" wire:key="dispute-{{ $key }}" class="hover:bg-rp-neutral-50 cursor-pointer">
                        <x-table.rounded.td>{{ $dispute->reason->name }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ \Carbon\Carbon::parse($dispute->transaction->created_at)->timezone('Asia/Manila')->format('m/d/Y') }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ \Number::currency($dispute->transaction->amount, 'PHP') }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ $dispute->transaction->txn_no }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ \Carbon\Carbon::parse($dispute->created_at)->timezone('Asia/Manila')->format('m/d/Y') }}</x-table.rounded.td>
                        <x-table.rounded.td>
                            <div class="flex items-center justify-between">
                                @switch($dispute->status)
                                    @case('pending')
                                        <x-status color="neutral" class="w-28">Pending</x-status>
                                        @break
                                    @case('partially-paid')
                                        <x-status color="green" class="w-28">Resolved - Partially Paid</x-status>
                                        @break
                                    @case('fully-paid')
                                        <x-status color="green" class="w-28">Resolved - Fully Paid</x-status>
                                        @break
                                    @case('denied')
                                        <x-status color="red" class="w-28">Denied</x-status>
                                        @break
                                    @default
                                        <x-status color="red" class="w-28">{{ $dispute->status }}</x-status>
                                @endswitch
                                <x-icon.chevron-right />
                            </div>
                        </x-table.rounded.td>
                    </x-table.rounded.row>
                @endforeach
            </x-slot:table_data>
        </x-table.rounded>
    </div>  

    {{-- Pagination --}}
    <div class="w-full flex items-center justify-center gap-8">
        @if ($disputes->hasPages())
            <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                <button wire:click="previousPage" {{ $disputes->onFirstPage() ? 'disabled' : '' }}
                    class="{{ $disputes->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M6 11.5001L1 6.50012L6 1.50012" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <button class="h-full bg-white border-r px-4 py-2 cursor-default">{{ $element }}</button>
                    @else
                        <button wire:click="gotoPage({{ $element }})"
                            class="h-full border-r px-4 py-2 {{ $element == $disputes->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                    @endif
                @endforeach

                <button wire:click="nextPage" {{ !$disputes->hasMorePages() ? 'disabled' : '' }}
                    class="{{ !$disputes->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

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