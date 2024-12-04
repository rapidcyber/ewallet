<x-main.content class="!px-16 !py-10">
    <x-main.title class="mb-8">Disputes</x-main.title>

    <x-layout.admin.disputes.dispute-filter-card-header return_order_disputes="{{ $this->return_order_count }}" transaction_disputes="{{ $this->disputes_count }}" />

    <x-layout.search-container class="my-8">
        <x-input.search wire:model.live='searchTerm' icon_position="left" />
    </x-layout.search-container>

    <div class="overflow-auto">
        <x-table.rounded>
            <x-slot:table_header>
                <x-table.rounded.th class="w-56 max-w-56 min-w-56">User</x-table.rounded.th>
                <x-table.rounded.th>Contact Number</x-table.rounded.th>
                <x-table.rounded.th>Category</x-table.rounded.th>
                <x-table.rounded.th>Transaction Date</x-table.rounded.th>
                <x-table.rounded.th>Transaction Amount</x-table.rounded.th>
                <x-table.rounded.th>Reference No.</x-table.rounded.th>
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
            <x-slot:table_data  class="break-words">
                <tr><td class="pt-8"></td></tr>
                @foreach ($disputes as $dispute)
                    <x-table.rounded.row>
                        <x-table.rounded.td class="w-52 max-w-52 min-w-52">
                            {{-- <div class="flex items-center gap-2 w-full">
                                <div class="w-9 h-9">
                                    @if ($dispute->transaction->sender->media->first())
                                        <img src="{{ $this->get_media_url($dispute->transaction->sender->media->first()) }}" alt="" class="w-full h-full rounded-full object-cover"/>
                                    @else
                                        <img src="{{ url('images/user/default-avatar.png') }}" alt="" class="w-full h-full rounded-full object-cover"/>
                                    @endif
                                </div>
                                <div class="w-[calc(100%-36px)]">
                                </div>
                            </div> --}}
                            <p>{{ $dispute->transaction->sender->name }}</p>
                        </x-table.rounded.td>
                        <x-table.rounded.td class="w-52 max-w-52 min-w-52">{{ $this->format_phone_number($dispute->transaction->sender->phone_number, $dispute->transaction->sender->phone_iso) }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ $dispute->reason->name }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ \Carbon\Carbon::parse($dispute->transaction->created_at)->timezone('Asia/Manila')->format('m/d/Y') }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ \Number::currency($dispute->transaction->amount, 'PHP') }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ $dispute->transaction->ref_no }}</x-table.rounded.td>
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
                                <a  href="{{ route('admin.disputes.transactions.show', ['transactionDispute' => $dispute->id]) }}" class="cursor-pointer">
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
