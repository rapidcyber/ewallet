<div class="flex h-full">
    <x-main.content class="overflow-y-auto grow {{ $transactionDetails ? '!px-[18.5px]' : '' }}">
        <x-main.action-header>
            <x-slot:title>Cash Inflow History</x-slot:title>
            <x-slot:actions>
                <div class="flex flex-row items-center gap-3">
                    <p>Sort by:</p>
                    <x-dropdown.select-date wire:model.live="dateFilter" class="h-[32px]">
                        <x-dropdown.select-date.option value="past_year">Past Year</x-dropdown.select-date.option>
                        <x-dropdown.select-date.option value="past_30_days">Past 30 days</x-dropdown.select-date.option>
                        <x-dropdown.select-date.option value="past_week">Past Week</x-dropdown.select-date.option>
                        <x-dropdown.select-date.option value="day">Day</x-dropdown.select-date.option>
                    </x-dropdown.select-date>
                </div>
            </x-slot:actions>
        </x-main.action-header>

        <x-card.money-comparison-card title="Money Received" :date="$dateFilter" :present="$moneyReceived" :past="$vsMoneyReceived" />


        <div class="relative grid {{ !empty($transactionDetails) ? '' : 'lg:grid-cols-5' }} grid-cols-3  gap-[15px] mb-8">
            <x-card.filter-card wire:click="$set('activeBox', 'ALL')" :isActive="$activeBox === 'ALL'" label="All"
                :data="$allCashInflowCount" color="red" />
            <x-card.filter-card wire:click="$set('activeBox', 'TR')" :isActive="$activeBox === 'TR'" label="Money Transfers"
                :data="$moneyTransferCount" color="red" />
            <x-card.filter-card wire:click="$set('activeBox', 'OR')" :isActive="$activeBox === 'OR'" label="Order Payments"
                :data="$orderPaymentsCount" color="red" />
            <x-card.filter-card wire:click="$set('activeBox', 'CI')" :isActive="$activeBox === 'CI'" label="Cash In"
                :data="$cashInCount" color="red" />
            <x-card.filter-card wire:click="$set('activeBox', 'IV')" :isActive="$activeBox === 'IV'" label="Invoice Payments"
                :data="$invoicePaymentsCount" color="red" />
        </div>

        <div class="space-y-8">
            <x-layout.search-container>
                <x-input.search icon_position="left" wire:model.live.debounce.300ms="searchTerm" />
            </x-layout.search-container>

            {{-- TABLE --}}
            <div class="overflow-auto">
                <x-table.rounded>
                    <x-slot:table_header>
                        <x-table.rounded.th class="{{ $transactionDetails ? '' : 'w-52 min-w-52 max-w-52' }}">Transaction type</x-table.rounded.th>
                        <x-table.rounded.th class="{{ $transactionDetails ? '' : 'w-72 min-w-72 max-w-72' }}">Sender</x-table.rounded.th>
                        <x-table.rounded.th class="{{ $transactionDetails ? '' : 'w-48 min-w-48 max-w-48' }}">
                            <div class="flex flex-row items-center">
                                <span>Amount</span>
                                <button wire:click="sortView('amount')">
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th>
                            <div class="flex flex-row items-center">
                                <span>Date Created</span>
                                <button wire:click="sortView('created_at')">
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th>Transaction Number</x-table.rounded.th>
                        <x-table.rounded.th>Status</x-table.rounded.th>
                    </x-slot:table_header>
                    <x-slot:table_data>
                        <tr>
                            <td class="pt-8"></td>
                        </tr>
                        @foreach ($cashInflows as $key => $cashIn)
                            @php
                                $isActiveRow = false;
                                if (!empty($transactionDetails['txn_no'])) {
                                    $isActiveRow = $cashIn->txn_no === $transactionDetails['txn_no'];
                                }
                            @endphp
                            <x-table.rounded.row
                                class="overflow-hidden cursor-pointer {{ $isActiveRow ? 'border border-rp-red-500 ' : 'hover:bg-rp-neutral-50' }}"
                                wire:click="handleTableRowClick('{{ $cashIn->txn_no }}')">
                                <x-table.rounded.td>
                                    {{ ucwords(str_replace('_', ' ', $cashIn->type->name)) }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ get_class($cashIn->sender) == 'App\Models\User' ? $this->format_phone_number($cashIn->sender->phone_number, $cashIn->sender->phone_iso) : $cashIn->sender->name }}
                                </x-table.rounded.td>
                                <x-table.rounded.td class="font-bold text-rp-red-500">
                                    ₱{{ number_format($cashIn->amount, 2) }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ \Carbon\Carbon::parse($cashIn->created_at)->timezone('Asia/Manila')->format('F j, Y') }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ $cashIn->txn_no }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    @switch($cashIn->status->slug)
                                        @case('successful')
                                            <x-status color="green" class="w-28">
                                                {{ ucfirst($cashIn->status->name) }}
                                            </x-status>
                                        @break
    
                                        @case('pending')
                                            <x-status color="neutral" class="w-28">
                                                {{ ucfirst($cashIn->status->name) }}
                                            </x-status>
                                        @break
    
                                        @case('failed')
                                            <x-status color="red" class="w-28">
                                                {{ ucfirst($cashIn->status->name) }}
                                            </x-status>
                                        @break
    
                                        @default
                                    @endswitch
                                </x-table.rounded.td>
                            </x-table.rounded.row>
                        @endforeach
                    </x-slot:table_data>
                </x-table.rounded>
            </div>
            {{-- Pagination --}}
            <div class="flex items-center justify-center w-full gap-8">
                @if ($cashInflows->hasPages())
                    <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                        <button wire:click="previousPage" {{ $cashInflows->onFirstPage() ? 'disabled' : '' }}
                            class="{{ $cashInflows->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                                <button
                                    class="h-full px-4 py-2 bg-white border-r cursor-default">{{ $element }}</button>
                            @else
                                <button wire:click="gotoPage({{ $element }})"
                                    class="h-full border-r px-4 py-2 {{ $element == $cashInflows->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                            @endif
                        @endforeach

                        <button wire:click="nextPage" {{ !$cashInflows->hasMorePages() ? 'disabled' : '' }}
                            class="{{ !$cashInflows->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                            <svg  width="7" height="13" viewBox="0 0 7 13"
                                fill="none">
                                <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </x-main.content>
    @if (!empty($transactionDetails))
        <div class="min-w-[35%] max-w-[35%] h-full px-5 py-10 overflow-auto">
            <div class="w-full">
                <h1 class="text-2xl font-bold text-rp-neutral-700">Transaction Details</h1>
                <x-card.transaction-details :transactionDetails="$transactionDetails" />
            </div>
        </div>
    @endif
    <x-loader.black-screen wire:loading.block wire:target="dateFilter,handleTableRowClick,activeBox">
        <x-loader.clock />
    </x-loader.black-screen>
</div>
