<div class="flex h-full" x-data="timerData">
    <x-main.content class="overflow-y-auto grow !px-[18.5px]">
        <x-main.title class="mb-8">Create Transaction</x-main.title>
        <x-card.display-balance :balance="$this->available_balance" class="mb-8" />
        <div class="flex flex-row gap-2 w-full mb-3">
            <button wire:click="$set('transaction_type', 'money-transfer')"
                class="hover:bg-rp-neutral-100 border border-rp-neutral-500 bg-white rounded-lg px-4 py-3 flex flex-row justify-between items-center gap-2 flex-1">
                <div class="flex-none mr-2">
                    <x-icon.money-transfer />
                </div>
                <div class="flex-auto text-left">
                    <h2 class="font-bold text-rp-neutral-700">Money Transfer</h2>
                    <p class="text-sm">Transfer money to RePay users{{-- , a Unionbank account, --}} or another bank</p>
                </div>
                <div height="35" width="35" class="flex-none">
                    @if ($transaction_type == 'money-transfer')
                        <x-icon.check />
                    @endif
                </div>
            </button>
            <button wire:click="$set('transaction_type', 'bill-payment')"
                class="hover:bg-rp-neutral-100 border border-rp-neutral-500 bg-white rounded-lg px-4 py-3 flex flex-row justify-between items-center gap-2 flex-1">
                <div class="flex-none mr-2">
                    <x-icon.bill-payment />
                </div>
                <div class="flex-auto text-left">
                    <h2 class="font-bold text-rp-neutral-700">Bill Payment</h2>
                    <p class="text-sm">Pay bills and schedule them in a hassle-free way!</p>
                </div>
                <div height="35" width="35" class="flex-none">
                    @if ($transaction_type == 'bill-payment')
                        <x-icon.check />
                    @endif
                </div>
            </button>
        </div>

        @if ($transaction_type === 'money-transfer')
            <div class="mb-3">
                <x-input.input-group>
                    <x-slot:label for="transfer_to">Transfer to:</x-slot:label>
                    <x-dropdown.select wire:model.live="transfer_to" id="transfer_to" class="{{ $errors->has('amount') ? '!border-red-500' : '' }}">
                        <x-dropdown.select.option value="another-account">Another Account</x-dropdown.select.option>
                        {{-- <x-dropdown.select.option value="unionbank-account">UnionBank Account</x-dropdown.select.option> --}}
                        <x-dropdown.select.option value="another-bank">Another Bank</x-dropdown.select.option>
                    </x-dropdown.select>
                </x-input.input-group>
                @error('transfer_to')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            @if ($transfer_to === 'another-account')
                <div class="mb-3">
                    <div class="flex flex-row justify-between gap-2">
                        <x-input.input-group class="flex-1">
                            <x-slot:label for="phone_number" class="{{ $errors->has('amount') ? '!text-red-500' : '' }}">Phone Number:</x-slot:label>
                            <div class="flex flex-row gap-2">
                                <x-dropdown.select wire:model.change="phone_iso" class="w-32 {{ $errors->has('phone_iso') ? '!border-red-500' : '' }}">
                                    @foreach ($this->phone_isos as $country => $iso)
                                        <x-dropdown.select.option
                                            :value="$country">{{ '+' . $iso }}</x-dropdown.select.option>
                                    @endforeach
                                </x-dropdown.select>
                                <x-input type="text" id="phone_number" wire:model.blur="phone_number" maxlength="15"
                                    class="w-full {{ $errors->has('phone_number') ? '!border-red-500' : '' }}" oninput="this.value = this.value.replace(/[^0-9]/gi, '')">
                                    <x-slot:icon>
                                        <x-icon.phone />
                                    </x-slot:icon>
                                </x-input>
                            </div>
                        </x-input.input-group>

                        <x-input.input-group class="flex-1">
                            <x-slot:label for="amount" class="{{ $errors->has('amount') ? '!text-red-500' : '' }}">Amount:</x-slot:label>
                            <x-input wire:model.blur="amount" type="text" maxlength="5" class="{{ $errors->has('amount') ? '!border-red-500' : '' }}"
                                oninput="this.value = this.value.replace(/[^0-9]/gi, '')">
                            </x-input>
                        </x-input.input-group>
                    </div>

                    @error('phone_iso')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @error('phone_number')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @error('amount')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <x-input.input-group>
                    <x-slot:label>Message (optional)</x-slot:label>
                    <x-input.textarea class="w-full rounded-md" x-ref="message" wire:model.lazy="message"
                        maxlength="255" rows="6" />
                    <div class="flex flex-row justify-between">
                        <div>
                            @error('message')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <p class="text-right text-[11px]"><span x-html="$wire.message.length"></span>/<span
                                    x-html="$refs.message.maxLength"></span></p>
                        </div>
                    </div>
                </x-input.input-group>
            @elseif ($transfer_to === 'unionbank-account')
                <div class="flex flex-row justify-between gap-2 mb-9">
                    <x-input.input-group class="flex-1">
                        <x-slot:label>UnionBank Account Number:</x-slot:label>
                        <x-input type="text" wire:model="account_number" maxlength="12"
                            oninput="this.value = this.value.replace(/[^0-9]/gi, '')">
                            <x-slot:icon>
                                <x-icon.user />
                            </x-slot:icon>
                        </x-input>
                    </x-input.input-group>
                    <x-input.input-group class="flex-1">
                        <x-slot:label>Amount:</x-slot:label>
                        <x-input wire:model.blur="amount" type="text" maxlength="8"
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^(\d*\.\d{2}).*/, '$1')">
                        </x-input>
                    </x-input.input-group>
                </div>
                <div class="mb-3">
                    <p class="font-bold text-xl">Notify the recipient (optional)</p>
                    <div class="flex flex-row justify-between gap-2">
                        <x-input.input-group class="flex-1">
                            <x-slot:label>Phone Number:</x-slot:label>
                            <x-input type="text" wire:model="phone_number"
                                oninput="this.value = this.value.replace(/[^0-9]/gi, '')" maxlength="15">
                                <x-slot:icon>
                                    <x-icon.phone />
                                </x-slot:icon>
                            </x-input>
                        </x-input.input-group>
                        <x-input.input-group class="flex-1">
                            <x-slot:label>Phone Number:</x-slot:label>
                            <x-input type="text" wire:model="phone_number"
                                oninput="this.value = this.value.replace(/[^0-9]/gi, '')" maxlength="15">
                                <x-slot:icon>
                                    <x-icon.mail />
                                </x-slot:icon>
                            </x-input>
                        </x-input.input-group>
                    </div>
                </div>
                <x-input.input-group>
                    <x-slot:label>Message (optional)</x-slot:label>
                    <x-input.textarea wire:model="message" maxlength="120"></x-input.textarea>
                </x-input.input-group>
            @elseif ($transfer_to === 'another-bank')
                <div class="mb-3">
                    <label class="block text-xs 2xl:text-sm">Send via:</label>
                    <div class="flex flex-row gap-2">
                        {{-- Instapay --}}
                        <button wire:click="$set('send_via', 'instapay')"
                            class="border border-rp-neutral-500 bg-white rounded-lg px-2 py-3 flex flex-row justify-between items-center gap-2 flex-1">
                            <img src="{{ url('images/instapay.svg') }}" alt="Instapay">
                            <div x-show="$wire.send_via === 'instapay'">
                                <x-icon.check />
                            </div>
                        </button>
                        {{-- Pesonet --}}
                        <button wire:click="$set('send_via', 'pesonet')"
                            class="border border-rp-neutral-500 bg-white rounded-lg px-2 py-3 flex flex-row justify-between items-center gap-2 flex-1">
                            <img src="{{ url('images/pesonet.svg') }}" alt="Pesonet">
                            <div x-show="$wire.send_via === 'pesonet'">
                                <x-icon.check />
                            </div>
                        </button>
                    </div>
                    @error('send_via')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                {{-- New version: Select Bank --}}
                <x-input.input-group class="mb-3">
                    <x-slot:label>Select Bank:</x-slot:label>
                    <x-dropdown.select wire:model.change="selected_bank">
                        <x-dropdown.select.option value="" selected hidden>Select</x-dropdown.select.option>
                        @if ($send_via === 'instapay')
                            @foreach ($this->instapay_banks_list as $key => $bank_opt)
                                <x-dropdown.select.option
                                    :value="$bank_opt['code']">{{ $bank_opt['name'] }}</x-dropdown.select.option>
                            @endforeach
                        @elseif ($send_via === 'pesonet')
                            @foreach ($this->pesonet_banks_list as $key => $bank_opt)
                                <x-dropdown.select.option
                                    :value="$bank_opt['code']">{{ $bank_opt['name'] }}</x-dropdown.select.option>
                            @endforeach
                        @endif
                    </x-dropdown.select>
                    @error('selected_bank')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </x-input.input-group>

                <div class="flex flex-row justify-between gap-2 mb-3">
                    <x-input.input-group class="flex-1">
                        <x-slot:label>Account Number:</x-slot:label>
                        <x-input type="text" wire:model.blur="account_number"
                            oninput="this.value = this.value.replace(/[^0-9]/gi, '')" maxlength="30">
                            <x-slot:icon>
                                <x-icon.user />
                            </x-slot:icon>
                        </x-input>
                        @error('account_number')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>
                    <x-input.input-group class="flex-1">
                        <x-slot:label>Account Name:</x-slot:label>
                        <x-input type="text" maxlength="50" wire:model.blur="account_name" />
                        @error('account_name')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>
                </div>

                <div class="flex flex-row justify-between gap-2 mb-9">
                    <x-input.input-group class="flex-1">
                        <x-slot:label for="amount">Amount:</x-slot:label>
                        <x-input wire:model.blur="amount" type="text" maxlength="8"
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^(\d*\.\d{2}).*/, '$1')">
                        </x-input>
                        @error('amount')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>
                </div>

                {{-- <div>
                    <p class="font-bold text-xl">Notify the recipient (optional)</p>
                    <div class="flex flex-row justify-between gap-2 mb-3">
                        <x-input.input-group class="flex-1">
                            <x-slot:label for="phone_number">Phone Number:</x-slot:label>
                            <div class="flex flex-row gap-2">
                                <x-dropdown.select wire:model.change="phone_iso" class="w-32">
                                    @foreach ($this->phone_isos as $country => $iso)
                                        <x-dropdown.select.option
                                            :value="$country">{{ '+' . $iso }}</x-dropdown.select.option>
                                    @endforeach
                                </x-dropdown.select>
                                <x-input type="text" id="phone_number" wire:model.lazy="phone_number"
                                    maxlength="15" class="w-full"
                                    oninput="this.value = this.value.replace(/[^0-9]/gi, '')">
                                    <x-slot:icon>
                                        <x-icon.phone />
                                    </x-slot:icon>
                                </x-input>
                            </div>
                            @error('phone_iso')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            @error('phone_number')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </x-input.input-group>
                        <x-input.input-group class="flex-1">
                            <x-slot:label>
                                Email address:
                            </x-slot:label>
                            <x-input type="text" wire:model="email" maxlength="255" />
                            @error('email')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </x-input.input-group>
                    </div>
                    <x-input.input-group>
                        <x-slot:label>Message (optional)</x-slot:label>
                        <x-input.textarea class="w-full rounded-md" x-ref="message" wire:model.lazy="message"
                            maxlength="255" rows="6" />
                        <div class="flex flex-row justify-between">
                            <div>
                                @error('message')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <p class="text-right text-[11px]"><span x-html="$wire.message.length"></span>/<span
                                        x-html="$refs.message.maxLength"></span></p>
                            </div>
                        </div>
                    </x-input.input-group>
                </div> --}}
            @endif
        @elseif ($transaction_type === 'bill-payment')
            <div class="mb-3" x-data="{ show_billers: false }" @click="show_billers=true" @click.outside="show_billers=false">
                <x-input.input-group>
                    <x-slot:label for="biller_type">Biller type:</x-slot:label>
                    <x-input type="search"
                        id="biller_type"
                        wire:model.live="biller_type"
                        placeholder="Search biller">
                    </x-input>
                    @if (!empty($billers))
                        <div class="overflow-y-auto max-h-64 py-2" x-show="show_billers">
                            @foreach ($billers as $key => $biller_opt)
                                <button class="w-full items-center text-left justify-between border border-black rounded-lg px-5 py-4 mb-2 {{ $biller_opt['status'] == 0 ? 'cursor-not-allowed bg-gray-200' : 'bg-white hover:bg-gray-100' }}"
                                    wire:key="biller-{{ $biller_opt['code'] }}" {{ $biller_opt['status'] == 0 ? 'disabled' : '' }} wire:click="$set('biller_type', '{{ $biller_opt['name'] }}')">
                                    <p>{{ $biller_opt['name'] }}</p>
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @error('biller_type')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </x-input.input-group>
            </div>

            <div class="flex flex-row justify-between gap-2 mb-3">
                @foreach ($this->get_biller_input_fields as $key => $input_field)
                    <x-input.input-group class="flex-1" wire:key="input-field-{{ $key }}">
                        <x-slot:label for="input-field-{{ $key }}">{{ $input_field['label'] }}:</x-slot:label>
                        <x-input type="{{ $input_field['format'] === 'numeric' ? 'number' : 'text' }}"
                            maxlength="{{ $input_field['maxlength'] }}"
                            wire:model.blur="bill_info.{{ $key }}"
                            id="input-field-{{ $key }}">
                        </x-input>
                        @error('bill_info.' . $key)
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </x-input.input-group>
                @endforeach
            </div>

            <div class="flex flex-row justify-between gap-2">
                <x-input.input-group class="flex-1">
                    <x-slot:label>Amount:</x-slot:label>
                    <x-input wire:model.blur="amount" type="text" maxlength="8"
                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/^(\d*\.\d{2}).*/, '$1')"></x-input>
                    @error('amount')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </x-input.input-group>
                <x-input.input-group class="flex-1">
                    <x-slot:label>Email receipt to (optional):</x-slot:label>
                    <x-input type="text" maxlength="255" wire:model.blur="email">
                        <x-slot:icon>
                            <x-icon.mail />
                        </x-slot:icon>
                    </x-input>
                    @error('email')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </x-input.input-group>
            </div>
        @endif
    </x-main.content>

    <x-layout.summary>
        @if ($transaction_type === 'money-transfer' and $transfer_to === 'another-account')
            <x-slot:profile>
                <x-layout.summary.transaction-info>
                    {{-- Image --}}
                    <x-slot:image :src="$this->get_user['profile_picture']" alt="User Avatar"></x-slot:image>
                    <x-slot:info_block>
                        <p class="font-bold text-rp-neutral-700 text-xl">{{ $this->get_user['name'] }}</p>
                        @if (!empty($phone_number))
                            <p>{{ '(+' . $this->get_phone_iso . ') ' . $phone_number }}</p>
                        @endif
                        {{-- @elseif ($transaction_type === 'money-transfer' and $send_via === 'another-bank')
                            <p class="font-bold text-rp-neutral-700 text-xl">{{ $selectedBank['name'] }}</p>
                            <p>{{ $selectedBank['code'] }}</p>
                        @elseif (!empty($selectedBiller))
                            <p class="font-bold text-rp-neutral-700 text-xl">{{ json_decode($selectedBiller, true)['name'] }}</p>
                            <p>{{ json_decode($selectedBiller, true)['code'] }}</p> --}}
                    </x-slot:info_block>
                </x-layout.summary.transaction-info>
            </x-slot:profile>
        @endif
        <x-slot:body>
            @if ($transaction_type === 'money-transfer')
                @if ($transfer_to === 'another-account')
                    <x-layout.summary.section title="Payment Details">
                        <x-slot:data>
                            <x-layout.summary.label-data>
                                <x-slot:label>Transaction type</x-slot:label>
                                <x-slot:data>{{ ucwords(str_replace('-', ' ', $transaction_type)) }}</x-slot:data>
                            </x-layout.summary.label-data>
                            @if (!empty($phone_number))
                                <x-layout.summary.label-data>
                                    <x-slot:label>Phone Number</x-slot:label>
                                    <x-slot:data>{{ '(+' . $this->get_phone_iso . ') ' . $phone_number }}</x-slot:data>
                                </x-layout.summary.label-data>
                            @endif
                            @if (!empty($amount))
                                <x-layout.summary.label-data>
                                    <x-slot:label>Amount</x-slot:label>
                                    <x-slot:data>{{ \Number::currency($amount, 'PHP') }}</x-slot:data>
                                </x-layout.summary.label-data>
                            @endif
                        </x-slot:data>
                    </x-layout.summary.section>
                    @if (!empty($message))
                        <x-layout.summary.section title="Message">
                            <x-slot:data>
                                <p>{{ $message }}</p>
                            </x-slot:data>
                        </x-layout.summary.section>
                    @endif
                @elseif ($transfer_to === 'unionbank-account')
                    <x-layout.summary.section title="Payment Details">
                        <x-slot:data>
                            <x-layout.summary.label-data>
                                <x-slot:label>Transaction type</x-slot:label>
                                <x-slot:data>{{ ucwords(str_replace('-', ' ', $transaction_type)) }}</x-slot:data>
                            </x-layout.summary.label-data>
                            <x-layout.summary.label-data>
                                <x-slot:label>Account Number</x-slot:label>
                                <x-slot:data>{{ $account_number }}</x-slot:data>
                            </x-layout.summary.label-data>
                            <x-layout.summary.label-data>
                                <x-slot:label>Amount</x-slot:label>
                                <x-slot:data>{{ number_format(empty($amount) ? 0 : $amount, 2) }}</x-slot:data>
                            </x-layout.summary.label-data>
                        </x-slot:data>
                    </x-layout.summary.section>
                    <x-layout.summary.section title="Notification Details">
                        <x-slot:data>
                            <x-layout.summary.label-data>
                                <x-slot:label>Phone Number</x-slot:label>
                                <x-slot:data>{{ $phone_number ? '+' . $phone_number : '' }}</x-slot:data>
                            </x-layout.summary.label-data>
                            <x-layout.summary.label-data>
                                <x-slot:label>Email Address</x-slot:label>
                                <x-slot:data>{{ $email ?? '' }}</x-slot:data>
                            </x-layout.summary.label-data>
                        </x-slot:data>
                    </x-layout.summary.section>
                    <x-layout.summary.section title="Message">
                        <x-slot:data>
                            <p>{{ $message }}</p>
                        </x-slot:data>
                    </x-layout.summary.section>
                @elseif ($transfer_to === 'another-bank')
                    <x-layout.summary.transaction-info>
                        <x-slot:info_block>
                            <p class="font-bold text-rp-neutral-700 text-xl">{{ $this->get_bank_name }}</p>
                            <p>{{ $account_number }}</p>
                        </x-slot:info_block>
                    </x-layout.summary.transaction-info>
                    <x-layout.summary.section title="Payment Details">
                        <x-slot:data>
                            <x-layout.summary.label-data>
                                <x-slot:label>Transaction type</x-slot:label>
                                <x-slot:data>{{ ucwords(str_replace('-', ' ', $transaction_type)) }}</x-slot:data>
                            </x-layout.summary.label-data>
                            <div
                                class="flex flex-row justify-between gap-2 py-2 [&:not(:last-child)]:border-b [&:not(:last-child)]:border-rp-neutral-300 w-full">
                                <p class="w-2/5 break-words">Send via</p>
                                <div class="text-rp-neutral-700 font-bold w-3/5 break-words flex justify-end"
                                    x-show="$wire.send_via === 'instapay'">
                                    <img src="{{ url('/images/instapay.svg') }}" alt="instapay">
                                </div>
                                <div class="text-rp-neutral-700 font-bold w-3/5 break-words flex justify-end"
                                    x-show="$wire.send_via === 'pesonet'">
                                    <img src="{{ url('/images/pesonet.svg') }}" alt="pesonet">
                                </div>
                            </div>
                            @if (!empty($selected_bank))
                                <x-layout.summary.label-data>
                                    <x-slot:label>Bank Name</x-slot:label>
                                    <x-slot:data>{{ $this->get_bank_name }}</x-slot:data>
                                </x-layout.summary.label-data>
                            @endif
                            @if (!empty($account_number))
                                <x-layout.summary.label-data>
                                    <x-slot:label>Account Number</x-slot:label>
                                    <x-slot:data>{{ $account_number }}</x-slot:data>
                                </x-layout.summary.label-data>
                            @endif
                            @if (!empty($account_name))
                                <x-layout.summary.label-data>
                                    <x-slot:label>Account Name</x-slot:label>
                                    <x-slot:data>{{ $account_name }}</x-slot:data>
                                </x-layout.summary.label-data>
                            @endif
                            @if (!empty($amount))
                                <x-layout.summary.label-data>
                                    <x-slot:label>Amount</x-slot:label>
                                    <x-slot:data>{{ \Number::currency($amount, 'PHP') }}</x-slot:data>
                                </x-layout.summary.label-data>
                            @endif
                            @if ($this->service_fee > 0)
                                <x-layout.summary.label-data>
                                    <x-slot:label>Service Fee</x-slot:label>
                                    <x-slot:data>{{ \Number::currency($this->service_fee, 'PHP') }}</x-slot:data>
                                </x-layout.summary.label-data>
                            @endif
                            @if (!empty($amount) and $this->service_fee > 0)
                                <x-layout.summary.label-data>
                                    <x-slot:label>Total</x-slot:label>
                                    <x-slot:data>{{ \Number::currency($amount + $this->service_fee, 'PHP') }}</x-slot:data>
                                </x-layout.summary.label-data>
                            @endif
                        </x-slot:data>
                    </x-layout.summary.section>
                    @if (!empty($phone_number) or !empty($email))
                        <x-layout.summary.section title="Notification Details">
                            <x-slot:data>
                                @if (!empty($phone_number))
                                    <x-layout.summary.label-data>
                                        <x-slot:label>Phone Number</x-slot:label>
                                        <x-slot:data>{{ '(+' . $this->get_phone_iso . ') ' . $phone_number }}</x-slot:data>
                                    </x-layout.summary.label-data>
                                @endif
                                @if (!empty($email))
                                    <x-layout.summary.label-data>
                                        <x-slot:label>Email Address</x-slot:label>
                                        <x-slot:data>{{ $email }}</x-slot:data>
                                    </x-layout.summary.label-data>
                                @endif
                            </x-slot:data>
                        </x-layout.summary.section>
                    @endif
                    @if (!empty($message))
                        <x-layout.summary.section title="Message">
                            <x-slot:data>
                                <p>{{ $message }}</p>
                            </x-slot:data>
                        </x-layout.summary.section>
                    @endif
                @endif
            @elseif ($transaction_type === 'bill-payment')
                <x-layout.summary.transaction-info>
                    <x-slot:info_block>
                        @if (!empty($biller_type))
                            <div class="gap-2 text-center">
                                <p class="font-bold text-rp-neutral-700 text-xl text-balance">{{ $this->get_biller_name }}</p>
                                <p class="text-sm">{{ $this->get_biller_description }}</p>
                            </div>
                        @endif
                    </x-slot:info_block>
                </x-layout.summary.transaction-info>
                <x-layout.summary.section title="Payment Details">
                    <x-slot:data>
                        <x-layout.summary.label-data>
                            <x-slot:label>Transaction type</x-slot:label>
                            <x-slot:data>Bill Payment</x-slot:data>
                        </x-layout.summary.label-data>
                        @if (!empty($biller_type))
                            @foreach ($this->get_biller_input_fields as $key => $input_field)
                                @if (!empty($bill_info[$key]))
                                    <x-layout.summary.label-data>
                                        <x-slot:label>{{ $input_field['label'] }}</x-slot:label>
                                        <x-slot:data>{{ $bill_info[$key] ?? '' }}</x-slot:data>
                                    </x-layout.summary.label-data>
                                @endif
                            @endforeach
                            <x-layout.summary.label-data>
                                <x-slot:label>Service Charge</x-slot:label>
                                <x-slot:data>{{ $this->get_biller_service_charge ? \Number::currency($this->get_biller_service_charge, 'PHP') : 'Free of Charge' }}</x-slot:data>
                            </x-layout.summary.label-data>
                        @endif
                        @if (!empty($amount))
                            <x-layout.summary.label-data>
                                <x-slot:label>Amount</x-slot:label>
                                <x-slot:data>{{ \Number::currency($amount, 'PHP') }}</x-slot:data>
                            </x-layout.summary.label-data>
                            <x-layout.summary.label-data>
                                <x-slot:label>Total</x-slot:label>
                                <x-slot:data class="text-rp-red-500">
                                    {{ \Number::currency($amount + ($this->get_biller_service_charge ?? 0), 'PHP') }}
                                </x-slot:data>
                            </x-layout.summary.label-data>
                        @endif
                    </x-slot:data>
                </x-layout.summary.section>
            @endif
        </x-slot:body>
        <x-slot:action>
            <div class="flex gap-3 items-center mb-5">
                {{-- I agree --}}
                <x-input type="checkbox" wire:model.boolean.live="agreed_to_correct_info" id="agree" />
                <label for="agree" class="text-rp-neutral-600 cursor-pointer">I agree that the above information is
                    correct.</label>
            </div>
            <div class="flex flex-col gap-3">
                <x-button.filled-button @click="onSubmit" :disabled="$this->button_submit_clickable == false" form="cash-outflow-form"
                    size="md">send</x-button.filled-button>
                @if ($transaction_type === 'bill-payment')
                    <x-button.outline-button href="{{ route('user.bills') }}" size="md"
                        color="red">cancel</x-button.outline-button>
                @else    
                    <x-button.outline-button href="{{ route('user.cash-outflow.index') }}" size="md"
                        color="red">cancel</x-button.outline-button>
                @endif
            </div>
        </x-slot:action>
    </x-layout.summary>

    <x-modal x-model="$wire.show_otp_modal">
        <x-modal.form-modal title="OTP Verification">
            <x-input.input-group>
                <x-slot:label>We sent an OTP verification code to your number. Enter the code here to continue.</x-slot:label>
                <div class="w-full flex flex-row gap-2">
                    <x-input class="flex-1" type="text" wire:model='otp' maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/gi, '')" />
                    <button x-bind:disabled="isCountdownNotYetZero" x-on:click="resendOTP"
                        class="w-32 p-2 font-bold text-sm text-white rounded-lg  bg-rp-red-500 hover:bg-rp-red-600 disabled:bg-rp-neutral-200 focus-within:ring-4 focus-within:outline-none duration-300 transition">
                        <span x-show="isCountdownZero">
                            RESEND OTP
                        </span>
                        <span x-show="isCountdownNotYetZero">
                            RESEND in <span x-text="countdown"></span>
                        </span>
                    </button>
                </div>
                @error('otp')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>
            
            <x-slot:action_buttons>
                <x-button.outline-button wire:click="$dispatch('closeModal')" wire:target='submit' wire:loading.attr='disabled' class="w-1/2">cancel</x-button.outline-button>
                <x-button.filled-button wire:click='submit' wire:target='submit' wire:loading.attr='disabled'
                    wire:loading.class='cursor-progress' class="w-1/2">confirm</x-button.filled-button>
            </x-slot:actions>
        </x-modal.form-modal>
    </x-modal>
    

    {{-- Loader --}}
    <x-loader.black-screen wire:loading wire:target="transaction_type,transfer_to,submit,get_biller_input_fields,cancel_otp_verification,verify_otp">
        <x-loader.clock />
    </x-loader.black-screen>

    {{-- Error message --}}
    <div x-data="{ show: $wire.entangle('apiErrorMsg') }">
        <div x-cloak x-show="show"
            class="absolute inset-0 z-100 bg-opacity-50 bg-black flex items-center justify-center"
            @click="show = false">
            <div class="p-5 bg-white border-2 border-red-600 rounded-md w-96">
                <div class="flex flex-row justify-between items-center p-4">
                    <p class="text-red-600 text-lg">Error encountered!</p>
                    <div>
                        <x-icon.error />
                    </div>
                </div>
                <hr>
                <div class="p-4 text-red-600 font-bold">
                    {{ $apiErrorMsg }}
                </div>
                <hr>
                <small class="text-xs">Click anywhere inside this box to close</small>
            </div>
        </div>
    </div>

    {{-- Success message --}}
    <div x-data="{ show: $wire.entangle('apiSuccessMsg') }">
        <div x-cloak x-show="show" @click="show = false"
            class="absolute inset-0 mw-100 mh-100 z-100 bg-opacity-50 bg-black flex items-center justify-center">
            <div class="p-5 bg-white border-2 border-rp-green-600 rounded-md w-96">
                <div class="flex flex-row justify-between items-center p-4">
                    <p class="text-rp-green-600 text-md">Transaction Successful</p>
                    <x-icon.check />
                </div>
                <hr>
                <div class="p-4 text-rp-green-600 font-bold">
                    {{ $apiSuccessMsg }}
                </div>
                <hr>
                <small class="text-xs">Click anywhere inside this box to continue</small>
            </div>
        </div>
    </div>

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
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('timerData', () => {
                return { 
                    countdown: 0,
                    counter: null,
                    onSubmit() {
                        @this.submit();
                        this.countdown = 180;

                        if (this.counter != null) clearInterval(this.counter);

                        this.counter = setInterval(() => {
                            if (this.countdown == 0) {
                                clearInterval(this.counter);
                            } else {
                                this.countdown -= 1;
                            }
                        }, 1000);
                    },

                    resendOTP() {
                        @this.resend_otp();
                        this.countdown = 180;

                        if (this.counter != null) clearInterval(this.counter);

                        this.counter = setInterval(() => {
                            if (this.countdown == 0) {
                                clearInterval(this.counter);
                            } else {
                                this.countdown -= 1;
                            }
                        }, 1000);
                    },

                    isCountdownNotYetZero() {
                        return this.countdown > 0;
                    },

                    isCountdownZero() {
                        return this.countdown === 0;
                    }
                }
            });
        });
    </script>
@endpush
