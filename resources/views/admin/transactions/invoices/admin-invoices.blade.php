<div class="flex h-full">
    <x-main.content class="overflow-y-auto grow {{ $invoiceDetails ? '!px-[18.5px]' : '' }}">
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
                    <x-button.filled-button href="{{ route('admin.transactions.invoices.create') }}" color="primary">add
                        invoice</x-button.filled-button>
                </div>
            </x-slot:actions>
        </x-main.action-header>

        <x-card.money-comparison-card title="Money Received" color="primary" :date="$dateFilter" :present="$moneyReceived" :past="$vsMoneyReceived" />

        <div class="relative grid grid-cols-5 gap-[15px] mb-8">
            <x-card.filter-card wire:click="handleFilterBoxClick('ALL')" :isActive="$activeBox === 'ALL'" label="ALL"
                :data="$allInvoicesCount" color="primary" />
            <x-card.filter-card wire:click="handleFilterBoxClick('unpaid')" :isActive="$activeBox === 'unpaid'" label="Unpaid"
                :data="$unpaidInvoicesCount" color="primary" />
            <x-card.filter-card wire:click="handleFilterBoxClick('partial')" :isActive="$activeBox === 'partial'" label="Partially Paid"
                :data="$partialInvoicesCount" color="primary" />
            <x-card.filter-card wire:click="handleFilterBoxClick('paid')" :isActive="$activeBox === 'paid'" label="Fully Paid"
                :data="$paidInvoicesCount" color="primary" />
            <x-card.filter-card wire:click="handleFilterBoxClick('overdue')" :isActive="$activeBox === 'overdue'" label="Overdue"
                :data="$overdueInvoicesCount" color="primary" />
        </div>

        <div class="space-y-8">
            <x-layout.search-container>
                <x-input.search icon_position="left" wire:model.live="searchTerm" />
            </x-layout.search-container>

            <div class="overflow-auto">
                <x-table.rounded>
                    <x-slot:table_header>
                        <x-table.rounded.th>Recipient</x-table.rounded.th>
                        <x-table.rounded.th>
                            <div class="flex flex-row items-center">
                                <span>Amount</span>
                                <button wire:click="sortTable('total_price')">
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
                            <div class="flex flex-row items-center">
                                <span>Due Date</span>
                                <button wire:click="sortTable('due_date')">
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th>Reference Number</x-table.rounded.th>
                        <x-table.rounded.th>Status</x-table.rounded.th>
                    </x-slot:table_header>
                    <x-slot:table_data>
                        <tr>
                            <td class="pt-8"></td>
                        </tr>
    
                        @foreach ($invoices as $key => $invoice)
                            @php
                                $isActiveRow = false;
                                if (!empty($invoiceDetails)) {
                                    $isActiveRow = $invoice->invoice_no === $invoiceDetails['invoice_no'];
                                }
                            @endphp
                            <x-table.rounded.row
                                class="overflow-hidden hover:bg-rp-neutral-50 cursor-pointer {{ $isActiveRow ? 'border border-primary-600 ' : '' }}"
                                wire:click="handleTableRowClick('{{ $invoice->invoice_no }}')">
                                <x-table.rounded.td>
                                    {{ get_class($invoice->recipient) == 'App\Models\User' ? $this->format_phone_number($invoice->recipient->phone_number, $invoice->recipient->phone_iso) : $invoice->recipient->name }}
                                </x-table.rounded.td>
                                <x-table.rounded.td class="font-bold text-primary-600">
                                    {{ \Number::currency($invoice->total_price, 'PHP') }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ \Carbon\Carbon::parse($invoice->created_at)->timezone('Asia/Manila')->format('F j, Y') }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ !is_null($invoice->due_date) ? \Carbon\Carbon::parse($invoice->due_date)->format('F j, Y') : '----' }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ $invoice->invoice_no }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    @switch($invoice->status)
                                        @case('paid')
                                            <x-status color="green" class="w-24">
                                                {{ ucfirst($invoice->status) }}
                                            </x-status>
                                        @break
    
                                        @case('partial')
                                            <x-status color="yellow" class="w-24">
                                                {{ ucfirst($invoice->status) }}
                                            </x-status>
                                        @break
    
                                        @case('unpaid')
                                            @if ($invoice->due_date < \Carbon\Carbon::now()->timezone('Asia/Manila')->format('Y-m-d'))
                                                <x-status color="red" class="w-24">
                                                    Overdue
                                                </x-status>
                                            @else
                                                <x-status color="neutral" class="w-24">
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
    @if (!is_null($invoiceDetails))
        <div class="min-w-[35%] max-w-[35%] h-full px-5 py-10 overflow-auto">
            <div class="w-full">
                <h1 class="text-2xl font-bold text-rp-neutral-700">Transaction Details</h1>
                <div class="px-3 py-5 space-y-4 bg-white rounded-lg mt-7">
                    <div class="flex flex-col justify-between gap-3">
                        @foreach ($invoiceDetails['items'] as $key => $item)
                            <div class="flex flex-row items-center justify-between">
                                <div>
                                    <p>{{ $item['name'] }}</p>
                                    <p class="text-sm">
                                        <span class="font-bold">{{ $item['price'] }}</span>
                                        <span>x {{ $item['quantity'] }}</span>
                                    </p>
                                </div>

                                <div>
                                    <p>{{ $item['total'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-row items-center justify-between gap-3">
                        <p>Subtotal</p>
                        <p class="font-bold">{{ $invoiceDetails['subtotal'] }}</p>
                    </div>

                    @foreach ($invoiceDetails['inclusions'] as $inclusion)
                        <div class="flex flex-row items-center justify-between gap-3">
                            <p>{{ $inclusion['name'] }}</p>
                            @if ($inclusion['deduct'])
                                <p class="text-red-700">- {{ $inclusion['amount'] }}</p>
                            @else
                                <p>{{ $inclusion['amount'] }}</p>
                            @endif
                        </div>
                    @endforeach


                    <div class="flex flex-row items-center justify-between gap-3 font-bold">
                        <p>Total:</p>
                        <p class="text-lg">{{ $invoiceDetails['total_price'] }}</p>
                    </div>
                    @isset($invoiceDetails['minimum_partial'])
                        <div class="flex flex-row items-center justify-between gap-3">
                            <p>Minimum partial payment allowed:</p>
                            <p>{{ $invoiceDetails['minimum_partial'] }}</p>
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    @endif
    <x-loader.black-screen wire:loading.block wire:target="dateFilter,sortTable,previousPage,nextPage,gotoPage,handleFilterBoxClick,handleTableRowClick" />

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
</div>
