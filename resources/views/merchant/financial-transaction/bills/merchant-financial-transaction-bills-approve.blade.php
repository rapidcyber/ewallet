<div class="flex w-full h-full">
    <x-main.content class="flex-1 overflow-auto {{ !empty($billDetails) ? '!px-[18.5px]' : '' }}">
        <x-main.action-header>
            <x-slot:title>Bill Requests</x-slot:title>
        </x-main.action-header>

        <div class="grid grid-cols-3 gap-[15px] mb-8">
            <x-card.filter-card wire:click="$set('filter', 'pending')" :isActive="$filter === 'pending'" label="Pending"
                :data="$this->get_pending_count" />
            <x-card.filter-card wire:click="$set('filter', 'approved')" :isActive="$filter === 'approved'" label="Approved"
                :data="$this->get_approved_count" />
            <x-card.filter-card wire:click="$set('filter', 'rejected')" :isActive="$filter === 'rejected'" label="Rejected"
                :data="$this->get_rejected_count" />
        </div>
        <div class="space-y-8">
            <div>
                <x-table.rounded class="table-fixed">
                    <x-slot:table_header>
                        <x-table.rounded.th>Biller</x-table.rounded.th>
                        <x-table.rounded.th>
                            <div class="flex flex-row items-center">
                                <span>Amount</span>
                                <button>
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th>
                            <div class="flex flex-row items-center">
                                <span>Service Charge</span>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th>
                            <div class="flex flex-row items-center">
                                <span>Date Created</span>
                                <button>
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        @if ($filter === 'pending')
                            <x-table.rounded.th>
                                <div class="flex flex-row items-center">
                                    <span>Actions</span>
                                </div>
                            </x-table.rounded.th>
                        @endif
                    </x-slot:table_header>
                    <x-slot:table_data>
                        <tr>
                            <td class="pt-8"></td>
                        </tr>
                        @foreach ($billing_requests as $key => $billing)
                            @php
                                $isActiveRow = false;
                                if (!is_null($billDetails)) {
                                    $isActiveRow = $billing->id === $billDetails['id'];
                                }
                            @endphp
                            <x-table.rounded.row
                                class="overflow-hidden hover:bg-rp-neutral-50 cursor-pointer {{ $isActiveRow ? 'border border-rp-red-500 ' : '' }}"
                                @click="$wire.show_request({{ $billing->id }})">
                                <x-table.rounded.td>
                                    {{ $billing->name }}
                                </x-table.rounded.td>
                                <x-table.rounded.td class="font-bold text-rp-red-500">
                                    {{ \Number::currency($billing->amount, 'PHP') }}
                                </x-table.rounded.td>
                                <x-table.rounded.td class="font-bold text-rp-red-500">
                                    {{ $billing->service_charge > 0 ? \Number::currency($billing->service_charge, 'PHP') : 'Free' }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ \Carbon\Carbon::parse($billing->created_at)->timezone('Asia/Manila')->format('F j, Y - g:i A') }}
                                </x-table.rounded.td>
                                @if ($filter === 'pending')
                                    <x-table.rounded.td>
                                        <div class="flex flex-row items-center gap-4">
                                            <x-button.filled-button @click.stop="$wire.approveModal=true;$wire.action_set('{{ $billing->id }}')" size="md">approve</x-button.filled-button>
                                            <x-button.filled-button @click.stop="$wire.rejectModal=true;$wire.action_set('{{ $billing->id }}')" size="md">deny</x-button.filled-button>
                                        </div>
                                    </x-table.rounded.td>
                                @endif
                            </x-table.rounded.row>
                        @endforeach
                    </x-slot:table_data>
                </x-table.rounded>
            </div>
        </div>
        {{-- Pagination --}}
        @if ($billing_requests->hasPages())
            <div class="flex items-center justify-center w-full gap-8">
                <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                    <button wire:click="previousPage" {{ $billing_requests->onFirstPage() ? 'disabled' : '' }}
                        class="{{ $billing_requests->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                                class="h-full border-r px-4 py-2 {{ $element == $billing_requests->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                        @endif
                    @endforeach

                    <button wire:click="nextPage" {{ !$billing_requests->hasMorePages() ? 'disabled' : '' }}
                        class="{{ !$billing_requests->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                        <svg  width="7" height="13" viewBox="0 0 7 13"
                            fill="none">
                            <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- Confirmation Modal --}}
        <x-modal x-model="$wire.approveModal">
            <x-modal.confirmation-modal>
                <x-slot:title>Approve Bill?</x-slot:title>
                <x-slot:message>
                    This action will approve the bill and directly send the payment to the biller.
                </x-slot:message>
                <x-slot:action_buttons>
                    <x-button.outline-button class="flex-1"
                        @click="$wire.approveModal=false;$wire.reset_modal">go back</x-button.outline-button>
                    <x-button.filled-button class="flex-1"
                        @click="$wire.approve_bill_request()">proceed</x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.confirmation-modal>
        </x-modal>

        <x-modal x-model="$wire.rejectModal">
            <x-modal.confirmation-modal>
                <x-slot:title>Reject Bill?</x-slot:title>
                <x-slot:message>
                    This action will reject the bill.
                </x-slot:message>
                <x-slot:action_buttons>
                    <x-button.outline-button class="flex-1"
                        @click="$wire.rejectModal=false;$wire.reset_modal;">go back</x-button.outline-button>
                    <x-button.filled-button class="flex-1"
                        @click="$wire.handleConfirmDeleteEmployee()">proceed</x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.confirmation-modal>
        </x-modal>
    </x-main.content>

    @if (!is_null($billDetails))
        <x-layout.sidebar-detail>
            <h1 class="text-xl font-bold text-rp-neutral-700">Bill Details</h1>
            <div class="w-full px-5 py-6 bg-white rounded-lg mt-7 text-rp-gray-800">
                <div class="text-center">
                    <p>Biller:</p>

                    <div class="leading-none">
                        <h1 class="text-xl font-bold">{{ $billDetails['name'] }}</h1>
                        <p class="text-xs 2xl:text-sm">{{ $billDetails['category'] }}</p>
                        <p class="text-xs 2xl:text-sm">{{ $billDetails['remarks'] }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 my-7">
                    @php
                        $billInfo = $billDetails['infos'];
                    @endphp

                    @foreach ($billInfo as $key => $info)
                        <div class="flex flex-row items-center justify-between gap-2">
                            <p class="w-1/2">{{ $key }}</p>
                            <p class="w-1/2 text-right">{{ $info }}</p>
                        </div>
                    @endforeach

                    <div class="flex flex-row items-center justify-between gap-2">
                        <p class="w-1/2">Amount</p>
                        <p class="w-1/2 text-right">{{ \Number::currency($billDetails['amount'], 'PHP') }}</p>
                    </div>
                    <div class="flex flex-row items-center justify-between gap-2">
                        <p class="w-1/2">Service Charge</p>
                        <p class="w-1/2 text-right">{{ \Number::currency($billDetails['service_charge'], 'PHP') }}</p>
                    </div>
                    <div class="flex flex-row items-center justify-between gap-2">
                        <p class="w-1/2">Total</p>
                        <p class="w-1/2 text-lg font-bold text-right">{{ \Number::currency($billDetails['total'], 'PHP') }}</p>
                    </div>
                </div>
            </div>
        </x-layout.sidebar-detail>
    @endif

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

    <x-loader.black-screen wire:loading wire:target="show_request,approve_bill_request,filter" class="z-50">
        <x.loader.clock />
    </x-loader.black-screen>

</div>
