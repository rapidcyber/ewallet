<x-main.content class="!px-16 !py-10" x-data="{
    editModal: { isVisible: false, type: 'access_level' },
    isEmployeeDeleteModalVisible: false,
}">
    <x-main.action-header>
        <x-slot:title>Employee Details</x-slot:title>
        <x-slot:actions>
            @if ($employee->access_level->slug != 'owner')
                @isset($send_money)
                    <x-button.filled-button href="{{ route('admin.payroll.send', ['employee' => $employee->id]) }}" color="primary">send money</x-button.filled-button>
                @endisset
                @if ($employee->merchant_id === 1 && $employee->user_id !== $merchant->user_id)
                    <x-button.outline-button color="primary" @click="isEmployeeDeleteModalVisible=true;">delete
                        employee</x-button.filled-button>
                @endif
            @endif
        </x-slot:actions>
    </x-main.action-header>

    <div class="flex">
        {{-- Profile Overview --}}
        <x-layout.details.profile-overview class="h-full w-[25%] max-w-[25%]">
            <div class="h-auto mb-2 w-36">
                <img class="object-cover w-full h-auto rounded-full aspect-square"
                    src="{{ url('images/user/default-avatar.png') }}"
                    alt="">
            </div>
            <div class="w-full space-y-4">
                <h1 class="w-full mb-2 text-2xl font-bold 2xl:text-4xl">
                    {{ $employee->user->name }}

                </h1>
                <div class="flex items-center justify-between w-full gap-2">
                    <p class="w-4/5 text-xs break-words 2xl:text-sm">{{ $employee->occupation }}</p>
                    @if (($employee->access_level->slug != 'owner' || $merchant->user_id == $employee->user_id) && $employee->merchant_id === 1 && $employee->user_id !== $merchant->user_id)
                        <div class="cursor-pointer" @click="editModal.isVisible=true;editModal.type='occupation'">
                            <x-icon.edit />
                        </div>
                    @endif
                </div>
                <div class="flex items-center justify-between w-full gap-2">
                    <div class="bg-primary-200 rounded-[56px] text- text-xs 2xl:text-sm p-1 leading-[150%] w-4/5">
                        {{ $this->employee_access_level }}
                    </div>
                    @if ($employee->access_level->slug != 'owner' && $employee->merchant_id === 1 && $employee->user_id !== $merchant->user_id)
                        <div class="cursor-pointer" @click="editModal.isVisible=true;editModal.type='access_level'">
                            <x-icon.edit />
                        </div>
                    @endif
                </div>
            </div>
        </x-layout.details.profile-overview>

        {{-- More Details --}}
        <x-layout.details.more-details class="w-[75%] max-w-[75%]">
            <x-layout.details.more-details.section title="Account Details" title_text_color="primary">
                <x-layout.details.more-details.data-field field="Full Name"
                    value="{{ $employee->user->name }}" />
                <x-layout.details.more-details.data-field field="Birthday"
                    value="{{ \Carbon\Carbon::parse($employee->user->profile->birth_date)->format('F j, Y') }}" />
                <x-layout.details.more-details.data-field field="Birthplace"
                    value="{{ $employee->user->profile->birth_place }}" />
                <x-layout.details.more-details.data-field field="Nationality"
                    value="{{ $employee->user->profile->nationality }}" />
            </x-layout.details.more-details.section>
            <x-layout.details.more-details.section title="Contact Details" title_text_color="primary">
                <x-layout.details.more-details.data-field field="Number"
                    value="{{ $this->phone_number }}" />
                <x-layout.details.more-details.data-field field="Email" value="{{ $employee->user->email }}" />
                {{-- <x-layout.details.more-details.data-field field="Permanent address"
                    value="{{ json_decode($employee->user->location, true)[0]['address'] ?? '' }}" />
                <x-layout.details.more-details.data-field field="Current address"
                    value="{{ json_decode($employee->user->location, true)[0]['address'] ?? '' }}" /> --}}
            </x-layout.details.more-details.section>
            <x-layout.details.more-details.section title="Employment Details" title_text_color="primary">
                <x-layout.details.more-details.data-field field="Date added"
                    value="{{ \Carbon\Carbon::parse($employee->created_at)->timezone('Asia/Manila')->format('F j, Y') }}" />
                @if (($employee->access_level->slug != 'owner' || $merchant->user_id == $employee->user_id) && $employee->merchant_id === 1 && $employee->user_id !== $merchant->user_id)
                    <x-layout.details.more-details.edit-data-field field="Salary Type"
                        value="{{ $employee->salary_type->name }}"
                        @click="editModal.isVisible=true;editModal.type='salary_type'" />
                    @if ($employee->salary_type->slug === 'per_day')
                        <x-layout.details.more-details.edit-data-field field="Salary Per Day"
                            value="₱ {{ number_format($employee->salary, 2) }}"
                            @click="editModal.isVisible=true;editModal.type='salary'" />
                    @elseif ($employee->salary_type->slug === 'per_cutoff')
                        <x-layout.details.more-details.edit-data-field field="Salary Per Month"
                            value="₱ {{ number_format($employee->salary, 2) }}"
                            @click="editModal.isVisible=true;editModal.type='salary'" />
                    @endif
                @else
                    <x-layout.details.more-details.data-field field="Salary Type"
                        value="{{ $employee->salary_type->name }}" />
                    @if ($employee->salary_type->slug === 'per_day')
                        <x-layout.details.more-details.data-field field="Salary Per Day"
                            value="₱ {{ number_format($employee->salary, 2) }}" />
                    @elseif ($employee->salary_type->slug === 'per_cutoff')
                        <x-layout.details.more-details.data-field field="Salary Per Month"
                            value="₱ {{ number_format($employee->salary, 2) }}" />
                    @endif
                @endif
                <div wire:ignore>
                    @if (request()->routeIs('admin.employees.show'))
                        <a  href="{{ route('admin.payroll.index') }}" class="block text-sm text-right text-primary-600">Go to
                            payroll ></a>
                    @endif
                </div>
            </x-layout.details.more-details.section>
        </x-layout.details.more-details>
    </div>


    {{-- Edit Modals --}}
    <x-modal x-model="editModal.isVisible">
        <template x-if="editModal.type === 'occupation'">
            <x-modal.form-modal title="Edit Occupation" x-data="{ occupation: $wire.entangle('occupation') }">
                <x-input type="text" x-ref="edit_occupation" ::value="occupation" maxlength="120"
                    placeholder="Occupation" />
                <x-slot:action_buttons>
                    <x-button.outline-button color="primary" @click="editModal.isVisible=false;editModal.type=''" class="grow">
                        cancel
                    </x-button.outline-button>
                    <x-button.filled-button color="primary"
                        @click="editModal.isVisible=false;$wire.handleSave({ type: editModal.type, value: $refs.edit_occupation.value })"
                        class="grow">
                        confirm
                    </x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.form-modal>
        </template>

        <template x-if="editModal.type === 'access_level'">
            <x-modal.form-modal title="Edit Access Level" x-data="{ access_level: $wire.entangle('access_level') }">
                <x-dropdown.select x-model="access_level" x-ref="edit_access_level">
                    @foreach ($this->access_levels as $level)
                        <x-dropdown.select.option value="{{ $level->slug }}">
                            {{ $level->name }}
                        </x-dropdown.select.option>
                    @endforeach
                </x-dropdown.select>
                <x-slot:action_buttons>
                    <x-button.outline-button color="primary" @click="editModal.isVisible=false;editModal.type=''" class="grow">
                        cancel
                    </x-button.outline-button>
                    <x-button.filled-button color="primary"
                        @click="editModal.isVisible=false;$wire.handleSave({ type: editModal.type, value: $refs.edit_access_level.value })"
                        class="grow">
                        confirm
                    </x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.form-modal>
        </template>

        <template x-if="editModal.type === 'salary_type'">
            <x-modal.form-modal title="Edit Salary" x-data="{ salary_type: $wire.entangle('salary_type') }">
                <x-dropdown.select x-model="salary_type" x-ref="edit_salary_type">
                    @foreach ($this->salary_types as $type)
                        <x-dropdown.select.option value="{{ $type->slug }}">
                            {{ $type->name }}
                        </x-dropdown.select.option>
                    @endforeach
                </x-dropdown.select>
                <x-slot:action_buttons>
                    <x-button.outline-button color="primary" @click="editModal.isVisible=false;editModal.type=''"
                        class="grow">
                        cancel
                    </x-button.outline-button>
                    <x-button.filled-button color="primary"
                        @click="editModal.isVisible=false;$wire.handleSave({ type: editModal.type, value: $refs.edit_salary_type.value })"
                        class="grow">
                        confirm
                    </x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.form-modal>
        </template>

        <template x-if="editModal.type === 'salary'">
            <x-modal.form-modal title="Edit Salary" x-data="{ salary: $wire.entangle('salary') }">
                <x-input type="number" x-ref="edit_salary" ::value="salary" min="0" max="10000000.00"
                    placeholder="Salary" />
                <x-slot:action_buttons>
                    <x-button.outline-button color="primary" @click="editModal.isVisible=false;editModal.type=''"
                        class="grow">
                        cancel
                    </x-button.outline-button>
                    <x-button.filled-button color="primary"
                        @click="editModal.isVisible=false;$wire.handleSave({ type: editModal.type, value: parseFloat($refs.edit_salary.value) })"
                        class="grow">
                        confirm
                    </x-button.filled-button>
                </x-slot:action_buttons>
            </x-modal.form-modal>
        </template>
    </x-modal>

    {{-- Confirmation Modal --}}
    <x-modal x-model="isEmployeeDeleteModalVisible">
        <x-modal.confirmation-modal>
            <x-slot:title>Confirmation</x-slot:title>
            <x-slot:message>
                Are you sure you want to delete this Employee?
            </x-slot:message>
            <x-slot:action_buttons>
                <x-button.outline-button color="primary" class="flex-1"
                    @click="isEmployeeDeleteModalVisible=false;">cancel</x-button.outline-button>
                <x-button.filled-button color="primary" class="flex-1"
                    @click="$wire.handleConfirmDeleteEmployee()">yes</x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>
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

    <x-loader.black-screen wire:loading wire:target='handleSave' class="z-10"/>
</x-main.content>
