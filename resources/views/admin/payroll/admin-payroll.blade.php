<div class="flex h-full">
    <x-main.content class="grow !px-16 !py-10">
        <x-main.action-header>
            <x-slot:title>Payroll</x-slot:title>
            <x-slot:actions>
                <x-button.filled-button href="{{ route('admin.payroll.send') }}" color="primary">send salary</x-button.filled-button>
                <x-button.outline-button href="{{ route('admin.payroll.send-bulk') }}" color="primary">bulk send</x-button.outline-button>
            </x-slot:actions>
        </x-main.action-header>

        <x-card.display-balance :balance="$this->balance_amount" color="primary" class="mb-8" />

        <div class="space-y-8">
            <x-layout.search-container>
                <x-input.search wire:model.live='searchTerm' icon_position="left" />
            </x-layout.search-container>

            <div>
                <x-table.rounded>
                    <x-slot:table_header>
                        <x-table.rounded.th>Employee</x-table.rounded.th>
                        <x-table.rounded.th>
                            <div class="flex flex-row items-center">
                                <span>Base Salary</span>
                                <button wire:click="sortTable('salary')">
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th>
                            <div class="flex flex-row items-center">
                                <span>Salary Type</span>
                            </div>
                        </x-table.rounded.th>
                        {{-- <x-table.rounded.th>
                                <div class="flex flex-row items-center">
                                    <span>Deductions</span>
                                    <div class="cursor-pointer" wire:click="sortTable('total_deductions')">
                                        <x-icon.sort />
                                    </div>
                                </div>
                            </x-table.rounded.th> --}}
                        {{-- <x-table.rounded.th>
                                <div class="flex flex-row items-center">
                                    <span>SSS</span>
                                    <div class="cursor-pointer" wire:click="sortTable('sss_deduction')">
                                        <x-icon.sort />
                                    </div>
                                </div>
                            </x-table.rounded.th> --}}
                        <x-table.rounded.th>
                            <div class="flex flex-row items-center">
                                <span>Net Pay</span>
                                <button wire:click="sortTable('amount')">
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                        <x-table.rounded.th>
                            <div class="flex flex-row items-center">
                                <span>Sent Date</span>
                                <button wire:click="sortTable('created_at')">
                                    <x-icon.sort />
                                </button>
                            </div>
                        </x-table.rounded.th>
                    </x-slot:table_header>
                    <x-slot:table_data>
                        <tr>
                            <td class="pt-8"></td>
                        </tr>
                        @foreach ($payroll_transactions as $payroll)
                            <x-table.rounded.row>
                                <x-table.rounded.td class="flex flex-row items-center gap-2">
                                    <div class="w-10 h-10 min-w-10 min-h-10 rounded-full">
                                        {{-- @if ($profile_picture = $payroll->recipient->profile_picture)
                                            <img src="{{ $this->get_media_url($profile_picture, 'thumbnail') }}"
                                                alt="" class="w-full h-full object-cover rounded-full">
                                        @else --}}
                                            <img src="{{ url('images/user/default-avatar.png') }}" alt=""
                                                class="w-full h-full object-cover rounded-full">
                                        {{-- @endif --}}
                                    </div>
                                    {{ $payroll->recipient->name }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    ₱{{ number_format($payroll->salary, 2) }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ $payroll->recipient->employee->first()->salary_type->name }}
                                </x-table.rounded.td>
                                {{-- <x-table.rounded.td>
                                        ₱{{ number_format($employee->total_deductions, 2) }}
                                    </x-table.rounded.td> --}}
                                {{-- <x-table.rounded.td>
                                        ₱{{ number_format($employee->sss_deduction, 2) }}
                                    </x-table.rounded.td> --}}
                                <x-table.rounded.td class="text-primary-600 font-bold">
                                    ₱{{ number_format($payroll->amount, 2) }}
                                </x-table.rounded.td>
                                <x-table.rounded.td>
                                    {{ \Carbon\Carbon::parse($payroll->created_at)->timezone('Asia/Manila')->format('F j, Y') }}
                                </x-table.rounded.td>
                            </x-table.rounded.row>
                        @endforeach
                    </x-slot:table_data>
                </x-table.rounded>
            </div>
            {{-- Pagination --}}
            <div class="flex items-center justify-center w-full gap-8">
                @if ($payroll_transactions->hasPages())
                    <div class="flex flex-row items-center h-10 gap-0 mt-4 overflow-hidden border rounded-md w-max">
                        <button wire:click="previousPage" {{ $payroll_transactions->onFirstPage() ? 'disabled' : '' }}
                            class="{{ $payroll_transactions->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
                                    class="h-full border-r px-4 py-2 {{ $element == $payroll_transactions->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer bg-white' }}">{{ $element }}</button>
                            @endif
                        @endforeach

                        <button wire:click="nextPage" {{ !$payroll_transactions->hasMorePages() ? 'disabled' : '' }}
                            class="{{ !$payroll_transactions->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
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
</div>
