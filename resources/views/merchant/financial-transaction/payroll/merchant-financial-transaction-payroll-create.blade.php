<div class="flex h-full" x-data>
    <x-main.content class="grow overflow-auto !px-[18.5px]">
        <x-main.title class="mb-8">Send Salary</x-main.title>

        <x-card.display-balance :balance="$this->available_balance" class="mb-8" />

        <div class="space-y-4">
            <div class="flex flex-row items-center justify-between">
                <h2 class="font-bold text-xl">Select Employee</h2>
                <div class="flex flex-row gap-3">
                    <x-input.search wire:model.live='searchTerm' icon_position="left" />
                    @if ($hasPages)
                        <div class="flex flex-row items-center gap-2">
                            <button wire:click="handlePageArrow('left')"
                                class="{{ 1 === $currentPageNumber ? 'cursor-not-allowed opacity-50' : '' }} px-3 py-2 hover:bg-gray-300 transition-all">
                                <x-icon.thin-chevron-left />
                            </button>
                            <button wire:click="handlePageArrow('right')"
                                class="{{ $totalPages === $currentPageNumber ? 'cursor-not-allowed opacity-50' : '' }} px-3 py-2 hover:bg-gray-300 transition-all">
                                <x-icon.thin-chevron-right />
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Employee Selection --}}
            <div class="grid grid-cols-5 gap-2 w-full h-[270px] min-h-[270px]">
                {{-- Go to Employees --}}
                <a  href="{{ route('merchant.financial-transactions.employees.index', ['merchant' => $merchant]) }}"
                    class="h-full min-h-full px-3 py-2 rounded-xl border border-rp-neutral-500 flex flex-col items-center justify-center cursor-pointer hover:bg-rp-neutral-200">
                    <x-icon.user width="48" height="48" />
                    <p class="text-center">Go to Employees</p>
                </a>

                @foreach ($employees as $employee)
                    <div role="button" tabindex="0" @keyup.enter="$wire.handleEmployeeSelect({{ $employee->id }})" wire:key="employee-{{ $employee->id }}" wire:click="handleEmployeeSelect({{ $employee->id }})"
                        wire:target='handleEmployeeSelect' wire:loading.attr="disabled"
                        wire:loading.class='cursor-progress'
                        class="h-full min-h-full relative px-3 py-7 border rounded-xl bg-white flex-1 flex flex-col items-center cursor-pointer hover:bg-rp-neutral-50">
                        @if ($selectedEmployee and $employee->id === $selectedEmployee->id)
                            <div class="absolute top-2 right-3">
                                <x-icon.check />
                            </div>
                        @endif

                        <div class="max-w-28 max-h-28 mb-3">
                            <img src="{{ url('images/user/default-avatar.png') }}"
                                class="w-full h-full object-cover rounded-full" alt="">
                        </div>
                        <p class="font-bold text-xl text-center w-full truncate">{{ $employee->user->name }}</p>
                        <p class="text-sm text-center truncate w-full">{{ $employee->occupation }}</p>
                        <p class="text-center text-sm truncate w-full">Salary: <span
                                class="text-rp-pink-500">₱{{ number_format($employee->salary, 2) }}</span></p>
                        <p class="text-center text-sm truncate w-full">Salary Type: <span
                            class="text-rp-pink-500">{{ $employee->salary_type->name }}</span></p>
                    </div>
                @endforeach
            </div>

            @if ($selectedEmployee)
                <div class="mt-3">
                    <h2 class="font-bold text-xl mb-3">Salary Details</h2>
                    <div class="flex flex-row gap-2">
                        <x-input.input-group class="flex-1">
                            <x-slot:label>Amount:</x-slot:label>
                            <x-input disabled type="number" value="{{ $selectedEmployee->salary }}" />
                        </x-input.input-group>

                        @if ($selectedEmployee->salary_type->slug === 'per_day')
                            <x-input.input-group class="flex-1">
                                <x-slot:label>Days Worked:</x-slot:label>
                                <x-input wire:model.blur='days_worked' type="number" />
                                @error('amount')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </x-input.input-group>
                        @endif

                        <x-input.input-group class="flex-1">
                            <x-slot:label>Deductions:</x-slot:label>
                            <x-input wire:model.blur='deductions' min="0" type="number" />
                            @error('deductions')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </x-input.input-group>

                        {{-- <div class="flex flex-col flex-1">
                            <p>Send payment on:</p>
                            @if ($paymentDateToggle) 
                                <div class="flex flex-row items-center border-2 bg-white rounded-md overflow-hidden border-gray-500">
                                    <div wire:click="togglePaymentSelection(false)" class="px-3 py-2 cursor-pointer">&times;</div>
                                    <input 
                                    type="date"  

                                    wire:model="send_payment_at"
                                    wire:click="togglePaymentSelection(true)" class=" border-none outline-none focus:ring-0 bg-none w-full"/>
                                </div>
                            @else
                                <input 
                                type="text"  
                                value="Immediately"
                                wire:click="togglePaymentSelection(true)" class="rounded-md"/>
                            @endif
                            
                        
                        </div> --}}
                    </div>
                </div>
                {{-- <div class="space-y-3">
                    <label class="space-x-1 cursor-pointer flex flex-row items-center gap-2">
                        <x-input type="checkbox" />
                        <span class="align-middle">I would like to pay this employee’s SSS here</span>
                    </label>

                    @php
                        $pay_sss = true;
                    @endphp

                    @if ($pay_sss)
                        <div class="flex flex-row gap-2">

                            <x-input.input-group class="flex-1">
                                <x-slot:label>SSS Number:</x-slot:label>    
                                <x-input type="text" />
                                @error('sss_number')
                                    <p class="text-sm text-red-600">{{$message}}</p>
                                @enderror
                            </x-input.input-group>    

                            <x-input.input-group class="flex-1">
                                <x-slot:label>SSS Payment Amount:</x-slot:label>
                                <x-input type="number" />
                                @error('sss_payment_amount')
                                    <p class="text-sm text-red-600">{{$message}}</p>
                                @enderror
                            </x-input.input-group>
                        </div>
                    @endif
                </div> --}}
            @endif
        </div>

    </x-main.content>

    <x-layout.summary>
        @if ($selectedEmployee)
            <x-slot:profile>
                {{-- @if ($profile_picture = $selectedEmployee->user->profile_picture)
                    <x-layout.summary.profile image_path="{{ $this->get_media_url($profile_picture) }}">
                        <x-slot:info_block_top>Salary to:</x-slot:info_block_top>
                        <x-slot:info_block_middle>{{ $selectedEmployee->user->name }}</x-slot:info_block_middle>
                        <x-slot:info_block_bottom>{{ $selectedEmployee->user->phone_number }}</x-slot:info_block_bottom>
                    </x-layout.summary.profile>
                @else --}}
                <x-layout.summary.profile image_path="{{ url('images/user/default-avatar.png') }}">
                    <x-slot:info_block_top>Salary to:</x-slot:info_block_top>
                    <x-slot:info_block_middle>{{ $selectedEmployee->user->name }}</x-slot:info_block_middle>
                    <x-slot:info_block_bottom>{{ $this->format_phone_number($selectedEmployee->user->phone_number, $selectedEmployee->user->phone_iso) }}</x-slot:info_block_bottom>
                </x-layout.summary.profile>
                {{-- @endif --}}
            </x-slot:profile>
            <x-slot:body>
                <x-layout.summary.section title="Salary Details">
                    <x-slot:data>
                        <x-layout.summary.label-data>
                            <x-slot:label>Amount</x-slot:label>
                            <x-slot:data>{{ number_format($selectedEmployee->salary, 2) }}</x-slot:data>
                        </x-layout.summary.label-data>

                        @if ($selectedEmployee->salary_type->slug === 'per_day')
                            <x-layout.summary.label-data>
                                <x-slot:label>Days Worked</x-slot:label>
                                <x-slot:data>{{ $days_worked }}</x-slot:data>
                            </x-layout.summary.label-data>
                        @endif

                        <x-layout.summary.label-data>
                            <x-slot:label>Deductions</x-slot:label>
                            <x-slot:data>{{ number_format($deductions, 2) }}</x-slot:data>
                        </x-layout.summary.label-data>

                        {{-- @if ($pay_sss)
                            <x-layout.summary.label-data>
                                <x-slot:label>SSS Payment Amount</x-slot:label>
                                <x-slot:data>-</x-slot:data>
                            </x-layout.summary.label-data>
                        @endif --}}

                        <x-layout.summary.label-data>
                            <x-slot:label>Net Pay</x-slot:label>
                            <x-slot:data class="text-rp-red-500">{{ \Number::currency($net_pay, 'PHP') }}</x-slot:data>
                        </x-layout.summary.label-data>

                        {{-- @if ($pay_sss)
                            <x-layout.summary.label-data>
                                <x-slot:label>SSS Number</x-slot:label>
                                <x-slot:data>₱28,945.00</x-slot:data>
                            </x-layout.summary.label-data>
                        @endif --}}

                    </x-slot:data>
                </x-layout.summary.section>
            </x-slot:body>
            <x-slot:action>
                <div class="flex flex-col gap-2">
                    @if ($salaryDetailErrorMessage)
                        <p class="text-sm text-red-600">{{ $salaryDetailErrorMessage }}</p>
                    @endif
                    <x-button.filled-button wire:click='showConfirmationModal' :disabled="$salaryDetailErrorMessage" 
                        wire:target='showConfirmationModal' wire:loading.attr='disabled' wire:loading.class='cursor-progress'>
                        send
                    </x-button.filled-button>
                    <x-button.outline-button href="{{ route('merchant.financial-transactions.payroll.index', ['merchant' => $merchant]) }}">
                        cancel
                    </x-button.outline-button>
                </div>
            </x-slot:action>
        @else
            <x-slot:profile>
                <x-layout.summary.profile image_path="{{ url('images/user/default-avatar.png') }}">
                    <x-slot:info_block_top>Salary to:</x-slot:info_block_top>
                    <x-slot:info_block_middle>-</x-slot:info_block_middle>
                    <x-slot:info_block_bottom>-</x-slot:info_block_bottom>
                </x-layout.summary.profile>
            </x-slot:profile>
            <x-slot:body>
                <x-layout.summary.section title="Salary Details">
                    <x-slot:data>
                        <x-layout.summary.label-data>
                            <x-slot:label>Amount</x-slot:label>
                            <x-slot:data>-</x-slot:data>
                        </x-layout.summary.label-data>
                        <x-layout.summary.label-data>
                            <x-slot:label>Deductions</x-slot:label>
                            <x-slot:data>-</x-slot:data>
                        </x-layout.summary.label-data>

                        <x-layout.summary.label-data>
                            <x-slot:label>Net Pay</x-slot:label>
                            <x-slot:data class="text-rp-red-500">-</x-slot:data>
                        </x-layout.summary.label-data>
                    </x-slot:data>
                </x-layout.summary.section>
            </x-slot:body>
            <x-slot:action>
                <div class="flex flex-col gap-2">
                    <x-button.filled-button :disabled="true">
                        send
                    </x-button.filled-button>
                    <x-button.outline-button href="{{ route('merchant.financial-transactions.payroll.index', ['merchant' => $merchant]) }}">
                        cancel
                    </x-button.outline-button>
                </div>
            </x-slot:action>
        @endif
    </x-layout.summary>


    {{-- Confirm Modal --}}
    <x-modal x-model="$wire.displayConfirmation">
        <x-modal.confirmation-modal title="Confirm Payroll?">
            <x-slot:message>
                <span x-text="$wire.confirmationMessage"></span>
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button wire:target='submit' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' @click="$wire.displayConfirmation=false;"
                    class="flex-1">go
                    back</x-button.outline-button>
                <x-button.filled-button wire:target='submit' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' @click="$wire.submit()"
                    class="flex-1">proceed</x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>
    </x-modal>

    {{-- Success Modal --}}
    <div x-cloak x-show="$wire.successMessage.length > 0"
        class="fixed grid place-items-center inset-0 backdrop-blur-sm bg-black/20 z-50">
        <div class="w-96 px-7 py-4 space-y-2 bg-white rounded-2xl">
            <div class="flex items-center justify-center w-full">
                <img src="{{ url('images/placeholder/success-transaction.svg') }}" alt="Success Transaction">
            </div>
            <h1 class="text-center text-2xl font-bold">Success!</h1>
            <p x-text="successMessage" class="font-semibold text-center text-sm"></p>
            <div class="mt-3">
                <template x-if="$wire.successAmount !== null">
                    <div class="flex flex-row justify-between py-2 border-b">
                        <p>Payment</p>
                        <p x-text="`₱${successAmount}`"></p>
                    </div>
                </template>
                <template x-if="$wire.successRemainingBal !== null">
                    <div class="flex flex-row justify-between py-2">
                        <p>Remaining Balance</p>
                        <p x-text="$wire.successRemainingBal"></p>
                    </div>
                </template>
            </div>
            <button @click="$wire.successMessage='';$wire.successAmount=null;$wire.successRemainingBal=null"
                class="uppercase px-3 py-2 w-full rounded-lg text-white font-semibold bg-pink-600 hover:bg-pink-700">go
                back</button>
        </div>
    </div>

    {{-- Failed Modal --}}
    <div x-cloak x-show="$wire.failedMessage"
        class="fixed grid place-items-center inset-0 backdrop-blur-sm bg-black/20 z-50 rounded-2xl">
        <div class="w-96 px-14 py-4 space-y-2 bg-white rounded-2xl">
            <div class="flex items-center justify-center w-full">
                <img src="{{ url('images/placeholder/failed-transaction.svg') }}" alt="">
            </div>
            <h1 class="text-center text-2xl font-bold">Oops!</h1>
            <p x-text="$wire.failedMessage" class="font-semibold text-center text-sm"></p>
            <x-button.filled-button @click="$wire.failedMessage=''" class="w-full">go back</x-button.filled-button>
        </div>
    </div>

    <x-loader.black-screen wire:loading wire:target='send_salary,handleEmployeeSelect'>
        <x-loader.clock />
    </x-loader.black-screen>
</div>
