<div>
    {{-- Search with Phone Number --}}
    <div class="mb-9">
        <h3 class="font-bold text-[19.2px] text-rp-neutral-600 mb-3">Search with Phone Number</h3> 
        
        <div class="grid grid-cols-1 gap-3">
            {{-- Phone number --}}
            <div class="flex flex-col basis-1">
                <label class="text-xs 2xl:text-sm" for="phone_number">Phone Number:</label>
                <div class="flex flex-row gap-1 2xl:gap-2">
                    <x-dropdown.select wire:model="country_code" class="w-1/5">
                         {{-- @foreach ($country_code_options as $country_code)
                             
                         @endforeach --}}
                    </x-dropdown.select>
                    <x-input wire:model="phone_number" id="phone-number" type="text" placeholder="(+63) 912-345-6789" class="grow">
                        <x-slot:icon>
                            <x-icon.phone />
                        </x-slot:icon>
                    </x-input>
                </div>
            </div>  
        </div>
    </div>

    {{-- Employee Details --}}
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
                    <x-dropdown.select.option value="Male">Male</x-dropdown.select.option>
                    <x-dropdown.select.option value="Female">Female</x-dropdown.select.option>
                </x-dropdown.select>
            </x-input.input-group>

        </div>
    </div>

   
</div>
