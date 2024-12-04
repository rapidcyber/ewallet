<div class="flex h-full">
    <x-main.content class="overflow-y-auto grow {{ $invoice_data ? '!px-[18.5px]' : '' }}">
        <x-main.action-header>
            <x-slot:title>Invoices</x-slot:title>
            <x-slot:actions>
                <div class="flex flex-row items-center gap-3">
                    <div class="flex flex-row items-center gap-2">
                        <p>Sort by:</p>
                        <x-dropdown.select-date wire:model.live="dateFilter" class="h-[32px]">
                            <x-dropdown.select-date.option value="past_year">Past Year</x-dropdown.select-date.option>
                            <x-dropdown.select-date.option value="past_30_days">Past 30
                                days</x-dropdown.select-date.option>
                            <x-dropdown.select-date.option value="past_week">Past Week</x-dropdown.select-date.option>
                            <x-dropdown.select-date.option value="day">Day</x-dropdown.select-date.option>
                        </x-dropdown.select-date>
                    </div>
                    <div wire:ignore>
                        @if ($color == 'primary')
                            <x-button.filled-button color="primary" href="{{ route('admin.transactions.invoices.create') }}">
                                add invoice
                            </x-button.filled-button>
                        @else
                            @can('merchant-invoices', [$merchant, 'create'])
                                <x-button.filled-button
                                    href="{{ route('merchant.financial-transactions.invoices.create', ['merchant' => $merchant->account_number]) }}">add
                                    invoice</x-button.filled-button>
                            @endcan
                        @endif
                    </div>
                </div>
            </x-slot:actions>
        </x-main.action-header>

        <x-card.money-comparison-card title="Money Received" :date="$dateFilter" :present="$moneyReceived" :past="$vsMoneyReceived" :color="$color" />


        <div class="relative grid {{ $color === 'primary' ? 'grid-cols-5' : 'grid-cols-4' }} gap-[15px] mb-8">
            <x-card.filter-card wire:click="handleFilterBoxClick('')" :isActive="$activeBox === ''" label="ALL"
                :data="$allInvoicesCount" :color="$color" />
            <x-card.filter-card wire:click="handleFilterBoxClick('unpaid')" :isActive="$activeBox === 'unpaid'" label="Unpaid"
                :data="$unpaidCount" :color="$color" />
            <x-card.filter-card wire:click="handleFilterBoxClick('partial')" :isActive="$activeBox === 'partial'" label="Partially Paid"
                :data="$partiallyPaidCount" :color="$color" />
            <x-card.filter-card wire:click="handleFilterBoxClick('paid')" :isActive="$activeBox === 'paid'" label="Fully Paid"
                :data="$fullyPaidCount" :color="$color" />
            @if ($color === 'primary')
                <x-card.filter-card wire:click="handleFilterBoxClick('overdue')" :isActive="$activeBox === 'overdue'" label="Overdue"
                    :data="$overdueCount" :color="$color" />
            @endif
        </div>

        <div class="space-y-8">
            <x-layout.search-container>
                <x-input.search icon_position="left" wire:model.live="searchTerm" />
            </x-layout.search-container>

            <div class="overflow-auto">
                <x-table.rounded>
                    <x-slot:table_header>
                        <x-table.rounded.th class="{{ $invoice_data ? '' : 'w-72 min-w-72 max-w-72' }}">Recipient</x-table.rounded.th>
                        <x-table.rounded.th class="{{ $invoice_data ? '' : 'w-48 min-w-48 max-w-48' }}">
                            <div class="flex flex-row items-center">
                                <span>Amount</span>
                                <button wire:click="sortTable('final_price')">
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th class="{{ $invoice_data ? '' : 'w-56 min-w-56 max-w-56' }}">
                            <div class="flex flex-row items-center">
                                <span>Date Created</span>
                                <button wire:click="sortTable('created_at')">
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th class="{{ $invoice_data ? '' : 'w-56 min-w-56 max-w-56' }}">
                            <div class="flex flex-row items-center">
                                <span>Due Date</span>
                                <button wire:click="sortTable('due_date')">
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th class="{{ $invoice_data ? '' : 'w-72 min-w-72 max-w-72' }}">Reference Number</x-table.rounded.th>
                        <x-table.rounded.th>Status</x-table.rounded.th>
                    </x-slot:table_header>
                    <x-slot:table_data>
                        <tr>
                            <td class="pt-8"></td>
                        </tr>
                        @foreach ($invoices as $key => $invoice)
                            @php
                                $isActiveRow = false;
                                if (!is_null($invoice_data)) {
                                    $isActiveRow = $invoice->invoice_no === $invoice_data['invoice_no'];
                                }
                            @endphp
                            <x-table.rounded.row
                                class="overflow-hidden cursor-pointer {{ $isActiveRow ? ($color === 'primary' ? 'border border-primary-600 ' : 'border border-rp-red-500 ') : 'hover:bg-rp-neutral-50' }}"
                                wire:click="show_invoice('{{ $invoice->invoice_no }}')">
                                <x-table.rounded.td>
                                    {{ $invoice->recipient }}
                                </x-table.rounded.td>
                                <x-table.rounded.td class="font-bold {{ $color === 'primary' ? '!text-primary-600' : '!text-rp-red-500' }}">
                                    {{ \Number::currency($invoice->final_price, 'PHP') }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ \Carbon\Carbon::parse($invoice->created_at)->timezone('Asia/Manila')->format('F j, Y') }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ !is_null($invoice->due_date) ? \Carbon\Carbon::parse($invoice->due_date)->timezone('Asia/Manila')->format('F j, Y') : '----' }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ $invoice->invoice_no }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    @switch($invoice->status)
                                        @case('paid')
                                            <x-status color="green" class="w-20">
                                                {{ ucfirst($invoice->status) }}
                                            </x-status>
                                        @break
    
                                        @case('partial')
                                            <x-status color="yellow" class="w-20">
                                                {{ ucfirst($invoice->status) }}
                                            </x-status>
                                        @break
    
                                        @case('unpaid')
                                            @if (\Carbon\Carbon::parse($invoice->due_date)->timezone('Asia/Manila') < \Carbon\Carbon::now('Asia/Manila'))
                                                <x-status color="red" class="w-20">
                                                    Overdue
                                                </x-status>
                                            @else
                                                <x-status color="neutral" class="w-20">
                                                    {{ ucfirst($invoice->status) }}
                                                </x-status>
                                            @endif
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
                @if ($invoices->hasPages())
                    <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                        <button wire:click="previousPage" {{ $invoices->onFirstPage() ? 'disabled' : '' }}
                            class="{{ $invoices->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                                    class="h-full border-r px-4 py-2 {{ $element == $invoices->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                            @endif
                        @endforeach

                        <button wire:click="nextPage" {{ !$invoices->hasMorePages() ? 'disabled' : '' }}
                            class="{{ !$invoices->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
    @if (!is_null($invoice_data))
        <div class="min-w-[35%] max-w-[35%] h-full px-5 py-10 overflow-auto">
            <div class="w-full">
                <h1 class="text-2xl font-bold text-rp-neutral-700">Transaction Details</h1>
                <div class="px-3 py-5 space-y-4 bg-white rounded-lg mt-7">
                    <div class="flex flex-col justify-between gap-3">
                        @foreach ($invoice_data['items'] as $item)
                            <div class="flex flex-row items-center justify-between">
                                <div>
                                    <p>{{ $item['name'] }}</p>
                                    <p class="text-sm">
                                        <span class="font-bold">{{ \Number::currency($item['price'], 'PHP') }}</span>
                                        <span>x {{ $item['quantity'] }}</span>
                                    </p>
                                </div>

                                <div>
                                    <p>{{ \Number::currency($item['total'], 'PHP') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-row items-center justify-between gap-3">
                        <p>Subtotal</p>
                        <p class="font-bold">{{ \Number::currency($invoice_data['sub_total'], 'PHP') }}</p>
                    </div>

                    @foreach ($invoice_data['inclusions'] as $inclusion)
                        <div class="flex flex-row items-center justify-between gap-3">
                            <p>{{ $inclusion['name'] }}</p>
                            <p>{{ \Number::currency($inclusion['amount'], 'PHP') }}</p>
                        </div>
                    @endforeach


                    <div class="flex flex-row items-center justify-between gap-3 font-bold">
                        <p>Total:</p>
                        <p class="text-lg">{{ \Number::currency($invoice_data['total'], 'PHP') }}</p>
                    </div>

                    <div class="flex flex-row items-center justify-between gap-3">
                        <p>Minimum partial payment allowed:</p>
                        <p>{{ \Number::currency($invoice_data['minimum_partial'], 'PHP') }}</p>
                    </div>


                </div>
                @if (!empty($invoice_data['logs']))
                    <h1 class="text-lg font-bold text-rp-neutral-700 mt-7">Payment History</h1>
                    <div class="flex flex-col justify-between gap-2">
                        @foreach ($invoice_data['logs'] as $log)
                            <div class="px-3 py-5 bg-white rounded-lg">
                                <p class="italic">{{ $log['message'] }}</p>
                                <p class="text-xs text-right">{{ $log['created_at'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    @endif
    <x-loader.black-screen wire:loading wire:target="dateFilter,previousPage,nextPage,gotoPage,show_invoice,handleFilterBoxClick" />
</div>
