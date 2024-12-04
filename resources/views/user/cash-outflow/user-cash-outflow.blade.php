<div class="flex h-full">
    <x-main.content class="overflow-y-auto grow {{ $transactionDetails ? '!px-[18.5px]' : '' }}">
        <x-main.action-header>
            <x-slot:title>Cash Outflow</x-slot:title>
            <x-slot:actions>
                <div class="flex flex-row items-center gap-3">
                    <div class="flex flex-row items-center gap-2">
                        <p>Sort by:</p>
                        <x-dropdown.select-date wire:model.live="dateFilter" class="h-[32px]">
                            <x-dropdown.select-date.option value="past_year">Past Year</x-dropdown.select-date.option>
                            <x-dropdown.select-date.option value="past_30_days">Past 30 days</x-dropdown.select-date.option>
                            <x-dropdown.select-date.option value="past_week">Past Week</x-dropdown.select-date.option>
                            <x-dropdown.select-date.option value="day">Day</x-dropdown.select-date.option>
                        </x-dropdown.select-date>
                    </div>
                    <x-button.filled-button href="{{ route('user.cash-outflow.create') }}">add outflow transaction</x-button.filled-button>
                </div>
            </x-slot:actions>
        </x-main.action-header>

        <x-card.money-comparison-card title="Money Sent" :date="$dateFilter" :present="$moneySent" :past="$vsMoneySent" />


        <div class="relative grid grid-cols-5 gap-[15px] mb-8">
            <x-card.filter-card wire:click="handleFilterBoxClick('all')" :isActive="$activeBox === 'all'" label="All" :data="$allCashOutflow" color="red" />
            <x-card.filter-card wire:click="handleFilterBoxClick('transfer')" :isActive="$activeBox === 'transfer'" label="Money Transfers" :data="$moneyTransfers" color="red" />
            <x-card.filter-card wire:click="handleFilterBoxClick('order_payment')" :isActive="$activeBox === 'order_payment'" label="Order Payments" :data="$orderPayments" color="red" />
            <x-card.filter-card wire:click="handleFilterBoxClick('cash_out')" :isActive="$activeBox === 'cash_out'" label="Cash Out" :data="$cashOutflowCount" color="red" />
            <a  href="{{ route('user.bills') }}"
                class="{{ $activeBox === 'bill_payment' ? 'text-rp-red-500 bg-rp-red-100 border-rp-red-500 hover:bg-rp-red-100' : 'text-rp-neutral-600 bg-white border-transparent hover:bg-rp-red-100' }} cursor-pointer flex flex-col justify-center px-4 py-4 rounded-xl border-2 transition duration-300">
                <span class="text-base">Bills</span>
                <span class="text-3.5xl font-bold">{{ $bills }}</span>
            </a>
        </div>

        <div class="space-y-8">
            <x-layout.search-container>
                <x-input.search wire:model.live='searchTerm' icon_position="left" />
            </x-layout.search-container>

            <div class="overflow-auto">
                <x-table.rounded>
                    <x-slot:table_header>
                        <x-table.rounded.th class="{{ $transactionDetails ? '' : 'w-52 min-w-52 max-w-52' }}">Transaction type</x-table.rounded.th>
                        <x-table.rounded.th class="{{ $transactionDetails ? '' : 'w-72 min-w-72 max-w-72' }}">Recipient</x-table.rounded.th>
                        <x-table.rounded.th class="{{ $transactionDetails ? '' : 'w-48 min-w-48 max-w-48' }}">
                            <div class="flex flex-row items-center">
                                <span>Amount</span>
                                <button wire:click="sortTable('total_amount')">
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th>
                            <div class="flex flex-row items-center">
                                <span>Date Created</span>
                                <button wire:click="sortTable('created_at')">
                                    <x-icon.sort />
                                </button>
                            </div>   
                        </x-table.rounded.th>
                        <x-table.rounded.th>Transaction Number</x-table.rounded.th>
                        <x-table.rounded.th>Status</x-table.rounded.th>
                    </x-slot:table_header>
                    <x-slot:table_data>
                        <tr><td class="pt-8"></td></tr>
                        @foreach ($cashOutflows as $key => $cashOut)
                            @php 
                                $isActiveRow = false;
                                if (!is_null($transactionDetails)) {
                                    $isActiveRow = $cashOut->txn_no === $transactionDetails['txn_no'];
                                }
                            @endphp
                            <x-table.rounded.row class="overflow-hidden cursor-pointer {{ $isActiveRow ? 'border border-rp-red-500 ' : 'hover:bg-rp-neutral-50' }}" wire:click="handleTableRowClick('{{ $cashOut->txn_no }}')">
                                <x-table.rounded.td>
                                    {{ ucwords(str_replace('_', ' ', $cashOut->type->name)) }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ get_class($cashOut->recipient) == 'App\Models\User' ? $this->format_phone_number($cashOut->recipient->phone_number, $cashOut->recipient->phone_iso) : $cashOut->recipient->name }}
                                </x-table.rounded.td>
                                <x-table.rounded.td class="text-rp-red-500 font-bold">
                                    {{ \Number::currency($cashOut->total_amount, $cashOut->currency) }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ \Carbon\Carbon::parse($cashOut->created_at)->timezone('Asia/Manila')->format('F j, Y') }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{$cashOut->txn_no}}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    @switch($cashOut->status->name)
                                        @case('Successful')
                                            <x-status color="green" class="w-28">
                                                {{ $cashOut->status->name }}
                                            </x-status>
                                        @break
                                        @case('Pending')
                                            <x-status color="neutral" class="w-28">
                                                {{ $cashOut->status->name }}
                                            </x-status>
                                        @break
                                        @case('Failed')
                                            <x-status color="red" class="w-28">
                                                {{ $cashOut->status->name }}
                                            </x-status>
                                        @break
                                        @case('Refunded')
                                            <x-status color="red" class="w-28">
                                                {{ $cashOut->status->name }}
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
            <div class="w-full flex items-center justify-center gap-8">
                @if ($cashOutflows->hasPages())
                    <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                        <button wire:click="previousPage" {{ $cashOutflows->onFirstPage() ? 'disabled' : '' }}
                            class="{{ $cashOutflows->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                                    class="h-full  border-r px-4 py-2 {{ $element == $cashOutflows->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                            @endif
                        @endforeach

                        <button wire:click="nextPage" {{ !$cashOutflows->hasMorePages() ? 'disabled' : '' }}
                            class="{{ !$cashOutflows->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
    @if (!is_null($transactionDetails))
        <div class="min-w-[35%] max-w-[35%] h-full px-5 py-10 overflow-auto">
            <div class="w-full">
                <h1 class="text-2xl text-rp-neutral-700 font-bold">Transaction Details</h1>
                <x-card.transaction-details :transactionDetails="$transactionDetails" />
            </div>
        </div>
    @endif
    <x-loader.black-screen wire:loading wire:target="dateFilter,handleFilterBoxClick,handleTableRowClick">
        <x-loader.clock />
    </x-loader.black-screen>
</div>