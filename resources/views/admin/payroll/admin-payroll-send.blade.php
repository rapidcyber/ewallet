<div class="flex h-full" x-data="{
    displayConfirmation: $wire.entangle('displayConfirmation'),
    confirmationMessage: $wire.entangle('confirmationMessage'),
    successMessage: $wire.entangle('successMessage'),
    successAmount: $wire.entangle('successAmount'),
    successRemainingBal: $wire.entangle('successRemainingBal'),
    failedMessage: $wire.entangle('failedMessage'),
}">
    <x-main.content class="grow h-full overflow-auto !px-[18.5px]">
        <x-main.title class="mb-8">Send Salary</x-main.title>

        <x-card.display-balance :balance="$this->balance_amount" class="mb-8" color="primary" />

        <div class="space-y-4">
            <div class="flex flex-row items-center justify-between">
                <h2 class="font-bold text-xl">Select Employee</h2>
                <div class="flex flex-row gap-3">
                    <x-input.search wire:model.live='searchTerm' icon_position="left" />
                    @if ($hasPages)
                        <div class="flex flex-row items-center gap-2">
                            <div wire:click="handlePageArrow('left')"
                                class="{{ 1 === $currentPageNumber ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }} px-3 py-2 hover:bg-gray-300 transition-all">
                                <x-icon.thin-chevron-left />
                            </div>
                            <div wire:click="handlePageArrow('right')"
                                class="{{ $totalPages === $currentPageNumber ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }} px-3 py-2 hover:bg-gray-300 transition-all">
                                <x-icon.thin-chevron-right />
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Employee Selection --}}
            <div class="grid grid-cols-5 gap-2 w-full h-[270px] min-h-[270px]">
                {{-- Go to Employees --}}
                <a  href="{{ route('admin.employees.index') }}"
                    class="h-full px-3 py-2 rounded-xl border border-rp-neutral-500 flex flex-col items-center justify-center cursor-pointer hover:bg-rp-neutral-200">
                    <x-icon.user width="48" height="48" />
                    <p class="text-center">Go to Employees</p>
                </a>

                @foreach ($employees as $employee)
                    <div role="button" tabindex="0" @keyup.enter="$wire.handleEmployeeSelect({{ $employee->id }})" wire:key="employee-{{ $employee->id }}" wire:click="handleEmployeeSelect({{ $employee->id }})"
                        wire:target='handleEmployeeSelect' wire:loading.attr="disabled"
                        wire:loading.class='cursor-progress'
                        class="h-full relative px-3 py-7 border rounded-xl bg-white flex-1 flex flex-col items-center cursor-pointer hover:bg-rp-neutral-50">
                        @if ($selectedEmployee and $employee->id === $selectedEmployee->id)
                            <div class="absolute top-2 right-3">
                                <x-icon.check />
                            </div>
                        @endif

                        <div class="max-w-28 max-h-28 mb-3">
                            {{-- @if ($profile_picture = $employee->user->profile_picture)
                                <img src="{{ $this->get_media_url($profile_picture) }}"
                                    class="w-full h-full object-cover rounded-full" alt="">
                            @else --}}
                                <img src="{{ url('images/user/default-avatar.png') }}"
                                    class="w-full h-full object-cover rounded-full" alt="">
                            {{-- @endif --}}

                        </div>
                        <p class="font-bold text-xl text-center w-full truncate">{{ $employee->user->name }}</p>

                        <p class="text-sm text-center truncate w-full">{{ $employee->occupation }}</p>
                        <p class="text-center text-sm truncate w-full">Salary: <span
                                class="text-primary-600">₱{{ number_format($employee->salary, 2) }}</span></p>
                        <p class="text-center text-sm truncate w-full">Salary Type: <span
                                class="text-primary-600">{{ $employee->salary_type->name }}</span></p>
                    </div>
                @endforeach
            </div>

            @if ($selectedEmployee)
                <div class="mt-9">
                    <h2 class="font-bold text-xl mb-3">Salary Details</h2>
                    <div class="flex flex-row gap-2">
                        <x-input.input-group class="flex-1">
                            <x-slot:label>Amount:</x-slot:label>
                            <x-input disabled type="number" value="{{ $selectedEmployee->salary }}" />
                        </x-input.input-group>

                        @if ($selectedEmployee->salary_type->slug === 'per_day')
                            <x-input.input-group class="flex-1">
                                <x-slot:label>Days Worked:</x-slot:label>
                                <x-input wire:model.live.debounce.250ms='days_worked' type="number" />
                                @error('amount')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </x-input.input-group>
                        @endif

                        <x-input.input-group class="flex-1">
                            <x-slot:label>Deductions:</x-slot:label>
                            <x-input wire:model.live.debounce.250ms='deductions' min="0" type="number" />
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
                @if ($profile_picture = $selectedEmployee->user->profile_picture)
                    <x-layout.summary.profile image_path="{{ $this->get_media_url($profile_picture) }}">
                        <x-slot:info_block_top>Salary to:</x-slot:info_block_top>
                        <x-slot:info_block_middle>{{ $selectedEmployee->user->name }}</x-slot:info_block_middle>
                        <x-slot:info_block_bottom>{{ $selectedEmployee->user->phone_number }}</x-slot:info_block_bottom>
                    </x-layout.summary.profile>
                @else
                    <x-layout.summary.profile image_path="{{ url('images/user/default-avatar.png') }}">
                        <x-slot:info_block_top>Salary to:</x-slot:info_block_top>
                        <x-slot:info_block_middle>{{ $selectedEmployee->user->name }}</x-slot:info_block_middle>
                        <x-slot:info_block_bottom>{{ $selectedEmployee->user->phone_number }}</x-slot:info_block_bottom>
                    </x-layout.summary.profile>
                @endif
            </x-slot:profile>
            <x-slot:body>
                <x-layout.summary.section title="Salary Details" color="primary">
                    <x-slot:data>
                        <x-layout.summary.label-data>
                            <x-slot:label>Amount</x-slot:label>
                            <x-slot:data>{{ number_format($selectedEmployee->salary, 2) }}</x-slot:data>
                        </x-layout.summary.label-data>

                        @if ($selectedEmployee->salary_type->slug === 'per_day')
                            <x-layout.summary.label-data>
                                <x-slot:label>Days Worked</x-slot:label>
                                <x-slot:data>{{ number_format($days_worked) }}</x-slot:data>
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
                            <x-slot:data class="text-primary-600">{{ number_format($net_pay, 2) }}</x-slot:data>
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
                    <x-button.filled-button color="primary" wire:click='showConfirmationModal' :disabled="$salaryDetailErrorMessage">
                        send
                    </x-button.filled-button>
                    <x-button.outline-button color="primary" wire:click='clearEmployeeSelect'>
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
                <x-layout.summary.section color="primary" title="Salary Details">
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
                            <x-slot:data class="text-primary-600">-</x-slot:data>
                        </x-layout.summary.label-data>
                    </x-slot:data>
                </x-layout.summary.section>
            </x-slot:body>
            <x-slot:action>
                <div class="flex flex-col gap-2">
                    <x-button.filled-button :disabled="true" color="primary">
                        send
                    </x-button.filled-button>
                </div>
            </x-slot:action>
        @endif
    </x-layout.summary>


    {{-- Confirm Modal --}}
    <x-modal x-model="displayConfirmation">
        <x-modal.confirmation-modal title="Confirmation">
            <x-slot:message>
                <span x-text="confirmationMessage"></span>
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button color="primary" wire:target='send_salary' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' @click="displayConfirmation=false;confirmationMessage=''"
                    class="flex-1">go
                    back</x-button.outline-button>
                <x-button.filled-button color="primary" wire:target='send_salary' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' @click="$wire.send_salary()"
                    class="flex-1">confirm</x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>
    </x-modal>

    {{-- Success Modal --}}
    <div x-cloak x-show="successMessage.length > 0 && successAmount !== null && successRemainingBal !== null"
        class="fixed grid place-items-center inset-0 backdrop-blur-sm bg-black/20 z-50">
        <div class="w-96 px-7 py-4 space-y-2 bg-white rounded-2xl">
            <div class="flex items-center justify-center w-full">
                <img src="{{ url('images/placeholder/success-transaction.svg') }}" alt="Success Transaction">
            </div>
            <h1 class="text-center text-2xl font-bold">Success!</h1>
            <p x-text="successMessage" class="font-semibold text-center text-sm"></p>
            <div class="mt-3">
                <div class="flex flex-row justify-between py-2 border-b">
                    <p>Payment</p>
                    <p x-text="`₱${successAmount}`"></p>
                </div>
                <div class="flex flex-row justify-between py-2">
                    <p>Remaining Balance</p>
                    <p x-text="`₱${successRemainingBal}`"></p>
                </div>
            </div>
            <x-button.filled-button @click="successMessage='';successAmount=null;successRemainingBal=null" color="primary" class="w-full">
                go back
            </x-button.filled-button>
        </div>
    </div>

    {{-- Failed Modal --}}
    <div x-cloak x-show="failedMessage"
        class="fixed grid place-items-center inset-0 backdrop-blur-sm bg-black/20 z-50 rounded-2xl">
        <div class="w-96 px-14 py-4 space-y-2 bg-white rounded-2xl">
            <div class="flex items-center justify-center w-full">
                <img src="{{ url('images/placeholder/failed-transaction.svg') }}" alt="">
            </div>
            <h1 class="text-center text-2xl font-bold">Oops!</h1>
            <p x-text="failedMessage" class="font-semibold text-center text-sm"></p>
            <x-button.filled-button color="primary" @click="failedMessage=''" class="w-full">go back</x-button.filled-button>
        </div>
    </div>
    <x-loader.black-screen  wire:loading.flex wire:target="send_salary" />
</div>
