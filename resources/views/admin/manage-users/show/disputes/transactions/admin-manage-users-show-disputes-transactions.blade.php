<x-main.content class="!px-16 !py-10">

    <livewire:components.layout.admin.user-details-header :user="$user" />

    <x-layout.admin.user-details.disputes.dispute-filter-card-header class="my-8" :user="$user" :disputesCount="$disputesCount" />

    <x-layout.search-container class="mb-8">
        <x-input.search icon_position="left" wire:model.live='searchTerm' />
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
                        <div class="cursor-pointer" wire:click="sortTable('created_at')">
                            <x-icon.sort />
                        </div>
                    </div>
                </x-table.rounded.th>
                <x-table.rounded.th>Status</x-table.rounded.th>
            </x-slot:table_header>
            <x-slot:table_data>
                <tr>
                    <td class="pt-8"></td>
                </tr>
                @foreach ($disputes as $dispute)
                    <x-table.rounded.row>
                        <x-table.rounded.td>{{ $dispute->reason->name }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ \Carbon\Carbon::parse($dispute->transaction->created_at)->format('F j, Y') }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ \Number::currency($dispute->transaction->amount, 'PHP') }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ $dispute->transaction->ref_no }}</x-table.rounded.td>
                        <x-table.rounded.td>{{ \Carbon\Carbon::parse($dispute->created_at)->format('F j, Y') }}</x-table.rounded.td>


                        <x-table.rounded.td>
                            <div class="flex items-center justify-between">
                                @switch($dispute->status)
                                    @case('pending')
                                        <x-status color="neutral" class="w-32">
                                            {{ ucfirst($dispute->status) }}
                                        </x-status>
                                    @break

                                    @case('partially-paid')
                                        <x-status color="yellow" class="w-32">
                                            {{ ucfirst($dispute->status) }}
                                        </x-status>
                                    @break

                                    @case('fully-paid')
                                        <x-status color="green" class="w-32">
                                            {{ ucfirst($dispute->status) }}
                                        </x-status>
                                    @break

                                    @case('denied')
                                        <x-status color="red" class="w-32">
                                            {{ ucfirst($dispute->status) }}
                                        </x-status>
                                    @break

                                    @default
                                @endswitch
                                <div class="cursor-pointer">
                                    <a
                                        href="{{ route('admin.manage-users.show.disputes.transactions.details', ['user' => $user->id, 'transactionDispute' => $dispute->id]) }}">
                                        <x-icon.chevron-right /></a>
                                </div>
                            </div>
                        </x-table.rounded.td>
                    </x-table.rounded.row>
                @endforeach

            </x-slot:table_data>
        </x-table.rounded>
    </div>
    {{-- Pagination --}}
    <div class="flex items-center justify-center w-full gap-8">
        @if ($disputes->hasPages())
            <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                <button wire:click="previousPage" {{ $disputes->onFirstPage() ? 'disabled' : '' }}
                    class="{{ $disputes->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M6 11.5001L1 6.50012L6 1.50012" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <!-- Pagination Elements -->
                @foreach ($elements as $element)
                    <!-- "Three Dots" Separator -->
                    @if (is_string($element))
                        <button class="h-full px-4 py-2 bg-white border-r cursor-default">{{ $element }}</button>
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

</x-main.content>
