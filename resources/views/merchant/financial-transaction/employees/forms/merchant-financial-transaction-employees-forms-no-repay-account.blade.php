{{-- COMPONENT TO BE FOR LATER --}}

<div class="">
    {{-- Contact Information --}}
    <div class="mb-9">
        <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Contact Information</h3> 
        
        <div class="grid grid-cols-3 gap-3">
            {{-- Phone number --}}
            <div class="flex flex-col basis-1">
                <label class="text-xs 2xl:text-sm" for="phone_number">Phone Number:</label>
                <div class="flex flex-row gap-1 2xl:gap-2">
                    <x-dropdown.select wire:model="country_code">
                        {{-- @foreach ($country_code_options as $country_code)
                             
                         @endforeach --}}
                    </x-dropdown.select>
                    <x-input wire:model="phone_number" id="phone-number" type="text" placeholder="(+63) 912-345-6789">
                        <x-slot:icon>
                            <x-icon.phone />
                        </x-slot:icon>
                    </x-input>
                </div>
            </div>  

            {{-- Telephone number --}}
            <x-input.input-group>
                <x-slot:label for="telephone_number">
                    Telephone Number:
                </x-slot:label>
                <x-input type="text" id="telephone_number" name="telephone_number"  placeholder="Tel. No">
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
                <x-input type="text" id="email_address" name="email_address" placeholder="Email address">
                    <x-slot:icon>
                        <x-icon.mail />
                    </x-slot:icon>    
                </x-input>
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
                <x-input type="text" id="first_name" name="first_name" placeholder="First Name" />
            </x-input.input-group>

            {{-- Middle name --}}
            <x-input.input-group>
                <x-slot:label for="middle_name">
                    Middle Name:
                </x-slot:label>
                <x-input type="text" id="middle_name" name="middle_name" placeholder="Middle Name" />
            </x-input.input-group> 

            {{-- Surname --}}
            <x-input.input-group>
                <x-slot:label>
                    Surname
                </x-slot:label>
                <x-input type="text" wire:model="surname" id="email_address" name="email_address" placeholder="Surname" />
            </x-input.input-group> 

            {{-- Suffix --}}
            <x-input.input-group>
                <x-slot:label for="telephone_number">
                    Suffix:
                </x-slot:label>
                <x-input type="text" id="telephone_number" name="telephone_number" placeholder="Suffix" />
            </x-input.input-group>

            {{-- Gender --}}
            <x-input.input-group>
                <x-slot:label for="gender">
                    Gender
                </x-slot:label>
                <x-dropdown.select wire:model="gender">
                    <x-dropdown.select.option value="" disabled selected>Select</x-dropdown.select.option>
                    <x-dropdown.select.option value="Male">Male</x-dropdown.select.option>
                    <x-dropdown.select.option value="Female">Female</x-dropdown.select.option>
                </x-dropdown.select>
            </x-input.input-group>

            {{-- Nationality --}}
            <x-input.input-group>
                <x-slot:label for="nationality">
                    Nationality
                </x-slot:label>
                <x-dropdown.select placeholder="Select">
                    <x-dropdown.select.option value="" disabled selected>Select</x-dropdown.select.option>
                    <x-dropdown.select.option value="Filipino">Filipino</x-dropdown.select.option>
                    <x-dropdown.select.option value="Singaporean">Singaporean</x-dropdown.select.option>
                </x-dropdown.select>
            </x-input.input-group>

            {{-- Birthdate --}}
            <x-input.input-group>
                <x-slot:label for="birthdate">
                    Birthdate
                </x-slot:label>
                <x-input type="date" id="birthdate" name="birthplace"/>
            </x-input.input-group>

            {{-- Birthplace --}}
            <x-input.input-group>
                <x-slot:label for="birthplace">
                    Birthplace
                </x-slot:label>
                <x-input type="text" id="birthplace" name="birthplace" placeholder="Birthplace"/>
            </x-input.input-group>
        </div>

        {{-- Mother's Maiden Name --}}
        <x-input.input-group>
            <x-slot:label for="maiden_name">
                Mother's Maiden Name
            </x-slot:label>
            <x-input type="text" id="maiden_name" name="maiden_name" placeholder="Mother's Maiden Name" />
        </x-input.input-group>
    </div>

    {{-- Current Address --}}
    <div class="mb-9">
        <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Current Address</h3> 
        <div class="grid grid-cols-3 gap-3">
            {{-- Unit --}}
            <x-input.input-group>
                <x-slot:label for="unit_current">
                    Unit
                </x-slot:label>
                <x-input type="text" id="unit_current" name="unit_current" placeholder="Unit" />
            </x-input.input-group>

            {{-- Street Address --}}
            <x-input.input-group>
                <x-slot:label for="street_address">
                    Street Address:
                </x-slot:label>
                <x-input type="text" id="street_address" name="street_address" placeholder="Street Address" />
            </x-input.input-group> 

            {{-- Municipality / City --}}
            <x-input.input-group>
                <x-slot:label>
                    Municipality / City
                </x-slot:label>
                <x-input type="text" id="municipality" name="municipality" placeholder="Municipality / City" />
            </x-input.input-group> 

            {{-- Province / State --}}
            <x-input.input-group class="flex-1">
                <x-slot:label for="province">
                    Province / State
                </x-slot:label>
                <x-dropdown.select id="province" >
                    <x-dropdown.select.option value="" disabled selected>Select</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_1">Example 1</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_2">Example 2</x-dropdown.select.option>
                </x-dropdown.select>
            </x-input.input-group>

            {{-- Country --}}
            <x-input.input-group>
                <x-slot:label for="gender">
                    Country
                </x-slot:label>
                <x-dropdown.select>
                    <x-dropdown.select.option value="" disabled selected>Select</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_1">Example 1</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_2">Example 2</x-dropdown.select.option>
                </x-dropdown.select>
            </x-input.input-group>

            {{-- Zipcode --}}
            <x-input.input-group>
                <x-slot:label for="zipcode">
                    Zipcode
                </x-slot:label>
                <x-input type="text" id="zipcode" />
            </x-input.input-group>
        </div>

    </div>

    {{-- Permanent Address --}}
    <div class="mb-9">
        <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Permanent Address</h3> 
        <div class="mb-3 flex flex-row items-center gap-3">
            <x-input type="checkbox" id="same_as_current_address" />
            <label for="same_as_current_address" class="text-rp-neutral-600">Same as current address</label>
        </div>
        <div class="grid grid-cols-3 gap-3">
            {{-- Unit --}}
            <x-input.input-group>
                <x-slot:label for="unit_permanent">
                    Unit
                </x-slot:label>
                <x-input type="text" id="unit_permanent" name="unit_permanent" placeholder="Unit" />
            </x-input.input-group>

            {{-- Street Address --}}
            <x-input.input-group>
                <x-slot:label for="street_address">
                    Street Address:
                </x-slot:label>
                <x-input type="text" id="street_address" name="street_address" placeholder="Street Address" />
            </x-input.input-group> 

            {{-- Municipality / City --}}
            <x-input.input-group>
                <x-slot:label>
                    Municipality / City
                </x-slot:label>
                <x-input type="text" id="municipality" name="municipality" placeholder="Municipality / City" />
            </x-input.input-group> 

            {{-- Province / State --}}
            <x-input.input-group>
                <x-slot:label for="province">
                    Province / State
                </x-slot:label>
                <x-dropdown.select id="province">
                    <x-dropdown.select.option value="" disabled selected>Select</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_1">Example 1</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_2">Example 2</x-dropdown.select.option>
                </x-dropdown.select>
            </x-input.input-group>

            {{-- Country --}}
            <x-input.input-group>
                <x-slot:label for="test">
                    Country
                </x-slot:label>
                <x-dropdown.select>
                    <x-dropdown.select.option value="" disabled selected>Select</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_1">Example 1</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_2">Example 2</x-dropdown.select.option>
                </x-dropdown.select>
            </x-input.input-group>

            {{-- Zipcode --}}
            <x-input.input-group>
                <x-slot:label for="zipcode">
                    Zipcode
                </x-slot:label>
                <x-input type="text" id="zipcode" />
            </x-input.input-group>
            
        </div>

    </div>

    {{-- Employee Address --}}
    <div class="mb-9">
        <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Employee Details</h3> 
        <div class="grid grid-cols-2 gap-3">
            {{-- Salary type --}}
            <x-input.input-group>
                <x-slot:label>
                    Salary type
                </x-slot:label>
                <x-dropdown.select id="salary_type" name="salary_type">
                    <x-dropdown.select.option value="" disabled selected>Select</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_1">Example 1</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_2">Example 2</x-dropdown.select.option>
                </x-dropdown.select>
            </x-input.input-group>

            {{-- Salary per day --}}
            <x-input.input-group>
                <x-slot:label for="salary_per_day">
                    Salary per day:
                </x-slot:label>
                <x-input type="number" id="salary_per_day" name="salary_per_day" placeholder="Salary per day" />
            </x-input.input-group> 

            {{--Position --}}
            <x-input.input-group>
                <x-slot:label for="position">
                    Position
                </x-slot:label>
                <x-input type="text" id="position" name="position" placeholder="Position" />
            </x-input.input-group> 

            {{-- Access Level --}}
            <x-input.input-group>
                <x-slot:label for="access_level">
                    Access Level
                </x-slot:label>
                <x-dropdown.select id="access_level">
                    <x-dropdown.select.option value="" disabled selected>Select</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_1">Example 1</x-dropdown.select.option>
                    <x-dropdown.select.option value="example_2">Example 2</x-dropdown.select.option>
                </x-dropdown.select>
            </x-input.input-group>

        </div>
    </div>

  
</div>
