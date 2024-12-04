<x-main.content class="!px-16 !py-10">

    <livewire:components.layout.admin.merchant-details-header :merchant="$merchant" />

    <x-layout.admin.merchant-details.disputes.dispute-filter-card-header :merchant="$merchant" :returnOrderCount="$this->return_orders_disputes_count"
        :disputesCount="$this->transaction_disputes_count" :merchant="$merchant" class="my-8" />

    <x-layout.search-container class="mb-8">
        <x-input.search wire:model.live='searchTerm' icon_position="left" />
    </x-layout.search-container>

    <div>
        <x-table.rounded>
            <x-slot:table_header>
                <x-table.rounded.th>Category</x-table.rounded.th>
                <x-table.rounded.th>Transaction Date</x-table.rounded.th>
                <x-table.rounded.th>Transaction Amount</x-table.rounded.th>
                <x-table.rounded.th>Transaction Reference Number</x-table.rounded.th>
                <x-table.rounded.th>
                    <div class="flex items-center">
                        <p>Date Created</p>
                        <button wire:click="updatedSortDirection">
                            <x-icon.sort />
                        </button>
                    </div>
                </x-table.rounded.th>
                <x-table.rounded.th>Status</x-table.rounded.th>
            </x-slot:table_header>
            <x-slot:table_data>
                <tr><td class="pt-8"></td></tr>
                @foreach ($disputes as $dispute)
                    <x-table.rounded.row>
                        <x-table.rounded.td>{{ $dispute->reason->name }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ \Carbon\Carbon::parse($dispute->transaction->created_at)->format('m/d/Y') }}</x-table.rounded.td>
                        <x-table.rounded.td>₱{{ number_format($dispute->transaction->amount, 2) }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ $dispute->transaction->ref_no }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ \Carbon\Carbon::parse($dispute->created_at)->format('m/d/Y') }}</x-table.rounded.td>
                        <x-table.rounded.td>
                            <div class="flex items-center justify-between">
                                @switch($dispute->status)
                                    @case('pending')
                                        <x-status color="neutral">Pending</x-status>
                                        @break
                                    @case('partially-paid')
                                        <x-status color="green">Resolved - Partially Paid</x-status>
                                        @break
                                    @case('fully-paid')
                                        <x-status color="green">Resolved - Fully Paid</x-status>
                                        @break
                                    @case('denied')
                                        <x-status color="red">Denied</x-status>
                                        @break
                                    @default
                                        <x-status color="red">{{ $dispute->status }}</x-status>
                                @endswitch
                                <a  href="{{ route('admin.manage-merchants.show.disputes.transactions.details', ['transactionDispute' => $dispute->id, 'merchant' => $merchant->id]) }}" class="cursor-pointer">
                                    <x-icon.chevron-right />
                                </a>
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
                            class="h-full bg-white border-r px-4 py-2 {{ $element == $disputes->currentPage() ? 'cursor-default' : 'cursor-pointer' }}">{{ $element }}</button>
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