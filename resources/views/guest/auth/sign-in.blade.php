<x-guest.hero-section-wrapper>
    <x-guest.navigation whiteLogo="true" whiteText="true" />
    <div class="w-full h-auto flex flex-col align-center mb-10 mt-5">
        @if (session()->has('error'))
            <x-toasts.error :title="session('error')" :message="session('error_message')" />
        @elseif (session()->has('success'))
            <x-toasts.success :title="session('success')" :message="session('success_message')" />
        @endif
        {{-- CARD --}}
        <div x-data="timerData"
            class="mx-auto my-auto flex flex-row bg-white rounded-lg drop-shadow-md w-max md:w-[80vw] lg:w-[850px]">
            {{-- IMAGE --}}
            <div class="bg-cover bg-center rounded-l-lg hidden md:w-1/2 md:block h-auto holding-shopping-bag"></div>

            @php
                $agent = new Jenssegers\Agent\Agent();
                $isMobile = $agent->isMobile();
                $os = $agent->platform();
            @endphp


            @if ($signup || $isMobile)
                {{-- Sign Up --}}
                <div class="w-[80vw] max-w-[400px] md:max-w-none md:w-1/2 h-[500px] p-7 flex grow flex-col ">

                    <h2 class="font-bold text-xl text-center text-rp-neutral-700">Become a RePay user!</h2>
                    <p class="text-center text-sm break-words">Build your wealth today by becoming a RePay user!</p>

                    <div class="flex flex-col gap-6 md:flex-row mt-5">
                        <a href="https://apps.apple.com/us/app/repay-digital-banking/id6446475056" target="_blank"
                            class="w-46 md:w-54 cursor-pointer">
                            <img src="{{ url('/images/guest/app-store.svg') }}" class="w-full">
                        </a>
                        <a href="https://play.google.com/store/apps/details?id=com.repay.app" target="_blank"
                            class="w-46 md:w-54 cursor-pointer">
                            <img src="{{ url('/images/guest/google-play.svg') }}" class="w-full">
                        </a>
                    </div>


                    @if (!$isMobile)
                        <div class="flex flex-row justify-center text-xs mt-auto">
                            <p>Already have an account?</p>
                            <button wire:click="switchView" class="ml-2 underline text-rp-red-600 cursor-pointer">
                                Sign-in here!
                            </button>
                        </div>
                    @endif
                </div>

                {{-- FORMS --}}
            @elseif (empty($verification_id))
                {{-- SIGNIN FORM --}}
                <form @submit.prevent=""
                    class="w-[80vw] max-w-[400px] md:max-w-none md:w-1/2 min-h-[500px] p-7 flex grow flex-col">
                    <h2 class="font-bold text-xl text-center text-rp-neutral-700 mb-10">Login</h2>
                
                    <x-input.input-group class="mb-6">
                        <x-slot:label for="username">RePay Username</x-slot:label>
                        <x-input type="text" id="username" wire:model.live="username"
                            placeholder="Type your username" class="{{ $errorMessage ? '!border-red-300' : '' }}">
                            <x-slot:icon>
                                <x-icon.user />
                            </x-slot:icon>
                        </x-input>
                    </x-input.input-group>

                    <div class="mb-8">
                        <label class="text-xs 2xl:text-sm text-slate-500" for="password">RePay Password</label>
                        <div x-data="password"
                            class="focus-within:ring-1 flex flex-row border-[1px] rounded-lg overflow-hidden bg-white items-center px-2 py-2 gap-2 text-rp-neutral-700 {{ empty($errorMessage) ? ' border-rp-neutral-500' : 'border-red-300' }}">
                            <div width="24" height="24">
                                <x-icon.lock />
                            </div>
                            <input :type="inputType" id="password"
                                class="w-full appearance-none bg-white border-none font-thin outline-none focus:ring-0 text-base p-0 placeholder:text-neutral-400 2xl:text-base"
                                placeholder="Type your password" wire:model.live="password">
                            <template x-if="showPass">
                                <div x-ref="hide_pass" @keyup.enter="showPass=false;" @click="hidePasswordClick"
                                    tabindex="0">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M0.31952 0.31952C0.745546 -0.106507 1.43627 -0.106507 1.8623 0.31952L23.6805 22.1377C24.1065 22.5637 24.1065 23.2545 23.6805 23.6805C23.2545 24.1065 22.5637 24.1065 22.1377 23.6805L17.6064 19.1492C16.0302 20.0714 14.1509 20.7273 12 20.7273C8.10925 20.7273 5.11744 18.5883 3.16068 16.5794C2.1738 15.5662 1.42037 14.5567 0.913337 13.801C0.659158 13.4222 0.465139 13.1046 0.333062 12.8786C0.266982 12.7655 0.216284 12.6752 0.181195 12.6113C0.163647 12.5794 0.149992 12.5541 0.140257 12.5359L0.128596 12.5139L0.124975 12.507L0.123228 12.5037C0.123021 12.5033 0.12283 12.5029 1.09091 12C0.12283 11.4971 0.122956 11.4969 0.123089 11.4966L0.12339 11.496L0.12412 11.4946L0.126086 11.4909L0.132043 11.4796C0.136913 11.4704 0.143582 11.4579 0.15204 11.4422C0.168953 11.4108 0.193035 11.3668 0.224215 11.3113C0.286553 11.2002 0.377388 11.043 0.496159 10.8489C0.733438 10.4612 1.08371 9.92415 1.54267 9.31373C2.26985 8.34659 3.28609 7.17361 4.57671 6.11949L0.31952 1.8623C-0.106507 1.43627 -0.106507 0.745546 0.31952 0.31952ZM6.1286 7.67138C4.93602 8.61297 3.97929 9.70358 3.28655 10.6249C2.87584 11.1712 2.56426 11.6494 2.35711 11.9879C2.35463 11.9919 2.35216 11.996 2.3497 12C2.44978 12.1639 2.57511 12.3618 2.72509 12.5853C3.1788 13.2615 3.85099 14.1611 4.72362 15.057C6.48587 16.8662 8.9486 18.5455 12 18.5455C13.476 18.5455 14.8117 18.1543 16.0004 17.5432L14.2166 15.7594C13.5669 16.143 12.8086 16.3637 12 16.3637C9.58998 16.3637 7.63636 14.4101 7.63636 12.0001C7.63636 11.1913 7.85706 10.4331 8.24072 9.7835L6.1286 7.67138ZM9.89192 11.4347C9.84379 11.6149 9.81818 11.8044 9.81818 12.0001C9.81818 13.2051 10.795 14.1819 12 14.1819C12.1958 14.1819 12.3852 14.1563 12.5654 14.1082L9.89192 11.4347ZM1.09091 12L0.123089 11.4966C-0.0406844 11.8119 -0.0409433 12.1876 0.12283 12.5029L1.09091 12ZM12 5.45454C11.6708 5.45454 11.3494 5.47396 11.0359 5.51066C10.4375 5.58071 9.89563 5.15239 9.82558 4.55398C9.75553 3.95557 10.1839 3.41368 10.7823 3.34364C11.1787 3.29723 11.5848 3.27273 12 3.27273C15.8907 3.27273 18.8825 5.4117 20.8393 7.42064C21.8262 8.43384 22.5796 9.44333 23.0867 10.199C23.3408 10.5778 23.5349 10.8954 23.6669 11.1214C23.733 11.2345 23.7837 11.3248 23.8188 11.3887C23.8363 11.4206 23.85 11.4459 23.8597 11.4641L23.8714 11.4861L23.875 11.493L23.8763 11.4954L23.8768 11.4963C23.877 11.4967 23.8772 11.4971 22.9091 12C23.8772 12.5029 23.8771 12.5031 23.877 12.5032L23.8754 12.5063L23.8725 12.5119L23.8633 12.5293C23.8557 12.5436 23.8451 12.5633 23.8315 12.5882C23.8043 12.6379 23.7653 12.7082 23.7145 12.7965C23.613 12.9729 23.4643 13.2219 23.2696 13.5233C22.8808 14.1248 22.3046 14.9412 21.5499 15.8076C21.1541 16.2619 20.465 16.3094 20.0107 15.9137C19.5564 15.5179 19.5089 14.8288 19.9047 14.3745C20.577 13.6027 21.0918 12.8735 21.4371 12.3391C21.5177 12.2144 21.5888 12.1008 21.6503 12C21.5502 11.8361 21.4249 11.6382 21.2749 11.4147C20.8212 10.7385 20.149 9.83889 19.2764 8.94299C17.5141 7.13375 15.0514 5.45454 12 5.45454ZM22.9091 12L23.877 12.5032C24.0408 12.1879 24.0409 11.8124 23.8772 11.4971L22.9091 12ZM2.05836 12.5041C2.05825 12.5043 2.05826 12.5043 2.05836 12.5041V12.5041Z"
                                            fill="#7F56D9" />
                                    </svg>
                                </div>
                            </template>
                            <template x-if="isPasswordHidden">
                                <div x-ref="show_pass" @keyup.enter="showPass=true;" @click="showPasswordClick"
                                    tabindex="0">
                                    <svg class="cursor-pointer" width="18" height="18" viewBox="0 0 24 20"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M2.29923 10C2.40421 10.1801 2.53962 10.4038 2.70466 10.6593C3.16073 11.3655 3.83601 12.3044 4.71237 13.2392C6.48466 15.1296 8.95222 16.8732 12 16.8732C15.0478 16.8732 17.5153 15.1296 19.2876 13.2392C20.164 12.3044 20.8393 11.3655 21.2953 10.6593C21.4604 10.4038 21.5958 10.1801 21.7008 10C21.5958 9.81991 21.4604 9.59627 21.2953 9.34073C20.8393 8.63456 20.164 7.69567 19.2876 6.76089C17.5153 4.87045 15.0478 3.12687 12 3.12687C8.95222 3.12687 6.48466 4.87045 4.71237 6.76089C3.83601 7.69567 3.16073 8.63456 2.70466 9.34073C2.53962 9.59627 2.40421 9.81991 2.29923 10ZM22.9266 10C23.8867 10.4801 23.8865 10.4804 23.8863 10.4808L23.8858 10.4817L23.8846 10.4842L23.8811 10.4911L23.8696 10.5136C23.86 10.5323 23.8465 10.5584 23.829 10.5913C23.7942 10.6572 23.7437 10.7506 23.6778 10.8677C23.5462 11.1018 23.3526 11.4311 23.0988 11.824C22.5926 12.6079 21.84 13.6556 20.8539 14.7075C18.9012 16.7904 15.9055 19.0201 12 19.0201C8.09452 19.0201 5.0988 16.7904 3.14613 14.7075C2.16001 13.6556 1.40739 12.6079 0.901179 11.824C0.647408 11.4311 0.453807 11.1018 0.322164 10.8677C0.256302 10.7506 0.205827 10.6572 0.17097 10.5913C0.153538 10.5584 0.14 10.5323 0.130386 10.5136L0.118906 10.4911L0.115378 10.4842L0.1137 10.4808C0.113505 10.4804 0.113326 10.4801 1.07345 10C0.113327 9.51995 0.113506 9.51959 0.113701 9.5192L0.114168 9.51827L0.115379 9.51586L0.118907 9.50888L0.130387 9.48641C0.140001 9.46771 0.153539 9.44164 0.170971 9.4087C0.205828 9.34282 0.256303 9.24938 0.322165 9.13229C0.453808 8.89825 0.647409 8.56892 0.901181 8.17599C1.40739 7.39217 2.16001 6.34441 3.14613 5.29254C5.0988 3.2097 8.09452 0.97998 12 0.97998C15.9055 0.97998 18.9012 3.2097 20.8539 5.29254C21.84 6.34441 22.5926 7.39217 23.0988 8.17599C23.3526 8.56892 23.5462 8.89825 23.6778 9.13229C23.7437 9.24938 23.7942 9.34282 23.829 9.4087C23.8465 9.44164 23.86 9.46771 23.8696 9.48641L23.8811 9.50888L23.8846 9.51586L23.8863 9.5192C23.8865 9.51959 23.8867 9.51995 22.9266 10ZM22.9266 10L23.8867 10.4801C24.0378 10.1779 24.0378 9.82215 23.8867 9.51995L22.9266 10ZM1.07345 10L0.113326 10.4801C-0.0377756 10.1779 -0.0377753 9.82215 0.113327 9.51995L1.07345 10ZM12 8.0935C10.9471 8.0935 10.0935 8.94707 10.0935 10C10.0935 11.053 10.9471 11.9065 12 11.9065C13.053 11.9065 13.9066 11.053 13.9066 10C13.9066 8.94707 13.053 8.0935 12 8.0935ZM7.94663 10C7.94663 7.76138 9.7614 5.9466 12 5.9466C14.2387 5.9466 16.0535 7.76138 16.0535 10C16.0535 12.2387 14.2387 14.0534 12 14.0534C9.7614 14.0534 7.94663 12.2387 7.94663 10Z"
                                            fill="#7F56D9" />
                                    </svg>
                                </div>
                            </template>
                        </div>
                        <a href="{{ route('forgot-password') }}"
                            class="block mt-1 text-right text-xs text-rp-neutral-500">Forgot password?</a>
                    </div>


                    {{-- Error message --}}
                    @if (!empty($errorMessage))
                        <p class=" text-center text-red-500 mb-10 break-words">
                            {{ $errorMessage }}
                        </p>
                    @endif

                    {{-- Button --}}
                    <x-button.filled-button type="submit" x-on:click="onSubmit"
                        class="mb-4">login</x-button.filled-button>

                    {{-- Card Footer --}}
                    <div class="flex flex-row justify-center text-xs mt-auto">
                        <p>Want to sell on RePay?</p>
                        <button wire:click="switchView" class="ml-2 underline text-rp-red-500 cursor-pointer">
                            Sign-up
                            here!
                        </button>
                    </div>
                </form>
            @elseif (!$otp_valid)
                {{-- OTP FORM --}}

                <div
                    class="w-[80vw] max-w-[400px] md:max-w-none md:w-1/2 h-[500px] p-7 flex grow flex-col justify-center">
                    <h2 class="font-bold text-xl text-center text-rp-neutral-700">VERIFY OTP</h2>

                    <p class="mt-10 text-center">We have sent an OTP to your mobile number!</p>

                    <div class="mt-20 w-full">
                        <input
                            class=" {{ $invalidOTP ? 'border-red-400' : '' }} border appearance-none focus:ring-1 px-2 py-2 border-rp-neutral-500   text-rp-neutral-700 rounded-lg w-full text-center outline-none"
                            type="text" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/gi, '')"
                            wire:model.live="otp">

                        <div class="flex flex-row mt-8">
                            <button {{ strlen($otp) == 6 ? '' : 'disabled' }}
                                class="p-2 font-bold text-sm text-white bg-rp-red-500 hover:bg-rp-red-600 rounded-lg disabled:bg-rp-neutral-200 basis-6/12 focus:ring-4 focus:outline-none duration-300 transition"
                                wire:click="submitOTP">
                                VERIFY OTP
                            </button>
                            <div class="basis-1/12"></div>
                            <button x-bind:disabled="isCountdownNotYetZero" x-on:click="resendOTP"
                                class="p-2 font-bold text-sm text-white rounded-lg  bg-rp-red-500 hover:bg-rp-red-600 disabled:bg-rp-neutral-200 basis-6/12 focus-within:ring-4 focus-within:outline-none duration-300 transition">
                                <span x-show="isCountdownZero">
                                    RESEND CODE
                                </span>
                                <span x-show="isCountdownNotYetZero">
                                    RESEND in <span x-text="countdown"></span>s
                                </span>
                            </button>
                        </div>
                    </div>
                    @if ($invalidOTP)
                        <p class="mt-5 text-red-500 text-center">Invalid OTP</p>
                    @endif

                    <span class="flex-grow"></span>
                    <div class="flex flex-row justify-center text-xs">
                        <p>Wrong Account?</p>
                        <a href="{{ route('sign-in') }}" class="ml-2 underline text-rp-red-500">
                            Login to another account here.
                        </a>
                    </div>
                </div>
            @else
                <div
                    x-ref="loginContainer"
                    id="loginContainer"
                    
                    
                    class="w-[80vw] max-w-[400px] md:max-w-none md:w-1/2 h-[500px] p-7 flex grow flex-col justify-center">
                    <h2 class="font-bold text-xl text-center text-rp-neutral-700 mb-10">Login</h2>
                
                    <p class="mt-10 text-center">Please input your PIN to log-in</p>

                    <form @submit.prevent="submitPin" class="mt-16 w-full" x-data="pin">

                        <div class="flex justify-center gap-4">
                            {{-- 1ST DIGIT --}}
                            <div class="">
                                <input x-ref="pin1" type="text" 
                                    x-init="focusFirstDigitInput"
                                    pattern="[0-9]" :readonly="isCurrenPositionNotFirst" inputmode="numeric"
                                    maxlength="1"
                                    class="appearance-none text-2xl text-center outline-none w-11 max-w-11"
                                    {{-- @input="() => {
                                
                                    if(isNaN($event.target.value) || $event.target.value === ' ') {
                                        $event.target.value = '';
                                        pin[0] = '';
                                        return;
                                    }

                                    if ($event.target.value.length === 1) { 
                                        currentPosition = 2;
                                        $refs.pin2.focus(); 
                                    }
                                }" --}} @input="handleFirstDigitInput" />
                                <div class="w-11 h-[1px] bg-rp-red-600 mt-3"></div>
                            </div>
                            {{-- 2ND DIGIT --}}
                            <div class="">
                                <input x-ref="pin2" type="text"  pattern="[0-9]"
                                    :readonly="isCurrentPositionNotSecond" inputmode="numeric" maxlength="1"
                                    class="appearance-none text-2xl text-center outline-none w-11 max-w-11"
                                    {{-- @input="() => {
                                    
                                    if(isNaN($event.target.value) || $event.target.value === ' ') {
                                        $event.target.value = '';
                                        pin[1] = '';
                                        return;
                                    }   
    
                                    if ($event.target.value.length === 1) { 
                                        currentPosition = 3;
                                        $refs.pin3.focus();
                                    }
                                }" --}} @input="handleSecondDigitInput"
                                    {{-- @keydown.backspace="if ($event.target.value === '') { 
                                    currentPosition = 1;
                                    pin[0] = '';
                                    $refs.pin1.focus(); 
                                }" --}} @keydown.backspace="handleSecondDigitBackspace" />
                                <div class="w-11 h-[1px] bg-rp-red-600 mt-3"></div>
                            </div>
                            {{-- 3RD DIGIT --}}
                            <div class="">
                                <input x-ref="pin3" type="text"  pattern="[0-9]"
                                    :readonly="isCurrentPositionNotThird" inputmode="numeric" maxlength="1"
                                    class="appearance-none text-2xl text-center outline-none w-11 max-w-11"
                                    {{-- @input="() => {
                                    
                                    if(isNaN($event.target.value) || $event.target.value === ' ') {
                                        $event.target.value = '';
                                        pin[2] = '';
                                        return;
                                    }
    
                                    if ($event.target.value.length === 1) { 
                                        currentPosition = 4;
                                        $refs.pin4.focus(); 
                                    }
                                }" --}} @input="handleThirdDigitInput"
                                    {{-- @keydown.backspace="if ($event.target.value === '') { 
                                    currentPosition = 2;
                                    pin[1] = '';
                                    $refs.pin2.focus(); 
                                }" --}} @keydown.backspace="handleThirdDigitBackspace" />
                                <div class="w-11 h-[1px] bg-rp-red-600 mt-3"></div>
                            </div>
                            {{-- 4TH DIGIT --}}
                            <div class="">
                                <input x-ref="pin4" type="text"  pattern="[0-9]"
                                    :readonly="isCurrentPositionNotFourth" inputmode="numeric" maxlength="1"
                                    class="appearance-none text-2xl text-center outline-none w-11 max-w-11"
                                    {{-- @input="() => {
    
                                    if(isNaN($event.target.value) || $event.target.value === ' ') {
                                        $event.target.value = '';
                                        pin[3] = '';
                                        return;
                                    }
    
                                    if ($event.target.value.length === 1) { 
                                        currentPosition = 4;
                                    }
                                }" --}} @input="handleFourthDigitInput"
                                    {{-- @keydown.backspace="if ($event.target.value === '') { 
                                    currentPosition = 3;
                                    pin[2] = '';
                                    $refs.pin3.focus(); 
                                }" --}} @keydown.backspace="handleFourthDigitBackspace" />
                                <div class="w-11 h-[1px] bg-rp-red-600 mt-3"></div>
                            </div>
                        </div>

                        <div class="w-full mt-16">
                            <x-button.filled-button class="w-full" x-bind="loginPinSubmitButton"
                            >login</x-button.filled-button>
                        </div>  

                        
                    </form>
                    @error('pin')
                        <p class="mt-5 text-red-500 text-center">{{ $message }}</p>
                    @enderror

                    {{-- <span class="flex-grow"></span> --}}
                    <div class="flex flex-row justify-center text-xs mt-auto">
                        <p>Wrong Account?</p>
                        <a href="{{ route('sign-in') }}" class="ml-2 underline text-rp-red-500">
                            Login to another account here.
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <x-loader.black-screen wire:loading wire:target='submit,submitOTP,loginWithPin'>
            <x-loader.clock />
        </x-loader.black-screen>


    </div>

</x-guest.hero-section-wrapper>



@push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('alpine:init', () => {
            Alpine.data('timerData', () => {
                return { 
                    countdown: 0,
                    counter: null, /// container for setInterval timer.
                    onSubmit() {
                        @this.submit(); /// access livewire `submit` function using `@this`.
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

                    resendOTP() {
                        @this.resendOTP(); /// access livewire `submit` function using `@this`.
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

                    isCountdownNotYetZero() {
                        return this.countdown > 0;
                    },

                    isCountdownZero() {
                        return this.countdown === 0;
                    }

                    
                }
            });

            Alpine.data('password', () => {
                return {
                    showPass: false,

                    get isPasswordHidden() {
                        return !this.showPass;
                    },

                    
                    inputType() {
                        return this.showPass ? 'text' : 'password';
                    },

                    showPasswordClick() {
                        this.showPass = true;
                    },

                    hidePasswordClick() {
                        this.showPass = false;
                    }
                }
            });

            Alpine.data('pin', () => {
                return {
                    pin: ['', '', '', ''],

                    firstDigitPin: '',
                    secondDigitPin: '',
                    thirdDigitPin: '',
                    fourthDigitPin: '',

                    currentPosition: 1,

                    init() {
                        console.log('test pin init');
                        this.isPinComplete();

                    },      

                    pinInit() {
                        alert('heeloo');
                        document.querySelector('#loginContainer').click();
                    },
                    
                    focusFirstDigitInput() {
                        this.$refs.pin1.focus();    
                    },

                    isPinComplete() {
                        // return this.pin[0].length === 1 && this.pin[1].length === 1 && this.pin[2].length === 1 && this.pin[3].length === 1;

                        return this.firstDigitPin.length === 1 && this.secondDigitPin.length === 1 && this.thirdDigitPin.length === 1 && this.fourthDigitPin.length === 1;
                    },

                    isPinIncomplete() {
                        return !this.isPinComplete();
                    },

                    isCurrenPositionNotFirst() {
                        return this.currentPosition !== 1;
                    },

                    isCurrentPositionNotSecond() {
                        return this.currentPosition !== 2;
                    },

                    isCurrentPositionNotThird() {
                        return this.currentPosition !== 3;
                    },

                    isCurrentPositionNotFourth() {
                        return this.currentPosition !== 4;
                    },

                    handleFirstDigitInput(event) {
                        this.firstDigitPin = event.target.value;
                        if(isNaN(event.target.value) || event.target.value === ' ') {
                            event.target.value = '';
                            // this.pin[0] = '';
                            this.firstDigitPin = '';
                            return;
                        }

                        if (event.target.value.length === 1) { 
                            this.firstDigitPin = event.target.value;
                            this.currentPosition = 2;
                            this.$refs.pin2.focus(); 
                        }
                    },

                    handleSecondDigitInput(event) {
                        this.secondDigitPin = event.target.value;
                        if(isNaN(event.target.value) || event.target.value === ' ') {
                            event.target.value = '';
                            // this.pin[1] = '';
                            this.secondDigitPin = '';
                            return;
                        }   

                        if (event.target.value.length === 1) { 
                            this.secondDigitPin = event.target.value;
                            this.currentPosition = 3;
                            this.$refs.pin3.focus();
                        }
                    },

                    handleThirdDigitInput(event) {
                        this.thirdDigitPin = event.target.value;
                        if(isNaN(event.target.value) || event.target.value === ' ') {
                            event.target.value = '';
                            // this.pin[2] = '';
                            this.thirdDigitPin = '';
                            return;
                        }

                        if (event.target.value.length === 1) {
                            this.thirdDigitPin = event.target.value;
                            this.currentPosition = 4;
                            this.$refs.pin4.focus(); 
                        }
                    },

                    handleFourthDigitInput(event) {
                        this.fourthDigitPin = event.target.value;
                        if(isNaN(event.target.value) || event.target.value === ' ') {
                            event.target.value = '';
                            this.fourthDigitPin = '';
                            return;
                        }

                        if (event.target.value.length === 1) { 
                            this.fourthDigitPin = event.target.value;
                            this.currentPosition = 4;
                        }
                    },

                    handleSecondDigitBackspace(event) {
                        if (event.target.value === '') { 
                            this.currentPosition = 1;
                            // this.pin[0] = '';
                            this.$refs.pin1.focus(); 
                        }
                    },

                    handleThirdDigitBackspace(event) {
                        if (event.target.value === '') { 
                            this.currentPosition = 2;
                            // this.pin[1] = '';
                            this.$refs.pin2.focus(); 
                        }
                    },

                    handleFourthDigitBackspace(event) {
                        if (event.target.value === '') { 
                            this.currentPosition = 3;
                            // this.pin[2] = '';
                            this.$refs.pin3.focus(); 
                        }
                    },

                    submitPin() {
                        @this.loginWithPin([this.firstDigitPin, this.secondDigitPin, this.thirdDigitPin, this.fourthDigitPin]);
                    }
                }
            });

            Alpine.bind('loginPinSubmitButton', () => {
                return {
                    type: 'submit',
                    ':disabled'() {
                        return this.isPinIncomplete();
                    }
                }
            });

        });
        
    </script>
@endpush
