<x-main.content x-data="{
    showPreviousWorkModal: $wire.entangle('showPreviousWorkModal').live,
    ...operatingDays($wire.service_days),
}">
    <x-main.title class="mb-8">Enlist Service</x-main.title>

    {{-- BASIC INFORMATION --}}
    <div class="bg-white flex flex-col rounded-lg gap-4 px-4 py-5 w-full mt-7">
        <h1 class="font-bold text-xl">Basic Information</h1>

        <x-input.input-group>
            <x-slot:label><span class="text-[#F0146C]">*</span>Service Images</x-slot:label>
            <livewire:components.input.interactive-upload-images :uploaded_images="$service_images" :max="5"
            function="updateServiceImages" />
        </x-input.input-group>
        
        {{-- Service Name --}}
        <x-input.input-group x-data="{ counter: 0 }" x-init="counter = $refs.service_name.value.length">
            <x-slot:label><span class="text-[#F0146C]">*</span>Service Name</x-slot:label>
            <x-input type="text" wire:model="service_name" x-ref="service_name"
                x-on:keyup="counter = $refs.service_name.value.length" maxlength="180" />
            <p class="text-right text-[11px]"><span x-html="counter"></span>/<span
                    x-html="$refs.service_name.maxLength"></span></p>
        </x-input.input-group>

        {{-- Category --}}
        <x-input.input-group>
            <x-slot:label><span class="text-[#F0146C]">*</span>Category</x-slot:label>
            <x-dropdown.select wire:model='category'>
                {{-- options --}}
                <x-dropdown.select.option value="" selected hidden>Select Category</x-dropdown.select.option>
                @foreach ($this->categories as $category_option)
                    <x-dropdown.select.option value="{{ $category_option->id }}"
                        class="!bg-gray-100 !cursor-not-allowed" disabled
                        wire:key='category-option-{{ $category_option->id }}'>
                        {{ $category_option->name }}
                    </x-dropdown.select.option>

                    @foreach ($category_option->sub_categories as $sub_category)
                        <x-dropdown.select.option value="{{ $sub_category->id }}"
                            wire:key='sub-category-option-{{ $sub_category->id }}'>
                            {{ '• ' . $sub_category->name }}
                        </x-dropdown.select.option>
                    @endforeach
                @endforeach
            </x-dropdown.select>
        </x-input.input-group>

        {{-- Service Description --}}
        <x-input.input-group x-data="{ counter: 0 }" x-init="counter = $refs.service_description.value.length">
            <x-slot:label><span class="text-[#F0146C]">*</span>Service Description</x-slot:label>
            <x-input.textarea wire:model='service_description' x-ref='service_description'
                x-on:keyup="counter = $refs.service_description.value.length" maxlength="1000" rows="10" />
            <p class="text-right text-[11px]"><span x-html="counter"></span>/<span
                    x-html="$refs.service_description.maxLength"></span></p>
        </x-input.input-group>
    </div>

    @vite(['resources/js/swiper-operating-days.js','resources/js/leaflet-map.js'])

    {{-- OPERATING DAYS AND TIME SLOTS --}}
    <div class="bg-white flex flex-col rounded-lg gap-4 px-4 py-5 w-full mt-7">
        <div class="flex flex-row justify-between" wire:ignore>
            <h1 class="font-bold text-xl">Operating Days and Time Slots</h1>
            <div class="flex flex-row gap-4">
                {{-- Left arrow --}}
                <div class="operating-days-button-prev cursor-pointer">
                    <x-icon.chevron-left width="36" height="36" />
                </div>
                {{-- Right arrow --}}
                <div class="operating-days-button-next cursor-pointer">
                    <x-icon.chevron-right width="36" height="36"  />
                </div>
            </div>
        </div>
        {{-- Days --}}
        <div class="operatingDaysSlider !overflow-hidden{{-- grid grid-cols-3 w-full text-sm --}}" wire:ignore>
            <div class="swiper-wrapper">
                <template x-for="day in operatingDays" :key="day.id">
                    <div class="swiper-slide p-2">
                        <div class="flex flex-row justify-between items-center mb-4">
                            <div class="flex flex-row items-center gap-3">
                                {{-- <input x-model="day.checked" type="checkbox"
                                    class="rounded-sm scale-125 text-rp-neutral-700"> --}}
                                <x-input type="checkbox" x-model="day.checked" />
                                <p class="text-lg text-rp-neutral-700" x-text="day.dayName"></p>
                            </div>
                            <div class="flex flex-row gap-3">
                                {{-- Add icon --}}
                                <div @click="addTimeslot(day.id)" class="cursor-pointer">
                                    <x-icon.add />
                                </div>
                                {{-- Copy icon --}}
                                <div @click="copyToAll(day.id)" class="cursor-pointer">
                                    <x-icon.copy />
                                </div>
                            </div>
                        </div>
                        {{-- Time slots --}}
                        <div class="flex flex-col max-w-[318px] gap-3"
                            :class="day.checked === false && 'opacity-50 pointer-events-none'">
                            <template x-for="(slot, index) in day.timeslots">
                                <div>
                                    <h5 class="font-medium text-rp-neutral-700 mb-7">Time slot <span
                                            x-text="index+1"></span></h5>

                                    <div class="flex flex-row gap-1 justify-between items-center ">
                                        <div class="relative">
                                            <p class="absolute bottom-[100%]"><span
                                                    class="text-[#E31C79]">*</span>Start time</p>
                                            <x-dropdown.select x-model="slot['start_time'].selected">
                                                <x-dropdown.select.option value="" selected disabled>---</x-dropdown.select.option>
                                                <template x-for="time in slot['start_time'].option">
                                                    <x-dropdown.select.option ::value="time"
                                                        ::selected="time === slot['start_time'].selected"
                                                        x-text="time"></x-dropdown.select.option>
                                                </template>
                                            </x-dropdown.select>

                                        </div>
                                        <div>-</div>
                                        <div class="relative">
                                            <p class="absolute bottom-[100%]"><span
                                                    class="text-[#E31C79]">*</span>End time</p>
                                            <x-dropdown.select x-model="slot['end_time'].selected">
                                                <x-dropdown.select.option value="" selected disabled>---</x-dropdown.select.option>
                                                <template x-for="time in slot['end_time'].option">
                                                    <x-dropdown.select.option ::value="time"
                                                        ::selected="time === slot['end_time'].selected"
                                                        x-text="time"></x-dropdown.select.option>
                                                </template>
                                            </x-dropdown.select>

                                        </div>
                                        {{-- Remove --}}
                                        <div @click="removeTimeslot({ dayNum: day.id, slotIndex: index })"
                                            class="cursor-pointer">
                                            <svg  width="20" height="22"
                                                viewBox="0 0 20 22" fill="none">
                                                <path
                                                    d="M19 4.97998C15.67 4.64998 12.32 4.47998 8.98 4.47998C7 4.47998 5.02 4.57998 3.04 4.77998L1 4.97998"
                                                    stroke="#647887" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                                <path
                                                    d="M6.5 3.97L6.72 2.66C6.88 1.71 7 1 8.69 1H11.31C13 1 13.13 1.75 13.28 2.67L13.5 3.97"
                                                    stroke="#647887" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                                <path
                                                    d="M16.8504 8.14014L16.2004 18.2101C16.0904 19.7801 16.0004 21.0001 13.2104 21.0001H6.79039C4.00039 21.0001 3.91039 19.7801 3.80039 18.2101L3.15039 8.14014"
                                                    stroke="#647887" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                                <path d="M8.33008 15.5H11.6601" stroke="#647887"
                                                    stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                                <path d="M7.5 11.5H12.5" stroke="#647887" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        @foreach ($errors->get('service_days') as $message)
            <p class="text-xs text-red-500">{{ $message }}</p>
        @endforeach
        @foreach ($errors->get('service_days.*') as $message)
            <p class="text-xs text-red-500">{{ $message[0] }}</p>
        @endforeach
    </div>

    {{-- SERVICE FORM --}}
    <div class="bg-white flex flex-col rounded-lg gap-4 px-4 py-5 w-full mt-7">
        <h1 class="font-bold text-xl">Service Form</h1>

        <div x-ref="questions" class="space-y-6" x-data="{ handleQuestionSort: (item, position) => { $wire.sortQuestion(item, position) } }" x-sort="handleQuestionSort" x-sort.ghost>
            @foreach ($service_form as $key => $question)
                <div x-sort:item="{{ $key }}" class="bg-rp-neutral-50 px-4 py-3 rounded-md" wire:key='question-{{ $key }}'>
                    @if (in_array($question['type'], ['multiple', 'checkbox', 'dropdown']))
                        {{-- Question: Multiple Choice --}}
                        <div class="flex flex-row items-start justify-between text-sm">
                            <div class="flex flex-row gap-4">
                                {{-- Sort --}}
                                <div class="">
                                    <x-icon.three-bars />
                                </div>
                                <div class="flex gap-4">
                                    {{-- Question content --}}
                                    <div class="w-80">
                                        <div class="mb-3">
                                            <p>Question title</p>
                                            <x-input wire:model="service_form.{{ $key }}.question"
                                                type="text" />
                                        </div>
                                        <div>
                                            <p class="">Choices</p>
                                            <div class="w-full space-y-2">
                                                @if (count($question['choices']) > 1)
                                                    @foreach ($question['choices'] as $choiceKey => $choice)
                                                        <div class="flex flex-row items-center gap-2"
                                                            wire:key='question-{{ $key }}-choice-{{ $choiceKey }}'>
                                                            <x-input
                                                                wire:model="service_form.{{ $key }}.choices.{{ $choiceKey }}"
                                                                type="text" />
                                                            {{-- Add icon --}}
                                                            <div wire:click="addQuestionChoice({{ $key }})">
                                                                <x-icon.add />
                                                            </div>
                                                            {{-- Delete --}}
                                                            <div
                                                                wire:click="removeQuestionChoice({{ $key }}, {{ $choiceKey }})">
                                                                <x-icon.close />
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="flex flex-row items-center gap-2">
                                                        <x-input
                                                            wire:model="service_form.{{ $key }}.choices.0"
                                                            type="text" />
                                                        {{-- Add icon --}}
                                                        <div wire:click="addQuestionChoice({{ $key }})">
                                                            <x-icon.add />
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Question type --}}
                                    <div class="w-56">
                                        <p>Question type</p>
                                        <x-dropdown.select wire:model.live="service_form.{{ $key }}.type">
                                            <x-dropdown.select.option value="paragraph">Paragraph
                                                Answer</x-dropdown.select.option>
                                            <x-dropdown.select.option value="multiple">Multiple
                                                Choice</x-dropdown.select.option>
                                            <x-dropdown.select.option
                                                value="checkbox">Checkbox</x-dropdown.select.option>
                                            <x-dropdown.select.option
                                                value="dropdown">Dropdown</x-dropdown.select.option>
                                        </x-dropdown.select>
                                    </div>
                                </div>
                            </div>

                            {{-- Other actions --}}
                            <div class="flex items-center gap-3">
                                {{-- Required --}}
                                <div class="flex items-center gap-2">
                                    <x-input wire:model="service_form.{{ $key }}.important"
                                        type="checkbox" />
                                    <p>Required</p>
                                </div>
                                {{-- Delete --}}
                                <div wire:click="removeQuestion({{ $key }})">
                                    <x-icon.trash />
                                </div>
                            </div>
                        </div>
                    @elseif ($question['type'] === 'paragraph')
                        {{-- Question: Paragraph Answer --}}
                        <div class="flex flex-row items-start justify-between text-sm">
                            <div class="flex flex-row gap-4">
                                {{-- Sort --}}
                                <div>
                                    <x-icon.three-bars />
                                </div>
                                {{-- Question content --}}
                                <div class="w-80">
                                    <p>Question title</p>
                                    <x-input wire:model="service_form.{{ $key }}.question"
                                        type="text" />
                                </div>
                                {{-- Question type --}}
                                <div class="w-56">
                                    <p>Question type</p>
                                    <x-dropdown.select wire:model.live="service_form.{{ $key }}.type">
                                        <x-dropdown.select.option value="paragraph">Paragraph
                                            Answer</x-dropdown.select.option>
                                        <x-dropdown.select.option value="multiple">Multiple
                                            Choice</x-dropdown.select.option>
                                        <x-dropdown.select.option value="checkbox">Checkbox</x-dropdown.select.option>
                                        <x-dropdown.select.option value="dropdown">Dropdown</x-dropdown.select.option>
                                    </x-dropdown.select>
                                </div>
                            </div>

                            {{-- Other actions --}}
                            <div class="flex items-center gap-3">
                                {{-- Required --}}
                                <div class="flex items-center gap-2">
                                    <x-input type="checkbox"
                                        wire:model="service_form.{{ $key }}.important" />
                                    <p>Required</p>
                                </div>
                                {{-- Delete --}}
                                <div wire:click="removeQuestion({{ $key }})">
                                    <x-icon.trash />
                                </div>
                            </div>
                        </div>
                    @else
                        <span>{{ $question['type'] }}</span>
                    @endif
                </div>
            @endforeach
        </div>

        <x-button.filled-button wire:click="addQuestion">
            <div class="flex justify-between items-center">
                <p class="text-white text-left">
                    Add question
                </p>
                {{-- Add icon --}}
                <div>
                    <x-icon.rounded-add />
                </div>
            </div>
        </x-button.filled-button>
    </div>

    {{-- LOCATION --}}
    <div class="flex flex-col gap-4 mb-9 p-6 rounded-lg bg-white mt-7">
        <h1 class="font-bold text-xl">Service Location</h1>
        <form wire:submit.prevent='getMapLatlng' id="searchAddress" class="relative flex border rounded-lg overflow-hidden bg-white items-center w-full">
            <x-input.search wire:model.live='location' class="w-full" id="inp_search" icon_position="right" />
        </form>
        <div>
            <div wire:ignore id="map" class="w-full h-[570px] border rounded-lg z-10"></div>
        </div>
    </div>

    {{-- PREVIOUS WORKS --}}
    <div class="bg-white rounded-lg gap-4 px-4 py-5 w-full mt-7">
        <h1 class="font-bold text-xl mb-3">Previous Works</h1>
        <div class="flex flex-col w-full gap-10">
            {{-- Work --}}
            @foreach ($previous_works as $key => $previous_work)
                <div class="flex justify-between items-center w-full"
                    wire:key='previous-work-{{ $key }}'>
                    <div class="flex w-[90%] items-center">
                        {{-- Img --}}
                        <div class="w-[112px]">
                            <div class="w-28 h-28 min-w-28 min-h-28">
                                @if ($previous_work['images'][0]['id'] !== null)
                                    <img src="{{ $previous_work['images'][0]['image'] }}" alt=""
                                        class="w-full h-full object-cover rounded-md">
                                @else
                                    <img src="{{ $previous_work['images'][0]['image']->temporaryUrl() }}" alt=""
                                        class="w-full h-full object-cover rounded-md">
                                @endif
                            </div>
                        </div>
                        <div class="p-2 w-[calc(100%_-_112px)]">
                            <p class="text-rp-neutral-600 font-bold">{{ $previous_work['title'] }}</p>
                            <p class="truncate">{{ $previous_work['description'] }}</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        {{-- Edit --}}
                        <button wire:click="editPreviousWork({{ $key }})">
                            <x-icon.edit />
                        </button>
                        {{-- Delete --}}
                        <button wire:click="deletePreviousWork({{ $key }})">
                            <x-icon.trash />
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        <x-button.filled-button @click="showPreviousWorkModal=true" class="w-full">
            <div class="flex justify-between">
                <p class="text-white text-left">
                    Add previous work
                </p>
                {{-- Add icon --}}
                <div>
                    <x-icon.rounded-add />
                </div>
            </div>
        </x-button.filled-button>
    </div>

    <div class="w-full flex justify-center mt-5 gap-2">
        {{-- Submit --}}
        <x-button.filled-button @click="submitForm" class="w-56">submit</x-button.filled-button>
        {{-- Cancel --}}
        <x-button.outline-button class="w-56">cancel</x-button.outline-button>
    </div>

    {{--  --------- MODAL STARTS HERE ---------- --}}
    {{-- Add previous work --}}
    <x-modal x-model="showPreviousWorkModal">
        <x-modal.form-modal title="Add previous work"
            @click.outside="showPreviousWorkModal=false;$wire.cancelPreviousWork();">
            @if ($showPreviousWorkModal)
                {{-- FORM --}}
                <form action="" class="flex flex-col gap-2">
                    {{-- Services images --}}
                    <livewire:components.input.interactive-upload-images :uploaded_images="$previous_work_images" :max="5" function="updatePreviousWorkImages"  />

                    {{-- Title --}}
                    <x-input.input-group>
                        <x-slot:label>Title</x-slot:label>
                        <x-input type="text" wire:model="previous_work_title" maxlength="120" />
                        @error('previous_work_title')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>

                    {{-- Description --}}
                    <x-input.input-group>
                        <x-slot:label>Description</x-slot:label>
                        <x-input.textarea wire:model="previous_work_description" x-ref="inpcomment"
                            x-on:keyup="counter = $refs.inpcomment.value.length" cols="30" rows="10"
                            maxlength="1000" />
                        @error('previous_work_description')
                            <p class="text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>




                </form>
                <x-slot:action_buttons>
                    <x-button.outline-button @click="showPreviousWorkModal=false;$wire.cancelPreviousWork();"
                        class="flex-1">cancel</x-button.outline-button>
                    @if ($edit_previous_work_index !== null)
                        <x-button.filled-button wire:click='updatePreviousWork'
                            class="flex-1">submit</x-button.filled-button>
                    @else
                        <x-button.filled-button wire:click='addPreviousWork'
                            class="flex-1">submit</x-button.filled-button>
                    @endif
                </x-slot:action_buttons>
            @endif
        </x-modal.form-modal>
    </x-modal>

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
</x-main.content>


@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('operatingDays', (operating_days) => ({
                operatingDays: operating_days,

                addTimeslot(dayNum) {
                    const operatingDaysCopy = this.cleanObject(this.operatingDays);

                    const targetIndex = operatingDaysCopy.findIndex(el => el.id === dayNum);

                    const day = operatingDaysCopy[targetIndex];

                    if (!day) {
                        return;
                    }

                    const newTimeslot = {
                        'start_time': {
                            option: [
                                '12:00 AM',
                                '12:30 AM',
                                '01:00 AM',
                                '01:30 AM',
                                '02:00 AM',
                                '02:30 AM',
                                '03:00 AM',
                                '03:30 AM',
                                '04:00 AM',
                                '04:30 AM',
                                '05:00 AM',
                                '05:30 AM',
                                '06:00 AM',
                                '06:30 AM',
                                '07:00 AM',
                                '07:30 AM',
                                '08:00 AM',
                                '08:30 AM',
                                '09:00 AM',
                                '09:30 AM',
                                '10:00 AM',
                                '10:30 AM',
                                '11:00 AM',
                                '11:30 AM',
                                '12:00 PM',
                                '12:30 PM',
                                '01:00 PM',
                                '01:30 PM',
                                '02:00 PM',
                                '02:30 PM',
                                '03:00 PM',
                                '03:30 PM',
                                '04:00 PM',
                                '04:30 PM',
                                '05:00 PM',
                                '05:30 PM',
                                '06:00 PM',
                                '06:30 PM',
                                '07:00 PM',
                                '07:30 PM',
                                '08:00 PM',
                                '08:30 PM',
                                '09:00 PM',
                                '09:30 PM',
                                '10:00 PM',
                                '10:30 PM',
                                '11:00 PM',
                                '11:30 PM',
                            ],
                            selected: '',
                        },

                        'end_time': {
                            option: [
                                '12:00 AM',
                                '12:30 AM',
                                '01:00 AM',
                                '01:30 AM',
                                '02:00 AM',
                                '02:30 AM',
                                '03:00 AM',
                                '03:30 AM',
                                '04:00 AM',
                                '04:30 AM',
                                '05:00 AM',
                                '05:30 AM',
                                '06:00 AM',
                                '06:30 AM',
                                '07:00 AM',
                                '07:30 AM',
                                '08:00 AM',
                                '08:30 AM',
                                '09:00 AM',
                                '09:30 AM',
                                '10:00 AM',
                                '10:30 AM',
                                '11:00 AM',
                                '11:30 AM',
                                '12:00 PM',
                                '12:30 PM',
                                '01:00 PM',
                                '01:30 PM',
                                '02:00 PM',
                                '02:30 PM',
                                '03:00 PM',
                                '03:30 PM',
                                '04:00 PM',
                                '04:30 PM',
                                '05:00 PM',
                                '05:30 PM',
                                '06:00 PM',
                                '06:30 PM',
                                '07:00 PM',
                                '07:30 PM',
                                '08:00 PM',
                                '08:30 PM',
                                '09:00 PM',
                                '09:30 PM',
                                '10:00 PM',
                                '10:30 PM',
                                '11:00 PM',
                                '11:30 PM',
                            ],
                            selected: '',
                        }
                    }


                    day.timeslots.push(newTimeslot);

                    this.operatingDays = this.cleanObject(operatingDaysCopy);
                },

                copyToAll(dayNum) {

                    const operatingDaysCopy = this.cleanObject(this.operatingDays);

                    const targetIndex = operatingDaysCopy.findIndex(el => el.id === dayNum);

                    const day = operatingDaysCopy[targetIndex];

                    if (!day) {
                        return;
                    }

                    const timeslotForAll = day.timeslots;

                    const newOperatingDays = operatingDaysCopy.map(el => {
                        return {
                            ...el,
                            timeslots: timeslotForAll
                        }
                    });
                    this.operatingDays = this.cleanObject(newOperatingDays);

                },

                removeTimeslot({
                    dayNum,
                    slotIndex
                }) {
                    const operatingDaysCopy = this.cleanObject(this.operatingDays);

                    const targetIndex = operatingDaysCopy.findIndex(el => el.id === dayNum);

                    const day = operatingDaysCopy[targetIndex];

                    day.timeslots.splice(slotIndex, 1);

                    this.operatingDays = this.cleanObject(operatingDaysCopy);

                },

                cleanObject(obj) {
                    return JSON.parse(JSON.stringify(obj));
                },

                submitForm() {
                    const data = this.operatingDays.map(day => ({
                        dayName: day.dayName || null,
                        checked: day.checked,
                        timeslots: day.timeslots.map(slot => ({
                            start_time: slot.start_time.selected || null,
                            end_time: slot.end_time.selected || null
                        }))
                    }));

                    @this.set('service_days', data);

                    @this.save();
                }
            }));
        });
    </script>

    @script
        <script>
            document.addEventListener('livewire:navigated', () => {
                $wire.dispatch('initMap');

                // LEAFLET MAP
                let L = Leaflet;
                let map = L.map('map').setView([14.599512, 120.984222], 13);
                const markerGroup = L.layerGroup().addTo(map);

                // TILE
                L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                }).addTo(map);

                // ICON
                const pinIcon = L.icon({
                    iconUrl: `${window.location.origin}/images/map/map_pin.png`,
                    iconSize: [30, 51],
                    iconAnchor: [15, 51]
                });

                // MAP CLICK
                map.on('click', (e) => {
                    // remove all markers
                    markerGroup.clearLayers()

                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;

                    // add new marker
                    L.marker([lat, lng], {
                        icon: pinIcon
                    }).addTo(markerGroup);

                    $wire.dispatch('getMapAddress', {lat, lng});

                    // // EMIT
                    // $wire.getMapAddress(lat, lng);
                })

                // EMIT UPDATE
                $wire.on('updateMap', (data) => {
                    if (typeof data[0] !== 'string') {
                        //remove all markers
                        markerGroup.clearLayers()
                        // fly
                        map.flyTo([data[0].lat, data[0].lng], 17);
                        // add new marker
                        L.marker([data[0].lat, data[0].lng], {
                            icon: pinIcon
                        }).addTo(markerGroup);
                    }
                });
            });
        </script>
    @endscript

@endpush
