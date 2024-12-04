<div class="flex h-full" x-data="{ isEmployeeResendModalVisible: false, }">
    <x-main.content x-data="{
            mode: $wire.entangle('mode'),
            successAddEmployeeMsg: $wire.entangle('successAddEmployeeMsg'),
            failedAddEmployeeMsg: $wire.entangle('failedAddEmployeeMsg'),
            handleCloseMessageModal() {
                failedAddEmployeeMsg = '';
                successAddEmployeeMsg = '';
                $wire.handleCloseMessageModal();
            },
            mode_no_repay_account() {
                return $wire.mode == 'no_repay_account';
            },
            mode_has_repay_account() {
                return $wire.mode == 'has_repay_account';
            }
        }" class="flex-1 h-full overflow-auto !px-[18.5px]">

        <x-main.title class="mb-8">
            Add Employee
        </x-main.title>

        <div class="grid grid-cols-2 gap-3 mb-9">
            <x-card.select-card :title="'No RePay Account'" :description="'Select this if your employee doesn’t have a RePay account.'" x-model="mode_no_repay_account()"
                @click="$wire.mode = 'no_repay_account';" />
            <x-card.select-card :title="'Has RePay Account'" :description="'Select this if your employee does have a RePay Account.'" x-model="mode_has_repay_account()"
                @click="$wire.mode = 'has_repay_account';" />
        </div>


        <div x-show="$wire.mode === 'no_repay_account'">
            {{-- Contact Information --}}
            <div class="mb-9">
                <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Contact Information</h3>

                <div class="grid grid-cols-3 gap-3">
                    {{-- Phone number --}}
                    <div class="flex flex-col basis-1">
                        <label class="text-xs 2xl:text-sm" for="no_repay_phone_number">Phone Number:</label>
                        <div class="flex flex-row gap-1 2xl:gap-2">
                            <x-dropdown.select wire:model.live="no_repay_phone_number_prefix">
                                <x-dropdown.select.option value="" disabled
                                    selected>Select</x-dropdown.select.option>
                                @foreach ($phone_number_prefixes as $prefixes)
                                    <x-dropdown.select.option
                                        value="{{ $prefixes['dial_code'] }}">{{ $prefixes['dial_code'] }}</x-dropdown.select.option>
                                @endforeach
                            </x-dropdown.select>
                            <x-input id="no_repay_phone_number" type="text" placeholder="912-345-6789"
                                class="flex-1" wire:model.blur="no_repay_phone_number"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" :disabled="$no_repay_phone_number_prefix == ''">
                                <x-slot:icon>
                                    <x-icon.phone />
                                </x-slot:icon>
                            </x-input>

                        </div>
                        @error('no_repay_phone_number')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if ($noRepayPhoneNumberErr)
                            <p class="text-sm text-red-600">{{ $noRepayPhoneNumberErr }}</p>
                        @endif
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
                            Email address:
                        </x-slot:label>
                        <x-input type="text" id="email_address" name="email_address" placeholder="Email address"
                            wire:model.blur='email'>
                            <x-slot:icon>
                                <x-icon.mail />
                            </x-slot:icon>
                        </x-input>
                        @error('email')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if ($emailErr)
                            <p class="text-sm text-red-600">{{ $emailErr }}</p>
                        @endif
                    </x-input.input-group>
                </div>
            </div>


            {{-- Personal Information --}}
            <div class="mb-9">
                <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Personal Information</h3>
                <div class="grid grid-cols-4 gap-3 mb-3">
                    {{-- First name --}}
                    <x-input.input-group>
                        <x-slot:label for="first_name">
                            First Name:
                        </x-slot:label>
                        <x-input type="text" id="first_name" name="first_name" placeholder="First Name"
                            wire:model.blur='first_name' />
                        @error('first_name')
                            <p class="text-sm text-red-600">{{ $message }}</p>
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
                            Surname
                        </x-slot:label>
                        <x-input type="text" id="surname" name="surname" placeholder="Surname"
                            wire:model.blur="surname" />
                        @error('surname')
                            <p class="text-sm text-red-600">{{ $message }}</p>
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
                        <x-dropdown.select wire:model.blur="gender">
                            <x-dropdown.select.option value="" disabled
                                selected>Select</x-dropdown.select.option>
                            <x-dropdown.select.option value="Male">Male</x-dropdown.select.option>
                            <x-dropdown.select.option value="Female">Female</x-dropdown.select.option>
                        </x-dropdown.select>
                        @error('gender')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Nationality --}}
                    <x-input.input-group>
                        <x-slot:label for="nationality">
                            Nationality
                        </x-slot:label>
                        <x-dropdown.select wire:model.blur="nationality">
                            <x-dropdown.select.option value="" disabled
                                selected>Select</x-dropdown.select.option>
                            @foreach ($nationalities as $race)
                                <x-dropdown.select.option
                                    value="{{ $race }}">{{ $race }}</x-dropdown.select.option>
                            @endforeach
                        </x-dropdown.select>
                        @error('nationality')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Birthdate --}}
                    <x-input.input-group x-data="{
                        maxDate: new Date().toISOString().split('T')[0],
                        init() {
                            this.calculateMaxDate();
                        },
                        calculateMaxDate() {
                            let currentDate = new Date();
                            let currentYear = currentDate.getFullYear();
                            let endOfYear = new Date(currentYear, 11, new Date(currentYear, 12, 0).getDate(), 23, 59, 59); // Get the last day of December
                            this.maxDate = endOfYear.toISOString().split('T')[0];
                        }
                    }">
                        <x-slot:label for="birthdate">
                            Birthdate
                        </x-slot:label>
                        <x-input type="date" id="birthdate" max="maxDate" name="birthplace"
                            wire:model.blur="birthdate" />
                        @error('birthdate')
                            <p class="text-sm text-red-600">{{ $message }}</p>
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
                            <p class="text-sm text-red-600">{{ $message }}</p>
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
                </x-input.input-group>
            </div>

            {{-- Employee Details --}}
            <div class="mb-9">
                <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Employee Details</h3>
                <div class="grid grid-cols-3 gap-3 mb-3">

                    {{-- Position --}}
                    <x-input.input-group>
                        <x-slot:label for="position">
                            Position
                        </x-slot:label>
                        <x-input type="text" id="position" name="position" placeholder="Position"
                            wire:model.blur='no_repay_position' />
                        @error('no_repay_position')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Salary Type --}}
                    <x-input.input-group>
                        <x-slot:label for="no_repay_salary_type">
                            Salary Type
                        </x-slot:label>
                        <x-dropdown.select wire:model.live="no_repay_salary_type">
                            <x-dropdown.select.option value="" disabled
                                selected>Select</x-dropdown.select.option>
                            @foreach ($salary_types as $types)
                                <x-dropdown.select.option
                                    value="{{ $types->slug }}">{{ $types->name }}</x-dropdown.select.option>
                            @endforeach

                        </x-dropdown.select>
                        @error('no_repay_salary_type')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Salary --}}

                    @if (!blank($no_repay_salary_type))
                        <x-input.input-group>
                            <x-slot:label for="salary">
                                {{ $no_repay_salary_type !== 'per_day' ? 'Salary per month:' : 'Salary per day:' }}
                            </x-slot:label>
                            <x-input type="number" id="salary" name="salary"
                                placeholder="{{ $no_repay_salary_type !== 'per_day' ? 'Salary per month' : 'Salary per day' }}"
                                wire:model.blur='no_repay_salary' />
                            @error('no_repay_salary')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </x-input.input-group>
                    @endif



                </div>

                {{-- Access Level --}}
                <x-input.input-group>
                    <x-slot:label for="access_level">
                        Access Level
                    </x-slot:label>
                    <x-dropdown.select id="access_level" wire:model.blur='no_repay_access_level'>
                        <x-dropdown.select.option value="" disabled
                            selected>Select</x-dropdown.select.option>
                        @foreach ($accessLevels as $level)
                            <x-dropdown.select.option
                                value="{{ $level['slug'] }}">{{ $level['name'] }}</x-dropdown.select.option>
                        @endforeach
                    </x-dropdown.select>
                    @error('no_repay_access_level')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </x-input.input-group>
            </div>


        </div>
        
        <div x-show="$wire.mode === 'has_repay_account'">
            {{-- Search with Phone Number --}}
            <div class="mb-9">
                <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Search with Phone Number</h3>

                <div class="grid grid-cols-1 gap-3">
                    {{-- Phone number --}}
                    <div class="flex flex-col basis-1">
                        <label class="text-xs 2xl:text-sm" for="has_repay_phone_number">Phone Number:</label>
                        <div class="flex flex-row gap-1 2xl:gap-2">
                            <x-dropdown.select wire:model.live="has_repay_phone_number_prefix">
                                <x-dropdown.select.option value="" disabled
                                    selected>Select</x-dropdown.select.option>
                                @foreach ($phone_number_prefixes as $prefixes)
                                    <x-dropdown.select.option
                                        value="{{ $prefixes['dial_code'] }}">{{ $prefixes['dial_code'] }}</x-dropdown.select.option>
                                @endforeach
                            </x-dropdown.select>
                            <x-input wire:model.live="has_repay_phone_number" id="has_repay_phone_number"
                                type="text" placeholder="912-345-6789" class="grow"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" :disabled="$has_repay_phone_number_prefix == ''">
                                <x-slot:icon>
                                    <x-icon.phone />
                                </x-slot:icon>
                            </x-input>
                        </div>
                        @error('has_repay_phone_number')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if ($hasRepayPhoneNumberErr)
                            <p class="text-sm text-red-600">{{ $hasRepayPhoneNumberErr }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Employee Details --}}
            <div class="mb-9">
                <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Employee Details</h3>
                <div class="grid grid-cols-3 gap-3 mb-3">

                    {{-- Position --}}
                    <x-input.input-group>
                        <x-slot:label for="has_repay_position">
                            Position
                        </x-slot:label>
                        <x-input type="text" id="has_repay_position" name="has_repay_position"
                            placeholder="Position" wire:model.blur='has_repay_position' />
                        @error('has_repay_position')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>


                    {{-- Salary Type --}}
                    <x-input.input-group>
                        <x-slot:label for="has_repay_salary_type">
                            Salary Type
                        </x-slot:label>
                        <x-dropdown.select wire:model.live="has_repay_salary_type">
                            <x-dropdown.select.option value="" disabled
                                selected>Select</x-dropdown.select.option>
                            @foreach ($salary_types as $types)
                                <x-dropdown.select.option
                                    value="{{ $types->slug }}">{{ $types->name }}</x-dropdown.select.option>
                            @endforeach

                        </x-dropdown.select>
                        @error('has_repay_salary_type')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Salary --}}

                    @if (!blank($has_repay_salary_type))
                        <x-input.input-group>
                            <x-slot:label for="has_repay_salary">
                                {{ $has_repay_salary_type !== 'per_day' ? 'Salary per month:' : 'Salary per day:' }}
                            </x-slot:label>
                            <x-input type="number" id="has_repay_salary" name="has_repay_salary"
                                placeholder="{{ $has_repay_salary_type !== 'per_day' ? 'Salary per month' : 'Salary per day' }}"
                                wire:model.blur='has_repay_salary' />
                            @error('has_repay_salary')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </x-input.input-group>
                    @endif

                </div>

                {{-- Access Level --}}
                <x-input.input-group>
                    <x-slot:label for="has_repay_access_level">
                        Access Level
                    </x-slot:label>
                    <x-dropdown.select id="has_repay_access_level" wire:model.blur='has_repay_access_level'>
                        <x-dropdown.select.option value="" disabled
                            selected>Select</x-dropdown.select.option>
                        @foreach ($accessLevels as $level)
                            <x-dropdown.select.option
                                value="{{ $level['slug'] }}">{{ $level['name'] }}</x-dropdown.select.option>
                        @endforeach
                    </x-dropdown.select>
                    @error('has_repay_access_level')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </x-input.input-group>
            </div>


        </div>


        <div x-cloak x-show="successAddEmployeeMsg"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div @click.away="handleCloseMessageModal()"
                class="relative flex items-center justify-center w-64 px-4 py-3 bg-white rounded-lg h-44">
                <div class="absolute cursor-pointer top-3 right-4" @click="handleCloseMessageModal()">
                    <x-icon.close />
                </div>
                <p x-text="successAddEmployeeMsg" class="text-xl font-bold text-center text-rp-green-600"></p>
            </div>
        </div>

        <div x-cloak x-show="failedAddEmployeeMsg"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div @click.away="handleCloseMessageModal()"
                class="relative flex items-center justify-center w-64 px-4 py-3 bg-white rounded-lg h-44">
                <div class="absolute cursor-pointer top-3 right-4" @click="handleCloseMessageModal()">
                    <x-icon.close />
                </div>
                {{-- <p class="font-semibold text-center text-red-600">Form submission failed</p> --}}
                {{-- <p x-text="failedAddEmployeeMsg" class="text-lg font-bold text-center "></p> --}}
                <p x-text="failedAddEmployeeMsg" class="text-xl font-bold text-center text-red-600"></p>

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
                        @php
                            $formatted_has_repay_phone_number =
                                $has_repay_phone_number_prefix . $has_repay_phone_number;
                        @endphp
                        {{ $formatted_has_repay_phone_number && strlen($formatted_has_repay_phone_number) >= 13
                            ? '(' .
                                substr($formatted_has_repay_phone_number, 0, 3) .
                                ') ' .
                                substr($formatted_has_repay_phone_number, 3, 3) .
                                '-' .
                                substr($formatted_has_repay_phone_number, 6, 3) .
                                '-' .
                                substr($formatted_has_repay_phone_number, 9)
                            : '' }}
                    @endif
                </x-slot:info_block_middle>
                <x-slot:info_block_bottom>
                    @if ($mode === 'no_repay_account')
                        {{ $no_repay_position !== '' ? $no_repay_position : '' }}
                    @elseif($mode === 'has_repay_account')
                        {{ $has_repay_position !== '' ? $has_repay_position : '' }}
                    @endif
                </x-slot:info_block_bottom>
            </x-layout.summary.profile>
        </x-slot:profile>
        <x-slot:body>
            @if ($mode === 'no_repay_account')
                <x-layout.summary.section title="Contact Information" color="primary">
                    <x-slot:data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Phone Number
                            </x-slot:label>
                            <x-slot:data>
                                @php
                                    $formatted_no_repay_phone_number =
                                        $no_repay_phone_number_prefix . $no_repay_phone_number;
                                @endphp
                                {{ $formatted_no_repay_phone_number && strlen($formatted_no_repay_phone_number) >= 13
                                    ? '(' .
                                        substr($formatted_no_repay_phone_number, 0, 3) .
                                        ') ' .
                                        substr($formatted_no_repay_phone_number, 3, 3) .
                                        '-' .
                                        substr($formatted_no_repay_phone_number, 6, 3) .
                                        '-' .
                                        substr($formatted_no_repay_phone_number, 9)
                                    : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Telephone Number:
                            </x-slot:label>
                            <x-slot:data>
                                {{ $telephone_number && strlen($telephone_number) >= 10
                                    ? '(+' .
                                        substr($telephone_number, 0, 2) .
                                        ') ' .
                                        substr($telephone_number, 2, 3) .
                                        '-' .
                                        substr($telephone_number, 5, 3) .
                                        '-' .
                                        substr($telephone_number, 8)
                                    : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Email address:
                            </x-slot:label>
                            <x-slot:data>
                                {{ $email !== '' ? $email : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    </x-slot:data>
                </x-layout.summary.section>
                <x-layout.summary.section title="Personal Information" color="primary">
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
                                {{ $gender !== '' ? $gender : '-' }}
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
                <x-layout.summary.section title="Employee Details" color="primary">
                    <x-slot:data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Position
                            </x-slot:label>
                            <x-slot:data>
                                {{ $no_repay_position !== '' ? $no_repay_position : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Salary type
                            </x-slot:label>
                            <x-slot:data>
                                {{ blank($no_repay_salary_type) ? '-' : ($no_repay_salary_type !== 'per_day' ? 'Per Cutoff' : 'Per Day') }}

                            </x-slot:data>
                        </x-layout.summary.label-data>
                        @if (!blank($no_repay_salary_type))
                            <x-layout.summary.label-data>
                                @if ($no_repay_salary_type === 'per_day')
                                    <x-slot:label>
                                        Salary per day
                                    </x-slot:label>
                                @elseif($no_repay_salary_type === 'per_cutoff')
                                    <x-slot:label>
                                        Salary per month
                                    </x-slot:label>
                                @else
                                    <x-slot:label>
                                        Salary per month
                                    </x-slot:label>
                                @endif
                                <x-slot:data>
                                    {{ $no_repay_salary !== '' ? '₱ ' . number_format($no_repay_salary, 2) : '-' }}
                                </x-slot:data>
                            </x-layout.summary.label-data>
                        @endif

                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Access Level
                            </x-slot:label>
                            <x-slot:data>
                                {{ $no_repay_access_level !== '' ? $access_level_no_repay['name'] : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    </x-slot:data>
                </x-layout.summary.section>
            @elseif ($mode === 'has_repay_account')
                <x-layout.summary.section title="Contact Information" color="primary">
                    <x-slot:data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Phone Number
                            </x-slot:label>
                            <x-slot:data>
                                @php
                                    $formatted_has_repay_phone_number =
                                        $has_repay_phone_number_prefix . $has_repay_phone_number;
                                @endphp
                                {{ $formatted_has_repay_phone_number && strlen($formatted_has_repay_phone_number) >= 13
                                    ? '(' .
                                        substr($formatted_has_repay_phone_number, 0, 3) .
                                        ') ' .
                                        substr($formatted_has_repay_phone_number, 3, 3) .
                                        '-' .
                                        substr($formatted_has_repay_phone_number, 6, 3) .
                                        '-' .
                                        substr($formatted_has_repay_phone_number, 9)
                                    : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    </x-slot:data>
                </x-layout.summary.section>
                <x-layout.summary.section title="Employee Details" color="primary">
                    <x-slot:data>
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Position
                            </x-slot:label>
                            <x-slot:data>
                                {{ $has_repay_position !== '' ? $has_repay_position : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                        {{-- <x-layout.summary.label-data>
                            <x-slot:label>
                                Salary type
                            </x-slot:label>
                            <x-slot:data>
                                Per day
                            </x-slot:data>
                        </x-layout.summary.label-data> --}}
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Salary type
                            </x-slot:label>
                            <x-slot:data>
                                {{ blank($has_repay_salary_type) ? '-' : ($has_repay_salary_type !== 'per_day' ? 'Per Cutoff' : 'Per Day') }}

                            </x-slot:data>
                        </x-layout.summary.label-data>
                        @if (!blank($has_repay_salary_type))
                            <x-layout.summary.label-data>
                                @if ($has_repay_salary_type === 'per_day')
                                    <x-slot:label>
                                        Salary per day
                                    </x-slot:label>
                                @elseif($has_repay_salary_type === 'per_cutoff')
                                    <x-slot:label>
                                        Salary per month
                                    </x-slot:label>
                                @else
                                    <x-slot:label>
                                        Salary per month
                                    </x-slot:label>
                                @endif
                                <x-slot:data>
                                    {{ $has_repay_salary !== '' ? '₱ ' . number_format($has_repay_salary, 2) : '-' }}
                                </x-slot:data>
                            </x-layout.summary.label-data>
                        @endif
                        <x-layout.summary.label-data>
                            <x-slot:label>
                                Access Level
                            </x-slot:label>
                            <x-slot:data>
                                {{ $has_repay_access_level !== '' ? $access_level_has_repay['name'] : '-' }}
                            </x-slot:data>
                        </x-layout.summary.label-data>
                    </x-slot:data>
                </x-layout.summary.section>
            @endif
        </x-slot:body>
        <x-slot:action>
            @if ($mode === 'no_repay_account')
                <div class="flex items-center gap-3 mb-5">
                    {{-- I agree  --}}
                    <x-input wire:model.live="isAgree" type="checkbox" id="agree" />
                    <label for="agree" class="cursor-pointer text-rp-neutral-600">I agree that the above
                        information is correct.</label>
                </div>
                @if ($mode === 'no_repay_account')
                    @if (!empty($noRepayPhoneNumberErr) || !empty($emailErr))
                        @if (!empty($noRepayPhoneNumberErr))
                            <p class="text-sm text-red-600">{{ $noRepayPhoneNumberErr }}</p>
                        @endif
                        @if (!empty($emailErr))
                            <p class="text-sm text-red-600">{{ $emailErr }}</p>
                        @endif
                    @endif
                @endif
                <div class="flex flex-col gap-3">
                    <x-button.filled-button wire:click="handleAddEmployeeSubmit" :disabled="!$isAgree" size="lg"
                        color="primary">send</x-button.filled-button>
                    <x-button.outline-button size="lg" href="{{ route('admin.employees.index') }}"
                        color="primary">cancel</x-button.outline-button>
                </div>
            @elseif ($mode === 'has_repay_account')
                <div class="flex items-center gap-3 mb-5">
                    {{-- I agree  --}}
                    <x-input wire:model.live="isAgree" type="checkbox" id="agree" />
                    <label for="agree" class="cursor-pointer text-rp-neutral-600">I agree that the above
                        information is correct.</label>
                </div>
                @if ($mode === 'no_repay_account')
                    @if (!empty($noRepayPhoneNumberErr) || !empty($emailErr))
                        @if (!empty($noRepayPhoneNumberErr))
                            <p class="text-sm text-red-600">{{ $noRepayPhoneNumberErr }}</p>
                        @endif
                        @if (!empty($emailErr))
                            <p class="text-sm text-red-600">{{ $emailErr }}</p>
                        @endif
                    @endif
                @endif
                <div class="flex flex-col gap-3">

                    @if ($notified)
                        <x-button.filled-button @click="isEmployeeResendModalVisible=true;" :disabled="!$isAgree"
                            size="lg" color="primary">send</x-button.filled-button>
                    @else
                        <x-button.filled-button wire:click="handleAddEmployeeSubmit" :disabled="!$isAgree" size="lg"
                            color="primary">send</x-button.filled-button>
                    @endif
                    <x-button.outline-button size="lg" color="primary">cancel</x-button.outline-button>
                </div>
                {{-- Confirmation Modal --}}
                <x-modal x-model="isEmployeeResendModalVisible">
                    <x-modal.confirmation-modal>
                        <x-slot:title>Confirmation</x-slot:title>
                        <x-slot:message>
                            You already invited this user to be an Employee, do you want to resend?
                        </x-slot:message>
                        <x-slot:action_buttons>
                            <x-button.outline-button class="flex-1" @click="isEmployeeResendModalVisible=false;"
                                color="primary">cancel</x-button.outline-button>
                            <x-button.filled-button class="flex-1" color="primary"
                                wire:click="handleAddEmployeeSubmit;"
                                @click="isEmployeeResendModalVisible=false;">yes</x-button.filled-button>
                        </x-slot:action_buttons>
                    </x-modal.confirmation-modal>
                </x-modal>
            @endif
        </x-slot:action>
    </x-layout.summary>

    <x-loader.black-screen wire:loading.flex wire:target="handleAddEmployeeSubmit" />
</div>
