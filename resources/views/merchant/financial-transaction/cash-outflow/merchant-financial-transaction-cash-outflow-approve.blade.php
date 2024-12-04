<div class="flex h-full">
    <x-main.content class="overflow-y-auto grow {{ $transactionDetails ? '!px-[18.5px]' : '' }}">
        <x-main.action-header>
            <x-slot:title>Cash Outflow Requests</x-slot:title>
        </x-main.action-header>

        <div class="flex flex-col gap-5">
            <x-card.display-balance :balance="$this->available_balance" />
    
            <div class="relative grid grid-cols-3 gap-[15px]">
                <x-card.filter-card wire:click="$set('filter', 'pending')" :isActive="$filter === 'pending'" label="Pending"
                    :data="$this->get_pending_count" />
                <x-card.filter-card wire:click="$set('filter', 'approved')" :isActive="$filter === 'approved'" label="Approved"
                    :data="$this->get_approved_count" />
                <x-card.filter-card wire:click="$set('filter', 'rejected')" :isActive="$filter === 'rejected'" label="Rejected"
                    :data="$this->get_rejected_count" />
            </div>
    
            {{-- <x-layout.search-container>
                <x-input.search icon_position="left" wire:model.live="searchTerm" />
            </x-layout.search-container> --}}
            
            <div class="overflow-auto">
                <x-table.rounded>
                    <x-slot:table_header>
                        <x-table.rounded.th>Transaction type</x-table.rounded.th>
                        <x-table.rounded.th>Recipient</x-table.rounded.th>
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
                                <span>Date Created</span>
                                <button>
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        @switch($filter)
                            @case('approved')
                                <x-table.rounded.th>Approved at</x-table.rounded.th>
                                <x-table.rounded.th>Approved by</x-table.rounded.th>
                                @break
                            @case('rejected')
                                <x-table.rounded.th>Rejected at</x-table.rounded.th>
                                <x-table.rounded.th>Rejected by</x-table.rounded.th>
                                @break
                        @endswitch
                    </x-slot:table_header>
                    <x-slot:table_data>
                        <tr>
                            <td class="pt-8"></td>
                        </tr>
                        @foreach ($transaction_requests as $key => $cashOut)
                            @php
                                $isActiveRow = false;
                                if (!empty($transactionDetails)) {
                                    $isActiveRow = $cashOut->id === $transactionDetails['id'];
                                }
                            @endphp
                            <x-table.rounded.row
                                class="overflow-hidden cursor-pointer {{ $isActiveRow ? 'border border-rp-red-500 ' : 'hover:bg-rp-neutral-50' }}"
                                @click="$wire.show_transaction_details({{ $cashOut->id }})">
                                <x-table.rounded.td>
                                    {{ ucwords(str_replace('_', ' ', $cashOut->type->name)) }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    @if ($cashOut->type->code === 'PS')
                                        {{ $this->format_phone_number($cashOut->recipient->phone_number, $cashOut->recipient->phone_iso) }}
                                    @elseif ($cashOut->type->code === 'CO')
                                        {{ isset($cashOut->extras['bank_name']) ?$cashOut->extras['bank_name'] : '' }}
                                        <br>
                                        {{ $cashOut->extras['account_number'] }}
                                    @else
                                        {{ get_class($cashOut->recipient) == 'App\Models\User' ? $this->format_phone_number($cashOut->recipient->phone_number, $cashOut->recipient->phone_iso) : $cashOut->recipient->name }}
                                    @endif
                                </x-table.rounded.td>
                                <x-table.rounded.td class="font-bold text-rp-red-500">
                                    {{ \Number::currency($cashOut->total_amount, 'PHP') }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ \Carbon\Carbon::parse($cashOut->created_at)->timezone('Asia/Manila')->format('F j, Y') }}
                                </x-table.rounded.td>
                                @switch($filter)
                                    @case('approved')
                                        <x-table.rounded.td>
                                            {{ \Carbon\Carbon::parse($cashOut->approved_at)->timezone('Asia/Manila')->format('F j, Y') }}
                                        </x-table.rounded.td>
                                        <x-table.rounded.td>
                                            {{ $cashOut->processor->user->name }}
                                        </x-table.rounded.td>
                                        @break
                                    @case('rejected')
                                        <x-table.rounded.td>
                                            {{ \Carbon\Carbon::parse($cashOut->deleted_at)->timezone('Asia/Manila')->format('F j, Y') }}
                                        </x-table.rounded.td>
                                        <x-table.rounded.td>
                                            {{ $cashOut->processor->user->name }}
                                        </x-table.rounded.td>
                                        @break
                                    @default
                                        
                                @endswitch
                            </x-table.rounded.row>
                        @endforeach
                    </x-slot:table_data>
                </x-table.rounded>
            </div>
            {{-- Pagination --}}
            @if ($transaction_requests->hasPages())
                <div class="flex items-center justify-center w-full gap-8">
                    <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                        <button wire:click="previousPage" {{ $transaction_requests->onFirstPage() ? 'disabled' : '' }}
                            class="{{ $transaction_requests->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                                    class="h-full border-r px-4 py-2 {{ $element == $transaction_requests->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                            @endif
                        @endforeach

                        <button wire:click="nextPage" {{ !$transaction_requests->hasMorePages() ? 'disabled' : '' }}
                            class="{{ !$transaction_requests->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                            <svg  width="7" height="13" viewBox="0 0 7 13"
                                fill="none">
                                <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>


        {{-- Confirmation Modal --}}
        <x-modal x-model="$wire.approveModal">
            <x-modal.confirmation-modal>
                <x-slot:title>Approve Request?</x-slot:title>
                <x-slot:message>
                    This action will approve the request and process the payment.
                </x-slot:message>
                <x-slot:action_buttons>
                    <x-button.outline-button class="flex-1"
                        @click="$wire.approveModal=false;$wire.reset_modal">go back</x-button.outline-button>
                    <x-button.filled-button class="flex-1"
                        @click="$wire.approve_request()">proceed</x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.confirmation-modal>
        </x-modal>

        <x-modal x-model="$wire.rejectModal">
            <x-modal.confirmation-modal>
                <x-slot:title>Reject Request?</x-slot:title>
                <x-slot:message>
                    This action will reject the request.
                </x-slot:message>
                <x-slot:action_buttons>
                    <x-button.outline-button class="flex-1"
                        @click="$wire.rejectModal=false;$wire.reset_modal;">go back</x-button.outline-button>
                    <x-button.filled-button class="flex-1"
                        @click="$wire.reject_request()">proceed</x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.confirmation-modal>
        </x-modal>
    </x-main.content>
    @if (!empty($transactionDetails))
        <div class="min-w-[35%] max-w-[35%] h-full px-5 py-10 overflow-auto">
            <div class="w-full">
                <h1 class="text-2xl font-bold text-rp-neutral-700">Transaction Request</h1>
                <div class="bg-white py-6 mt-7 rounded-lg w-full">
                    <div class="w-[90%] mx-auto">
                        <h3 class="text-rp-red-600 text-lg font-bold italic text-center">{{ $transactionDetails['type'] }}</h3>
                        <div class="leading-3 py-5 text-center">
                            <span>{{ $transactionDetails['label'] }}</span>
                            <p class="text-lg font-bold">{{ $transactionDetails['recipient'] }}</p>
                            @isset($transactionDetails['recipient_phone_number'])
                                <p>{{ $transactionDetails['recipient_phone_number'] }}</p>
                            @endisset
                        </div>
                        @isset($transactionDetails['info'])
                            <div class="py-3 border-b-2">
                                @foreach ($transactionDetails['info'] as $key => $item)
                                    @if ($item !== null)
                                        <div class="flex flex-row justify-between">
                                            <span class="font-bold">{{ $key }}</span>
                                            <span>{{ $item }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endisset
                        <div class="flex flex-row justify-between">
                            <span>Amount</span>
                            <span>{{ \Number::currency($transactionDetails['amount'], 'PHP') }}</span>
                        </div>
                        @if ($transactionDetails['type'] !== 'Transfer')
                            <div class="flex flex-row justify-between">
                                @if ($transactionDetails['type'] === 'Order Payment')
                                    <span>Delivery Fee</span>
                                @else
                                    <span>Service Fee</span>
                                @endif
                                <span>{{ $transactionDetails['service_fee'] > 0 ? \Number::currency($transactionDetails['service_fee'], 'PHP') : 'Free of Charge' }}</span>
                            </div>
                        @endif
                        <div class="flex flex-row justify-between">
                            <span class="font-bold">Total</span>
                            <span class="text-lg font-bold">{{ \Number::currency($transactionDetails['total'], 'PHP') }}</span>
                        </div>
                        <div class="mt-3">
                            <p class="text-center text-sm">{{ 'Created by: ' . $transactionDetails['created_by'] }}</p>
                        </div>
                        <div class="mt-3">
                            <p class="text-center text-sm">{{ $transactionDetails['created_at'] }}</p>
                        </div>
                    </div>
                </div>
                @if ($transactionDetails['allow_actions'] == true)
                    <div class="flex justify-end gap-2 my-4">
                        <x-button.filled-button @click.stop="$wire.approveModal=true">Approve</x-button.filled-button>
                        <x-button.filled-button @click.stop="$wire.rejectModal=true">Reject</x-button.filled-button>
                    </div>
                @endif
            </div>
        </div>
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

    <x-loader.black-screen wire:loading wire:target="show_transaction_details,filter,approve_request,reject_request" class="z-50">
        <x-loader.clock />
    </x-loader.black-screen>
</div>
