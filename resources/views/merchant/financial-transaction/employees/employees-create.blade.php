<div class="flex h-full" x-data="{
    mode_no_repay_account() {
        return $wire.mode == 'no_repay_account';
    },
    mode_has_repay_account() {
        return $wire.mode == 'has_repay_account';
    }
    }">
    <x-main.content class="flex-1 h-full overflow-auto !px-[18.5px]">
        <x-main.title class="mb-8">
            Add Employee
        </x-main.title>

        <div class="grid grid-cols-2 gap-3 mb-9">
            <x-card.select-card :title="'No RePay Account'" :description="'Select this if your employee doesn’t have a RePay account.'" x-model="mode_no_repay_account()"
                @click="$wire.mode = 'no_repay_account';$wire.dispatch('updateMode')" />
            <x-card.select-card :title="'Has RePay Account'" :description="'Select this if your employee does have a RePay Account.'" x-model="mode_has_repay_account()"
                @click="$wire.mode = 'has_repay_account';$wire.dispatch('updateMode')"/>
        </div>

        <div x-cloak x-show="$wire.mode === 'no_repay_account'" class="flex flex-col gap-9">
            {{-- Contact Information --}}
            <div>
                <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Contact Information</h3>

                <div class="grid grid-cols-3 gap-3">
                    {{-- Phone number --}}
                    <div class="flex flex-col basis-1">
                        <label class="text-xs 2xl:text-sm" for="no_repay_phone_number"><span
                                class="text-red-500">*</span>Phone Number:</label>
                        <div class="flex flex-row gap-1 2xl:gap-2">
                            <x-dropdown.select wire:model.change="phone_iso">
                                <x-dropdown.select.option value="" selected>Select</x-dropdown.select.option>
                                @foreach ($this->phone_number_prefixes as $prefixes)
                                    <x-dropdown.select.option
                                        value="{{ $prefixes['code'] }}">{{ $prefixes['dial_code'] . ' (' . $prefixes['code'] . ')' }}</x-dropdown.select.option>
                                @endforeach
                            </x-dropdown.select>
                            <x-input id="phone_number" type="tel" placeholder="912-345-6789" class="flex-1"
                                wire:model.blur="phone_number"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                x-bind:disabled="$wire.phone_iso == ''">
                                <x-slot:icon>
                                    <x-icon.phone />
                                </x-slot:icon>
                            </x-input>

                        </div>
                        @error('phone_iso')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        @error('phone_number')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Telephone number --}}
                    <x-input.input-group>
                        <x-slot:label for="telephone_number">
                            Telephone Number:
                        </x-slot:label>
                        <x-input type="text" id="telephone_number" name="telephone_number" placeholder="Tel. No"
                            wire:model.blur='telephone_number'
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            <x-slot:icon>
                                <x-icon.phone />
                            </x-slot:icon>
                        </x-input>
                    </x-input.input-group>

                    {{-- Email address --}}
                    <x-input.input-group>
                        <x-slot:label>
                            <span class="text-red-500">*</span>Email address:
                        </x-slot:label>
                        <x-input type="text" id="email" name="email" placeholder="Email address"
                            wire:model.blur='email'>
                            <x-slot:icon>
                                <x-icon.mail />
                            </x-slot:icon>
                        </x-input>
                        @error('email')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>
                </div>
            </div>

            {{-- Personal Information --}}
            <div>
                <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Personal Information</h3>
                <div class="grid grid-cols-4 gap-3 mb-3">
                    {{-- First name --}}
                    <x-input.input-group>
                        <x-slot:label for="first_name">
                            <span class="text-red-500">*</span>First Name:
                        </x-slot:label>
                        <x-input type="text" id="first_name" name="first_name" placeholder="First Name"
                            wire:model.blur='first_name' />
                        @error('first_name')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Middle name --}}
                    <x-input.input-group>
                        <x-slot:label for="middle_name">
                            Middle Name:
                        </x-slot:label>
                        <x-input type="text" id="middle_name" name="middle_name" placeholder="Middle Name"
                            wire:model.blur="middle_name" />
                    </x-input.input-group>

                    {{-- Surname --}}
                    <x-input.input-group>
                        <x-slot:label for="surname">
                            <span class="text-red-500">*</span>Surname
                        </x-slot:label>
                        <x-input type="text" id="surname" name="surname" placeholder="Surname"
                            wire:model.blur="surname" />
                        @error('surname')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Suffix --}}
                    <x-input.input-group>
                        <x-slot:label for="telephone_number">
                            Suffix:
                        </x-slot:label>
                        <x-input type="text" id="telephone_number" name="telephone_number" placeholder="Suffix"
                            wire:model.blur="suffix" />
                    </x-input.input-group>

                    {{-- Gender --}}
                    <x-input.input-group>
                        <x-slot:label for="gender">
                            Gender
                        </x-slot:label>
                        <x-dropdown.select wire:model.change="gender">
                            <x-dropdown.select.option value="" selected>Select</x-dropdown.select.option>
                            <x-dropdown.select.option value="male">Male</x-dropdown.select.option>
                            <x-dropdown.select.option value="female">Female</x-dropdown.select.option>
                        </x-dropdown.select>
                        @error('gender')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Nationality --}}
                    <x-input.input-group>
                        <x-slot:label for="nationality">
                            Nationality
                        </x-slot:label>
                        <x-dropdown.select wire:model.blur="nationality">
                            <x-dropdown.select.option value="" selected
                                hidden>Select</x-dropdown.select.option>
                            @foreach ($this->nationalities as $nationality_opt)
                                <x-dropdown.select.option
                                    value="{{ $nationality_opt }}">{{ $nationality_opt }}</x-dropdown.select.option>
                            @endforeach
                        </x-dropdown.select>
                        @error('nationality')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Birthdate --}}
                    <x-input.input-group>
                        <x-slot:label for="birthdate">
                            Birthdate
                        </x-slot:label>
                        <x-input type="date" id="birthdate" max="maxDate" name="birthplace"
                            wire:model.blur="birthdate" />
                        @error('birthdate')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Birthplace --}}
                    <x-input.input-group>
                        <x-slot:label for="birthplace">
                            Birthplace
                        </x-slot:label>
                        <x-input type="text" id="birthplace" name="birthplace" placeholder="Birthplace"
                            wire:model.blur='birthplace' />
                        @error('birthplace')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>
                </div>

                {{-- Mother's Maiden Name --}}
                <x-input.input-group>
                    <x-slot:label for="maiden_name">
                        Mother's Maiden Name
                    </x-slot:label>
                    <x-input type="text" id="maiden_name" name="maiden_name" placeholder="Mother's Maiden Name"
                        wire:model.blur='mothers_maiden_name' />
                    <p class="text-right text-xs text-rp-neutral-500">First Name, Middle Name, Last Name</p>
                </x-input.input-group>
            </div>

            {{-- Employee Details --}}
            <div>
                <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Employee Details</h3>
                <div class="grid grid-cols-3 gap-3 mb-3">

                    {{-- Position --}}
                    <x-input.input-group>
                        <x-slot:label for="position">
                            <span class="text-red-500">*</span>Position
                        </x-slot:label>
                        <x-input type="text" id="position" name="position" placeholder="Position"
                            wire:model.blur='position' />
                        @error('position')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Salary Type --}}
                    <x-input.input-group>
                        <x-slot:label for="salary_type">
                            <span class="text-red-500">*</span>Salary Type
                        </x-slot:label>
                        <x-dropdown.select wire:model.change="salary_type">
                            <x-dropdown.select.option value="" hidden
                                selected>Select</x-dropdown.select.option>
                            @foreach ($this->salary_types as $types)
                                <x-dropdown.select.option
                                    value="{{ $types->slug }}">{{ $types->name }}</x-dropdown.select.option>
                            @endforeach

                        </x-dropdown.select>
                        @error('salary_type')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Salary --}}
                    <x-input.input-group x-show="$wire.salary_type">
                        <x-slot:label for="salary">
                            <span class="text-red-500">*</span><span
                                x-text="$wire.salary_type !== 'per_day' ? 'Salary per month:' : 'Salary per day:'"></span>
                        </x-slot:label>
                        <x-input type="number" id="salary" name="salary" step="0.01" min="0"
                            x-bind:placeholder="$wire.salary_type !== 'per_day' ? 'Salary per month' : 'Salary per day'"
                            wire:model.blur='salary' />
                        @error('salary')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>
                </div>

                {{-- Access Level --}}
                <x-input.input-group>
                    <x-slot:label for="access_level">
                        <span class="text-red-500">*</span>Access Level
                    </x-slot:label>
                    <x-dropdown.select id="access_level" wire:model.change='access_level'>
                        <x-dropdown.select.option value="" hidden selected>Select</x-dropdown.select.option>
                        @foreach ($this->access_levels as $level)
                            <x-dropdown.select.option
                                value="{{ $level->slug }}">{{ $level->name }}</x-dropdown.select.option>
                        @endforeach
                    </x-dropdown.select>
                    @error('access_level')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </x-input.input-group>
            </div>
        </div>
        
        <div x-cloak x-show="$wire.mode === 'has_repay_account'" class="flex flex-col gap-9">
            {{-- Search with Phone Number --}}
            <div>
                <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Search with Phone Number</h3>

                <div class="grid grid-cols-1 gap-3">
                    {{-- Phone number --}}
                    <div class="flex flex-col basis-1">
                        <label class="text-xs 2xl:text-sm" for="phone_number"><span class="text-red-500">*</span>Phone Number:</label>
                        <div class="flex flex-row gap-1 2xl:gap-2">
                            <x-dropdown.select wire:model.change="phone_iso">
                                <x-dropdown.select.option value="" disabled
                                    selected>Select</x-dropdown.select.option>
                                @foreach ($this->phone_number_prefixes as $prefix)
                                    <x-dropdown.select.option
                                        value="{{ $prefix['code'] }}">{{ $prefix['dial_code'] . ' (' . $prefix['code'] . ')' }}</x-dropdown.select.option>
                                @endforeach
                            </x-dropdown.select>
                            <x-input wire:model.blur="phone_number" id="phone_number" type="text"
                                placeholder="912-345-6789" class="grow"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                x-bind:disabled="$wire.phone_iso === ''">
                                <x-slot:icon>
                                    <x-icon.phone />
                                </x-slot:icon>
                            </x-input>
                        </div>
                        @error('phone_iso')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        @error('phone_number')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Employee Details --}}
            <div>
                <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Employee Details</h3>
                <div class="grid grid-cols-3 gap-3 mb-3">

                    {{-- Position --}}
                    <x-input.input-group>
                        <x-slot:label for="position">
                            <span class="text-red-500">*</span>Position
                        </x-slot:label>
                        <x-input type="text" id="position" name="position" placeholder="Position"
                            wire:model.blur='position' />
                        @error('position')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Salary Type --}}
                    <x-input.input-group>
                        <x-slot:label for="salary_type">
                            <span class="text-red-500">*</span>Salary Type
                        </x-slot:label>
                        <x-dropdown.select wire:model.change="salary_type">
                            <x-dropdown.select.option value="" hidden
                                selected>Select</x-dropdown.select.option>
                            @foreach ($this->salary_types as $types)
                                <x-dropdown.select.option
                                    value="{{ $types->slug }}">{{ $types->name }}</x-dropdown.select.option>
                            @endforeach

                        </x-dropdown.select>
                        @error('salary_type')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Salary --}}
                    <x-input.input-group x-show="$wire.salary_type">
                        <x-slot:label for="salary">
                            <span class="text-red-500">*</span><span
                                x-text="$wire.salary_type !== 'per_day' ? 'Salary per month:' : 'Salary per day:'"></span>
                        </x-slot:label>
                        <x-input type="number" id="salary" name="salary" step="0.01" min="0"
                            x-bind:placeholder="$wire.salary_type !== 'per_day' ? 'Salary per month' : 'Salary per day'"
                            wire:model.blur='salary' />
                        @error('salary')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>
                </div>

                {{-- Access Level --}}
                <x-input.input-group>
                    <x-slot:label for="access_level">
                        <span class="text-red-500">*</span>Access Level
                    </x-slot:label>
                    <x-dropdown.select id="access_level" wire:model.change='access_level'>
                        <x-dropdown.select.option value="" hidden selected>Select</x-dropdown.select.option>
                        @foreach ($this->access_levels as $level)
                            <x-dropdown.select.option
                                value="{{ $level->slug }}">{{ $level->name }}</x-dropdown.select.option>
                        @endforeach
                    </x-dropdown.select>
                    @error('access_level')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </x-input.input-group>
            </div>
        </div>

        {{-- Success Message --}}
        <div x-cloak x-show="$wire.success_message" @click="$wire.success_message = ''""
            class="absolute inset-0 mw-100 mh-100 z-50 bg-opacity-50 bg-black flex items-center justify-center">
            <div class="p-5 bg-white border-2 border-rp-green-600 rounded-md w-96">
                <div class="flex flex-row justify-between items-center p-4">
                    <p class="text-rp-green-600 text-md">Success!</p>
                    <x-icon.check />
                </div>
                <hr>
                <div class="p-4 text-rp-green-600 font-bold">
                    <span x-text="$wire.success_message"></span>
                </div>
                <hr>
                <small class="text-xs">Click anywhere inside this box to continue</small>
            </div>
        </div>
        {{-- Error Message --}}
        <div x-cloak x-show="$wire.error_message" @click="$wire.error_message = ''""
            class="absolute inset-0 mw-100 mh-100 z-50 bg-opacity-50 bg-black flex items-center justify-center">
            <div class="p-5 bg-white border-2 border-red-600 rounded-md w-96">
                <div class="flex flex-row justify-between items-center p-4">
                    <p class="text-red-600 text-md">Error: Something went wrong</p>
                    <x-icon.error />
                </div>
                <hr>
                <div class="p-4 text-red-600 font-bold">
                    <span x-text="$wire.error_message"></span>
                </div>
                <hr>
                <small class="text-xs">Click anywhere inside this box to continue</small>
            </div>
        </div>
    </x-main.content>




    {{-- Summary --}}
    <x-layout.summary>
        <x-slot:profile>
            <x-layout.summary.profile :image_path="url('images/user/default-avatar.png')">
                <x-slot:info_block_middle>
                    @if ($mode === 'no_repay_account')
                        {{ $first_name . ' ' . $middle_name . ' ' . $surname . ($suffix ? ', ' . $suffix : '') }}
                    @elseif($mode === 'has_repay_account')
                        {{ $this->format_phone_number($phone_number, $phone_iso) }}
                    @endif
                </x-slot:info_block_middle>
                <x-slot:info_block_bottom>
                    {{ $position ?? '' }}
                </x-slot:info_block_bottom>
            </x-layout.summary.profile>
        </x-slot:profile>
        <x-slot:body>
            <x-layout.summary.section title="Contact Information">
                <x-slot:data>
                    <x-layout.summary.label-data>
                        <x-slot:label>
                            Phone Number
                        </x-slot:label>
                        <x-slot:data>
                            {{ $phone_number ? $this->format_phone_number($phone_number, $phone_iso) : '-' }}
                        </x-slot:data>
                    </x-layout.summary.label-data>
                    @if ($mode === 'no_repay_account')
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Telephone Number:
                            </x-slot:label>
                            <x-slot:data>
                                {{ $telephone_number ? $this->format_phone_number($telephone_number, $phone_iso) : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Email address:
                            </x-slot:label>
                            <x-slot:data>
                                {{ $email ?? '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    @endif
                </x-slot:data>
            </x-layout.summary.section>
            <template x-if="$wire.mode === 'no_repay_account'">
                <x-layout.summary.section title="Personal Information">
                    <x-slot:data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Full Name
                            </x-slot:label>
                            <x-slot:data>
                                @php
                                    $fullName = trim(
                                        $first_name .
                                            ' ' .
                                            $middle_name .
                                            ' ' .
                                            $surname .
                                            ($suffix ? ', ' . $suffix : ''),
                                    );
                                @endphp
    
                                {{ blank($fullName) ? '-' : $fullName }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Gender
                            </x-slot:label>
                            <x-slot:data>
                                {{ $gender ? ucfirst($gender) : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Nationality
                            </x-slot:label>
                            <x-slot:data>
                                {{ $nationality !== '' ? $nationality : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Birthdate
                            </x-slot:label>
                            <x-slot:data>
                                {{ $birthdate !== '' ? $birthdate : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Birthplace
                            </x-slot:label>
                            <x-slot:data>
                                {{ $birthplace !== '' ? $birthplace : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Mother's Maiden Name
                            </x-slot:label>
                            <x-slot:data>
                                {{ $mothers_maiden_name !== '' ? $mothers_maiden_name : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    </x-slot:data>
                </x-layout.summary.section>
            </template>
            <x-layout.summary.section title="Employee Details">
                <x-slot:data>
                    <x-layout.summary.label-data>
                        <x-slot:label>
                            Position
                        </x-slot:label>
                        <x-slot:data>
                            {{ $position ?? '-' }}
                        </x-slot:data>
                    </x-layout.summary.label-data>

                    <x-layout.summary.label-data>
                        <x-slot:label>
                            Salary type
                        </x-slot:label>
                        <x-slot:data>
                            {{ empty($salary_type) ? '-' : ($salary_type !== 'per_day' ? 'Per Cutoff' : 'Per Day') }}

                        </x-slot:data>
                    </x-layout.summary.label-data>
                    @if (!empty($salary_type))
                        <x-layout.summary.label-data>
                            @if ($salary_type === 'per_day')
                                <x-slot:label>
                                    Salary per day
                                </x-slot:label>
                            @elseif($salary_type === 'per_cutoff')
                                <x-slot:label>
                                    Salary per month
                                </x-slot:label>
                            @else
                                <x-slot:label>
                                    Salary per month
                                </x-slot:label>
                            @endif
                            <x-slot:data>
                                {{ $salary !== '' ? '₱ ' . number_format($salary, 2) : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    @endif

                    <x-layout.summary.label-data>
                        <x-slot:label>
                            Access Level
                        </x-slot:label>
                        <x-slot:data>
                            {{ $access_level ? ucwords(str_replace('_', ' ', $access_level)) : '-' }}
                        </x-slot:data>
                    </x-layout.summary.label-data>
                </x-slot:data>
            </x-layout.summary.section>
        </x-slot:body>
        <x-slot:action>
            <div class="flex flex-col mb-5">
                {{-- I agree  --}}
                <div class="flex items-center gap-3">
                    <x-input wire:model.live="isAgree" type="checkbox" id="agree" />
                    <label for="agree" class="cursor-pointer text-rp-neutral-600">I agree that the above
                        information is correct.</label>
                </div>
                @error('isAgree')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @if ($mode === 'no_repay_account')
                <div class="flex flex-col gap-3">
                    <x-button.filled-button wire:click="add_employee_no_repay_account"
                        x-bind:disabled="$wire.isAgree === false" size="lg">send</x-button.filled-button>

                    <x-button.outline-button size="lg"
                        href="{{ route('merchant.financial-transactions.employees.index', ['merchant' => $merchant->account_number]) }}">cancel</x-button.outline-button>
                </div>
            @elseif ($mode === 'has_repay_account')
                <div class="flex flex-col gap-3">
                    @if ($notified)
                        <x-button.filled-button @click="$wire.isEmployeeResendModalVisible=true" x-bind:disabled="$wire.isAgree === false"
                            size="lg">send</x-button.filled-button>
                    @else
                        <x-button.filled-button wire:click="add_employee_has_repay_account" x-bind:disabled="$wire.isAgree === false"
                            size="lg">send</x-button.filled-button>
                    @endif
                    <x-button.outline-button size="lg"
                        href="{{ route('merchant.financial-transactions.employees.index', ['merchant' => $merchant->account_number]) }}">cancel</x-button.outline-button>
                </div>
                {{-- Confirmation Modal --}}
                <x-modal x-model="$wire.isEmployeeResendModalVisible">
                    <x-modal.confirmation-modal>
                        <x-slot:title>Resend Invite?</x-slot:title>
                        <x-slot:message>
                            This user has already received an invitation. Proceeding will resend the invitation.
                        </x-slot:message>
                        <x-slot:action_buttons>
                            <x-button.outline-button class="flex-1"
                                @click="$wire.isEmployeeResendModalVisible=false;">cancel</x-button.outline-button>
                            <x-button.filled-button class="flex-1" wire:click="add_employee_has_repay_account;">proceed</x-button.filled-button>
                        </x-slot:action_buttons>
                    </x-modal.confirmation-modal>
                </x-modal>
            @endif
        </x-slot:action>
    </x-layout.summary>

    <x-loader.black-screen wire:loading wire:target="add_employee_has_repay_account,add_employee_no_repay_account,mode">
        <x-loader.clock />
    </x-loader.black-screen>
</div>
