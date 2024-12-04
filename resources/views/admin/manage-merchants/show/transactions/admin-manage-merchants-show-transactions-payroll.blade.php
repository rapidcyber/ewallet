<x-main.content class="!px-16 !py-10">
    <livewire:components.layout.admin.merchant-details-header :merchant="$merchant" />

    <div class="mt-8">
        <x-card.display-balance title="Merchant Balance" :balance="$merchant->latest_balance?->amount ?? 0.00" color="primary" class="mb-6"/>

        <div class="flex">
            {{-- 1st Column: Left Sidebar --}}
            <x-layout.admin.merchant-details.transactions.left-sidebar :merchant="$merchant" class="w-60"/>

            {{-- 2nd Column: Table --}}
            <div class="w-[calc(100%-240px)] pl-4 space-y-8">
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
                                    <button wire:click="sortTable('salary_type_id')">
                                        <x-icon.sort />
                                    </button>
                                </div>
                            </x-table.rounded.th>
                            {{-- <x-table.rounded.th>
                                <div class="flex flex-row items-center">
                                    <span>Deductions</span>
                                    <div class="cursor-pointer" wire:click="sortTable('total_deductions')">
                                        <x-icon.sort />
                                    </div>
                                </div>
                            </x-table.rounded.th>
                            <x-table.rounded.th>
                                <div class="flex flex-row items-center">
                                    <span>SSS</span>
                                    <div class="cursor-pointer" wire:click="sortTable('sss_deduction')">
                                        <x-icon.sort />
                                    </div>
                                </div>
                            </x-table.rounded.th>
                            <x-table.rounded.th>
                                <div class="flex flex-row items-center">
                                    <span>Net Pay</span>
                                    <div class="cursor-pointer" wire:click="sortTable('net_pay')">
                                        <x-icon.sort />
                                    </div>
                                </div>
                            </x-table.rounded.th> --}}
                            <x-table.rounded.th>
                                Sent Date
                            </x-table.rounded.th>
                        </x-slot:table_header>
                        <x-slot:table_data>
                            <tr><td class="pt-8"></td></tr>
                            @foreach ($employees as $employee)
                                <x-table.rounded.row>
                                    <x-table.rounded.td>
                                        {{ $employee->user->name }}
                                    </x-table.rounded.td>
                                    <x-table.rounded.td>
                                        ₱{{ number_format($employee->salary, 2) }}
                                    </x-table.rounded.td>
                                    <x-table.rounded.td>
                                        {{ $employee->salary_type->name }}
                                    </x-table.rounded.td>
                                    {{-- <x-table.rounded.td>
                                        ₱{{ number_format($employee->total_deductions, 2) }}
                                    </x-table.rounded.td>
                                    <x-table.rounded.td>
                                        ₱{{ number_format($employee->sss_deduction, 2) }}
                                    </x-table.rounded.td>
                                    <x-table.rounded.td class="text-rp-red-600 font-bold">
                                        ₱{{ number_format($employee->net_pay, 2) }}
                                    </x-table.rounded.td> --}}
                                    <x-table.rounded.td>
                                        @if ($transaction = $employee->user->incoming_transactions->first())
                                            {{ \Carbon\Carbon::parse($transaction->created_at)->format('F j, Y') }}
                                        @else
                                            -
                                        @endif
                                    </x-table.rounded.td>
                                </x-table.rounded.row>
                            @endforeach
                        </x-slot:table_data>
                    </x-table.rounded>
                </div>
            </div>
        </div>
    </div>
</x-main.content>