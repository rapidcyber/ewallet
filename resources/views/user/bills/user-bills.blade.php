<div class="h-full flex w-full {{ $billDetails ? '!px-[18.5px]' : '' }}">
    <x-main.content class="overflow-y-auto grow">
        <x-main.action-header>
            <x-slot:title>Bills</x-slot:title>
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
                <x-button.filled-button href="{{ route('user.cash-outflow.create', ['type' => 'bill-payment']) }}">pay bill</x-button.filled-button>
            </x-slot:actions>
        </x-main.action-header>

        <x-card.money-comparison-card title="Money Sent" :date="$dateFilter" :present="$moneySent" :past="$vsMoneySent" />

        <div class="grid grid-cols-4 gap-[15px] mb-8">
            <x-card.filter-card wire:click="handleFilterBoxClick('all')" :isActive="$activeBox === 'all'" label="All" :data="$allBillsCount" />
            <x-card.filter-card wire:click="handleFilterBoxClick('paid')" :isActive="$activeBox === 'paid'" label="Paid" :data="$paidBillsCount" />
            <x-card.filter-card wire:click="handleFilterBoxClick('unpaid')" :isActive="$activeBox === 'unpaid'" label="Unpaid" :data="$unpaidBillsCount" />
            <x-card.filter-card wire:click="handleFilterBoxClick('overdue')" :isActive="$activeBox === 'overdue'" label="Overdue" :data="$overdueBillsCount" />
        </div>

        <div class="space-y-8">
            <x-layout.search-container>
                <x-input.search wire:model.live.debound.300ms='searchTerm' icon_position="left" />
            </x-layout.search-container>
            <div>
                <x-table.rounded>
                    <x-slot:table_header>
                        <x-table.rounded.th>Biller</x-table.rounded.th>
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
                            <div class="flex flex-row items-center">
                                <span>Due Date</span>
                                <button wire:click="sortTable('due_date')">
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th>Status</x-table.rounded.th>
                    </x-slot:table_header>
                    <x-slot:table_data>
                        <tr><td class="pt-8"></td></tr>
                        @foreach ($bills as $key => $bill)
                            @php
                                $isActiveRow = false;
                                if (!is_null($billDetails)) {
                                    $isActiveRow = $bill->id === $billDetails->id;
                                }
                            @endphp
                            <x-table.rounded.row class="overflow-hidden cursor-pointer {{ $isActiveRow ? 'border border-rp-red-500 ' : 'hover:bg-rp-neutral-50' }}" wire:click="handleTableRowClick({{ $bill->id }})">
                                <x-table.rounded.td>
                                    {{ $bill->biller_name }}
                                </x-table.rounded.td>
                                <x-table.rounded.td class="text-rp-red-500 font-bold">
                                    ₱{{ number_format($bill->amount, 2) }}
                                </x-table.rounded.td>
                                <x-table.rounded.td >
                                    {{ \Carbon\Carbon::parse($bill->created_at)->format('F j, Y') }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('F j, Y') : '-' }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    @switch($bill->status)
                                        @case('paid')
                                            <x-status class="w-24" color="green">Paid</x-status>
                                            @break
                                        @case('unpaid')
                                            <x-status class="w-24" color="neutral">Unpaid</x-status>
                                            @break
                                        @case('overdue')
                                            <x-status class="w-24" color="red">Overdue</x-status>
                                            @break
                                        @default    
                                    @endswitch
                                </x-table.rounded.td>
                            </x-table.rounded.row>
                        @endforeach
                      
                    </x-slot:table_data>
                </x-table.rounded>
                {{-- Pagination --}}
                <div class="w-full flex items-center justify-center gap-8">
                    @if ($bills->hasPages())
                        <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                            <button wire:click="previousPage" {{ $bills->onFirstPage() ? 'disabled' : '' }}
                                class="{{ $bills->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                                        class="h-full border-r px-4 py-2 {{ $element == $bills->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                                @endif
                            @endforeach

                            <button wire:click="nextPage" {{ !$bills->hasMorePages() ? 'disabled' : '' }}
                                class="{{ !$bills->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
    </x-main.content>
    @if ($right_sidebar_content_type === 'bill_details' && !is_null($billDetails))
        <x-layout.sidebar-detail>
            <h1 class="text-xl font-bold text-rp-neutral-700">Bill Details</h1>
            <div class="bg-white px-5 py-6 mt-7 rounded-lg w-full text-rp-gray-800">
                <div class="text-center">
                    <p>Paid to:</p>
                    <div class="leading-none">
                        <h1 class="text-xl font-bold">{{$billDetails->biller_name}}</h1>
                        <p class="text-xs 2xl:textsm">Merchant ID: {{$billDetails->biller_code}}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 my-7">
                    <div class="flex flex-row gap-2 justify-between items-center">
                        <p class="w-1/2">Amount</p>
                        <p class="w-1/2 text-right">₱{{ number_format($billDetails->amount, 2) }}</p>
                    </div>
                    <div class="flex flex-row gap-2 justify-between items-center">
                        <p class="w-1/2">Total</p>
                        <p class="w-1/2 text-right font-bold text-lg">₱{{ number_format($billDetails->amount, 2) }}</p>
                    </div>
                </div>
                <div class="text-center text-sm mt-6">
                    <p>Reference No. {{$billDetails->ref_no}}</p>
                    <p>{{ \Carbon\Carbon::parse($billDetails->created_at)->timezone('Asia/Manila')->format('M d, Y - g:i A') }}</p>
                </div>
            </div>
        </x-layout.sidebar-detail>
    @endif
    {{-- black screen loading indication --}}
    <x-loader.black-screen wire:loading wire:target="dateFilter,handleFilterBoxClick">
        <x-loader.clock />
    </x-loader.black-screen>
</div>