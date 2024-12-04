<div class="flex flex-col grow">
    @push('headscripts')
        <script nonce="{{ csp_nonce() }}" src="https://www.google.com/recaptcha/enterprise.js?onload=handle&render=explicit" async defer></script>
        <script nonce="{{ csp_nonce() }}">
            var handle = function(e) {
                widget = grecaptcha.enterprise.render('grecaptcha', {
                    'sitekey': '{{ config('services.recaptcha.site_key') }}',
                    'theme': 'light', // you could switch between dark and light mode.
                    'callback': verify,
                    'expired-callback': expired,
                    'error-callback': expired
                });
            }

            var verify = function(response) {
                @this.set('recaptcha', response)
            }
            var expired = function() {
                @this.set('recaptcha', null)
            }
        </script>
    @endpush
    <x-guest.hero-section-wrapper>
        <livewire:components.layout.guest.guest-navigation whiteLogo="true" whiteText="true" />
        <div class="flex flex-col h-full max-w-6xl gap-12 lg:flex-row p-9">
            <div class="flex flex-col gap-2 space-y-3 leading-7 lg:pt-24 lg:items-start lg:basis-1/2">
                <h1 class="text-4xl font-bold text-center text-white lg:text-left">We're Ready to Help You</h1>
                <p class="tracking-wide text-center text-white lg:text-left">If you have any lingering questions or
                    clarifications about our features and services, don't hesitate to reach out. Our team of experts is
                    always ready to help out!</p>
            </div>
            <div class="basis-1/2">
                <div class="w-full h-full py-12 bg-white rounded-2xl px-9">
                    <h1 class="text-3xl font-bold text-center mb-7 text-rp-neutral-700">Get in touch with us</h1>
                    <div class="space-y-5">
                        <div>
                            <x-input.input-group>
                                <x-slot:label for="full_name">Full Name</x-slot:label>
                                <x-input wire:model.live="name" type="text" id="full_name"
                                    placeholder="Enter full name" maxlength="120" class="" />
                            </x-input.input-group>

                            @if (!empty($errs['full_name']))
                                @foreach ($errs['full_name'] as $key => $val)
                                    <p class="text-red-500 text-[13.33px]">* {{ $val }}</p>
                                @endforeach
                            @endif
                        </div>
                        <div>
                            <x-input.input-group>
                                <x-slot:label for="email">Email Address</x-slot:label>
                                <x-input wire:model.live="email" id="email" type="email"
                                    placeholder="Enter email address" />
                            </x-input.input-group>

                            @if (!empty($errs['email']))
                                @foreach ($errs['email'] as $key => $val)
                                    <p class="text-red-500 text-[13.33px]">* {{ $val }}</p>
                                @endforeach
                            @endif
                        </div>
                        <div>
                            <x-input.input-group>
                                <x-slot:label for="subject">Subject</x-slot:label>
                                <x-input wire:model.live="subject" id="subject" type="text"
                                    placeholder="Tell us what's it about!" maxlength="180" />
                            </x-input.input-group>

                            @if (!empty($errs['subject']))
                                @foreach ($errs['subject'] as $key => $val)
                                    <p class="text-red-500 text-[13.33px]">* {{ $val }}</p>
                                @endforeach
                            @endif
                        </div>
                        <div>
                            <x-input.input-group>
                                <x-slot:label for="message">Message</x-slot:label>
                                <x-input.textarea wire:model.live="message" id="message"
                                    placeholder="Type in your message!" maxlength="255" class="min-h-[135px]"
                                    data-gramm="false" data-gramm_editor="false" data-enable-grammarly="false" />
                            </x-input.input-group>

                            @if (!empty($errs['message']))
                                @foreach ($errs['message'] as $key => $val)
                                    <p class="text-red-500 text-[13.33px]">* {{ $val }}</p>
                                @endforeach
                            @endif
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="g-recaptcha" id="grecaptcha"
                                data-sitekey="{{ config('services.recaptcha.site_key') }}" wire:ignore></div>
                            @if (!empty($errs['recaptcha']))
                                @foreach ($errs['recaptcha'] as $key => $val)
                                    <p class="text-red-500 text-[13.33px]">* {{ $val }}</p>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <x-button.filled-button wire:click="onSend" size="lg" class="w-full mt-5">
                        send
                    </x-button.filled-button>

                </div>

            </div>
        </div>

    </x-guest.hero-section-wrapper>

    @if ($apiSuccessMsg)
        <div>
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 mw-100 mh-100 z-100">
                <div class="p-5 bg-white border-2 border-green-700 rounded-md w-96" wire:click="$set('apiSuccessMsg', null)">
                    <div class="flex flex-row p-4">
                        <p class="text-green-600 text-md">Inquiry Submitted</p>
                        <div class="flex-grow"></div>
                        <svg  width="24" height="24" viewBox="0 0 25 24"
                            fill="none">
                            <path
                                d="M12.5 2C6.99 2 2.5 6.49 2.5 12C2.5 17.51 6.99 22 12.5 22C18.01 22 22.5 17.51 22.5 12C22.5 6.49 18.01 2 12.5 2ZM17.28 9.7L11.61 15.37C11.47 15.51 11.28 15.59 11.08 15.59C10.88 15.59 10.69 15.51 10.55 15.37L7.72 12.54C7.43 12.25 7.43 11.77 7.72 11.48C8.01 11.19 8.49 11.19 8.78 11.48L11.08 13.78L16.22 8.64C16.51 8.35 16.99 8.35 17.28 8.64C17.57 8.93 17.57 9.4 17.28 9.7Z"
                                fill="#149D8C"></path>
                        </svg>
                    </div>
                    <hr>
                    <div class="p-4 font-bold text-green-700">
                        {{ $apiSuccessMsg }}
                    </div>
                    <hr>
                    <small class="text-xs">Click anywhere inside this box to continue</small>
                </div>
            </div>
        </div>
    @endif


    {{-- Error message --}}
    @if ($apiErrorMsg)
        <div>
            <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 mw-100 mh-100 z-100"
                wire:click="$set('apiErrorMsg', null)">
                <div class="p-5 bg-white border-2 rounded-md border-primary-500 w-96">
                    <div class="flex flex-row p-4">
                        <p class="text-lg text-primary-500">Error encountered!<br />
                        <div class="flex-grow"></div>
                        <svg height="26" viewBox="0 0 32 32"
                            width="26" xml:space="preserve" 
                            >
                            <g>
                                <g id="Error_1_">
                                    <g id="Error">
                                        <circle cx="16" cy="16" id="BG" r="16" fill="#D72828" />
                                        <path d="M14.5,25h3v-3h-3V25z M14.5,6v13h3V6H14.5z" id="Exclamatory_x5F_Sign"
                                            fill="#E6E6E6" />
                                    </g>
                                </g>
                            </g>
                        </svg>
                        </p>
                    </div>
                    <hr>
                    <div class="p-4 font-bold text-primary-500">
                        {{ $apiErrorMsg }}
                    </div>
                    <hr>
                    <small class="text-xs">Click anywhere inside this box to close</small>
                </div>
            </div>
        </div>
    @endif

    <x-loader.black-screen wire:loading.block wire:target="onSend" />
</div>
