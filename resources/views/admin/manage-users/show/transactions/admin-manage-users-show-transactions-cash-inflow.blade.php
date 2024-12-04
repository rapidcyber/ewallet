<x-main.content class="!px-16 !py-10" x-data="{
    transactionDetailsModal: {
        visible: false,
    }
}" x-init="function() { 
    Livewire.on('showTransactionDetails', function () {
        transactionDetailsModal.visible = true;
    });
}">
 
    <livewire:components.layout.admin.user-details-header :user="$user" />

    <div class="mt-8">
        <x-card.display-balance title="User Balance" :balance="$user->latest_balance?->amount ?? 0.00" color="primary" class="mb-6"/>

        <div class="flex">
            {{-- 1st Column: Left Sidebar --}}
            <x-layout.admin.user-details.transactions.left-sidebar :user="$user" class="w-60"/>

            {{-- 2nd Column: Table --}}
            <div class="w-[calc(100%-240px)] pl-4">
                <div class="flex items-center gap-2 mb-5">
                    <p>Sort by:</p>
                    <x-dropdown.select-date wire:model.live='dateFilter' wire:loading.attr="disabled" wire:loading.class='cursor-not-allowed'>
                        <x-dropdown.select-date.option value="none">All transactions</x-dropdown.select-date.option>
                        <x-dropdown.select-date.option value="past_year">Past Year</x-dropdown.select-date.option>
                        <x-dropdown.select-date.option value="past_6_months">Past 6 Months</x-dropdown.select-date.option>
                        <x-dropdown.select-date.option value="past_30_days">Past 30 Days</x-dropdown.select-date.option>
                        <x-dropdown.select-date.option value="past_week">Past Week</x-dropdown.select-date.option>
                        <x-dropdown.select-date.option value="past_24_hours">Past 24 Hours</x-dropdown.select-date.option>
                    </x-dropdown.select-date>
                </div>

                {{-- Filter Cards --}}
                <div class="grid grid-cols-4 gap-3 mb-5">
                    <x-card.filter-card wire:click="$set('activeBox', 'all')" :isActive="$activeBox === 'all'" label="All" :data="$this->count_all" color="primary" />
                    <x-card.filter-card wire:click="$set('activeBox', 'money-transfers')" :isActive="$activeBox === 'money-transfers'" label="Money Transfers" :data="$this->count_money_transfers" color="primary" />
                    <x-card.filter-card wire:click="$set('activeBox', 'cash_in')" :isActive="$activeBox === 'cash_in'" label="Cash In" :data="$this->count_cash_in" color="primary" />
                    <x-card.filter-card wire:click="$set('activeBox', 'salary')" :isActive="$activeBox === 'salary'" label="Salary" :data="$this->count_salary" color="primary" />
                </div>

                {{-- Search --}}
                <x-layout.search-container class="mb-5">
                    <x-input.search wire:model.live='searchTerm' icon_position="left" />
                </x-layout.search-container>

                {{-- Table --}}
                <div class="overflow-auto">
                    <x-table.rounded>
                        <x-slot:table_header>
                            <x-table.rounded.th>Transaction type</x-table.rounded.th>
                            <x-table.rounded.th>Sender</x-table.rounded.th>
                            <x-table.rounded.th>
                                <div class="flex flex-row items-center">
                                    <span>Amount</span>
                                    <button wire:click="sortTable('amount')">
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
                            <x-table.rounded.th>
                                Reference Number
                            </x-table.rounded.th>
                            <x-table.rounded.th>
                                Status
                            </x-table.rounded.th>
                        </x-slot:table_header>
                        <x-slot:table_data>
                            <tr><td class="pt-8"></td></tr>

                            @foreach ($cash_inflows as $key => $cash_inflow)
                                @php 
                                    $isActiveRow = false;
                                    if(!is_null($transactionDetails)) {
                                        $isActiveRow = $cash_inflow->txn_no === $transactionDetails['txn_no'];   
                                    }
                                @endphp
                                <x-table.rounded.row class="{{ $isActiveRow ? 'border border-primary-600' : 'hover:bg-rp-neutral-50 cursor-pointer' }}"
                                    wire:click="handleTableRowClick('{{ $cash_inflow->txn_no }}')" wire:key="cash_inflow-{{ $key }}">
                                    <x-table.rounded.td>{{ $cash_inflow->type->name }}</x-table.rounded.td>
                                    @if (get_class($cash_inflow->sender) == 'App\Models\User')
                                        <x-table.rounded.td>{{ $this->format_phone_number($cash_inflow->sender->phone_number, $cash_inflow->sender->phone_iso) }}</x-table.rounded.td>
                                    @else
                                        <x-table.rounded.td>{{ $cash_inflow->sender->name }}</x-table.rounded.td>
                                    @endif
                                    <x-table.rounded.td class="text-primary-600 font-bold">₱{{ $cash_inflow->amount }}</x-table.rounded.td>
                                    <x-table.rounded.td>{{ $cash_inflow->created_at->timezone('Asia/Manila')->format('M d, Y') }}</x-table.rounded.td>
                                    <x-table.rounded.td>{{ $cash_inflow->ref_no }}</x-table.rounded.td>
                                    <x-table.rounded.td>
                                        @switch($cash_inflow->status->name)
                                            @case('Pending')
                                                <x-status color="primary" class="w-36">Pending</x-status>
                                                @break
                                            @case('Successful')
                                                <x-status color="green" class="w-36">Successful</x-status>
                                                @break
                                            @case('Failed')
                                                <x-status color="red" class="w-36">Failed</x-status>
                                                @break
                                            @case('Refunded')
                                                <x-status color="primary" class="w-36">Refunded</x-status>
                                                @break
                                            @default
                                                -
                                        @endswitch
                                    </x-table.rounded.td>
                                </x-table.rounded.row> 
                            @endforeach                                                   
                        </x-slot:table_data>
                    </x-table.rounded>
                </div>

                {{-- Pagination --}}
                <div class="w-full flex items-end justify-end gap-8">
                    @if ($cash_inflows->hasPages())
                        <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                            <button wire:click="previousPage" {{ $cash_inflows->onFirstPage() ? 'disabled' : '' }}
                                class="{{ $cash_inflows->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                                        class="h-full bg-white border-r px-4 py-2 {{ $element == $cash_inflows->currentPage() ? 'cursor-default' : 'cursor-pointer' }}">{{ $element }}</button>
                                @endif
                            @endforeach

                            <button wire:click="nextPage" {{ !$cash_inflows->hasMorePages() ? 'disabled' : '' }}
                                class="{{ !$cash_inflows->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
        </div>
    </div>

    {{-- Transaction Details Modal --}}
    <x-modal x-model="transactionDetailsModal.visible">
        @if (!is_null($transactionDetails))
            <x-modal.transaction-details :transactionDetails="$transactionDetails" titleColor="primary" />
        @endif
    </x-modal>

    <x-loader.black-screen wire:loading wire:target="dateFilter,activeBox,handleTableRowClick,sortTable" class="z-10"/>
</x-main.content>
