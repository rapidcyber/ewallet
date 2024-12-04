<x-guest.hero-section-wrapper>
    <x-guest.navigation whiteLogo="true" whiteText="true" />

    <div class="flex flex-col w-full h-auto mt-5 mb-10 align-center">
        {{-- CARD --}}
        <div x-data="countdownTimer"
            class="mx-auto my-auto flex flex-row bg-white rounded-lg drop-shadow-md w-max md:w-[80vw] lg:w-[850px]">
            {{-- IMAGE --}}
            <div class="hidden h-auto bg-center bg-cover rounded-l-lg md:w-1/2 md:block holding-shopping-bag"></div>

            {{-- FORM --}}
            @if ($form_state === 'forgot_password_email_step')
                {{-- Email Step --}}

                <div class="w-[80vw] max-w-[400px] md:max-w-none md:w-1/2 h-max md:h-[500px] p-7 flex grow flex-col">
                    <div class="mb-10 ">
                        <h2 class="mb-3 text-xl font-bold text-center text-rp-neutral-700">Reset Password</h2>
                        <p class="text-center">Please input your username or mobile number. A reset code will be sent to
                            the number associated with the provided username.</p>
                    </div>
                    <x-input.input-group class="mb-6">
                        <x-slot:label for="email_or_phone">Email or Phone Number</x-slot:label>
                        <x-input type="text" id="email_or_phone" wire:model="email_or_phone"
                            placeholder="Type your email or phone number">
                            <x-slot:icon>
                                <x-icon.user />
                            </x-slot:icon>
                        </x-input>
                    </x-input.input-group>
                    {{-- Button --}}
                    <x-button.filled-button x-on:click="send_otp">send reset
                        code</x-button.filled-button>
                </div>
            @elseif ($form_state === 'forgot_password_verification_step')
                {{-- Verification Step --}}
                <div class="w-[80vw] max-w-[400px] md:max-w-none md:w-1/2 h-max md:h-[500px] p-7 flex grow flex-col">
                    <div class="mb-10">
                        <h2 class="mb-3 text-xl font-bold text-center text-rp-neutral-700">Reset Password</h2>
                        <p class="text-center">Please enter the reset code you have received.</p>
                    </div>

                    <div class="w-full mb-5">
                        <input
                            class=" {{ $invalidOTP ? 'border-red-400' : '' }} border appearance-none focus:ring-1 px-2 py-2 border-rp-neutral-500 text-rp-neutral-700 rounded-lg w-full text-center outline-none"
                            type="text" maxlength="5" wire:model="otp">

                        <div class="flex flex-row gap-3 mt-8">
                            <x-button.filled-button wire:click="verify_otp" size="lg"
                                class="w-1/2">verify otp</x-button.filled-button>

                            <x-button.filled-button x-bind:disabled="countdownNotFinished" wire:click="verify_otp" size="lg"
                                class="w-1/2">
                                <span x-show="countdownFinished">
                                    RESEND CODE
                                </span>
                                <span x-show="countdownNotFinished">
                                    RESEND in <span x-text="countdown"></span>s
                                </span>
                            </x-button.filled-button>

                        </div>
                    </div>
                </div>
            @elseif ($form_state === 'forgot_password_reset_step')
                {{-- Reset Password Step --}}
                <div class="w-[80vw] max-w-[400px] md:max-w-none md:w-1/2 h-max md:h-[650px] p-7 flex grow flex-col">
                    <h2 class="mb-10 text-xl font-bold text-center text-rp-neutral-700">Reset Password</h2>


                    <div class="mb-6">
                        <label class="text-xs text-slate-500" for="new_password">New password</label>
                        <div
                            class="focus-within:ring-1 flex flex-row border-[1px] rounded-lg overflow-hidden bg-white items-center px-2 py-2 gap-2 text-rp-neutral-700 {{ empty($errorMessage) ? ' border-rp-neutral-500' : 'border-red-300' }}">
                            <div width="24" height="24">
                                <x-icon.lock />
                            </div>
                            <input type="password" id="new_password"
                                class="w-full p-0 text-sm font-thin bg-white border-none outline-none appearance-none focus:ring-0 placeholder:text-neutral-400 2xl:text-base"
                                placeholder="Type your password" wire:model="new_password">
                        </div>
                    </div>

                    <div class="mb-10">
                        <label class="text-xs text-slate-500" for="confirm_password">Confirm password</label>
                        <div x-data="passwordField"
                            class="focus-within:ring-1 flex flex-row border-[1px] rounded-lg overflow-hidden bg-white items-center px-2 py-2 gap-2 text-rp-neutral-700 {{ empty($errorMessage) ? ' border-rp-neutral-500' : 'border-red-300' }}">
                            <div width="24" height="24">
                                <x-icon.lock />
                            </div>
                            <input type="password" id="confirm_password"
                                class="w-full p-0 text-sm font-thin bg-white border-none outline-none appearance-none focus:ring-0 placeholder:text-neutral-400 2xl:text-base"
                                placeholder="Type your password" wire:model="confirm_password">
                        </div>
                        @error('new_password')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-10">
                        <p>Password must:</p>
                        <ul>
                            <li>- at least have 1 uppercase character</li>
                            <li>- at least have 1 numeric character</li>
                            <li>- have a minimum of 8 characters</li>
                            <li>- have a maximum of 32 characters</li>
                        </ul>
                    </div>

                    <x-button.filled-button size="lg" wire:click="resetPassword">reset
                        password</x-button.filled-button>
                </div>
            @endif
        </div>

        <x-loader.black-screen wire:loading wire:target='resetPassword,resend_code,send_otp,verify_otp'>
            <x-loader.clock />
        </x-loader.black-screen>

    </div>

    @if (session()->has('success'))
        <x-toasts.success />
    @endif

    @if (session()->has('error'))
        <x-toasts.error />
    @endif

    @if (session()->has('warning'))
        <x-toasts.warning />
    @endif

</x-guest.hero-section-wrapper>



@push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('alpine:init', () => {
            Alpine.data('countdownTimer', () => {
                return {
                    countdown: 0,
                    counter: null, /// container for setInterval timer.
                    send_otp() {
                        @this.send_otp(); /// access livewire `submit` function using `@this`.
                        this.countdown = 180; /// set countdown to 180 seconds, 3 minutes.

                        /// if `counter` is not null, we clear the interval to start a new one.
                        if (this.counter != null) clearInterval(this.counter);

                        /// set `counter` a new setInterval.
                        this.counter = setInterval(() => {
                            /// if countdown is 0, clear the interval for `counter`.
                            /// if not, reduce countdown.
                            if (this.countdown == 0) {
                                clearInterval(this.counter);
                            } else {
                                this.countdown -= 1;
                            }
                        }, 1000); /// 1000ms == 1s
                    },

                    countdownNotFinished() {
                        return this.countdown > 0;
                    },
                    countdownFinished() {
                        return this.countdown == 0;
                    },
                }
            });
        });
    </script>
@endpush
