<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RePay your Bills the Smartest Way!</title>

    <link rel="icon" href="{{ url('/favicon.png') }}">
    <!-- Fonts -->

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/guest.js'])

    <!-- Styles -->
    @livewireStyles(['nonce' => csp_nonce()])
    @stack('styles')
    @stack('headscripts')
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NL49PR52" height="0" width="0"
            style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->

    <div id="app" class="flex flex-col relative min-h-screen overflow-x-hidden ">
        <div class="flex flex-col flex-1">
            {{ $slot }}
        </div>
        {{-- Footer --}}
        <div class="bg-rp-neutral-700 py-10">
            <div class="max-w-6xl px-5 xl:px-0 mx-auto flex flex-col gap-[43px] lg:gap-0 lg:flex-row">
                <div class="flex flex-col gap-[43px] sm:gap-0 sm:flex-row grow lg:w-3/5">
                    <div class="flex justify-center">
                        <a class="block sm:mx-0 w-48 h-10" href="{{ url('/') }}">
                            <img src="{{ url('images/repay-logo.svg') }}" class="w-full h-full" alt="Repay Logo">
                        </a>
                    </div>
                    <div class="flex grow justify-between">
                        <div class="flex flex-col flex-1 text-white {{-- md:w-2/12 --}} {{-- flex-1 w-3/12 mt-12 md:mt-0 --}}">
                            <h3 class="uppercase text-center font-bold">website</h3>
                            <ul class="text-center space-y-4 mt-4">
                                <li>
                                    <a href="{{ route('home') }}">Home</a>
                                </li>
                                <li>
                                    <a href="{{ route('about-us') }}">About</a>
                                </li>
                                <li>
                                    <a href="{{ route('contact-us') }}">Contact us</a>
                                </li>
                                <li>
                                    <a href="{{ route('terms-and-conditions') }}">Terms and Conditions</a>
                                </li>
                                <li>
                                    <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                                </li>
                            </ul>
                        </div>

                        <div class="flex flex-col flex-1 text-white {{-- md:w-2/12 --}} {{-- flex-1 w-3/12 mt-12 md:mt-0 --}}">
                            <h3 class="uppercase text-center font-bold">features</h3>
                            <ul class="text-center space-y-4 mt-4">
                                <li>
                                    <a href="{{ route('features.remit') }}">Remit</a>
                                </li>
                                <li>
                                    <a href="{{ route('features.explore') }}">Expense</a>
                                </li>
                                <li>
                                    <a href="{{ route('features.payments') }}">Payment</a>
                                </li>
                                <li>
                                    <a href="{{ route('features.assets') }}">Assets</a>
                                </li>
                                <li>
                                    <a href="{{ route('features.yolo') }}">Yolo</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex mx-auto w-[80%] lg:w-2/5">
                    <div class="flex flex-col items-center lg:items-start text-white">
                        <h3 class="uppercase font-bold">contact us</h3>
                        <div class="flex flex-col items-center lg:items-start gap-4 mt-4">
                            <div class="flex flex-row gap-2 justify-center md:justify-start">
                                <div class="min-w-[19px]">
                                    <img src="{{ url('/images/guest/message.svg') }}" alt="Message">
                                </div>
                                <p>solutions@repay.ph</p>
                            </div>
                            <div class="flex flex-row gap-2 justify-center md:justify-start">
                                <div class="min-w-[19px]">
                                    <img src="{{ url('/images/guest/call.svg') }}" alt="Call">
                                </div>
                                <p>(+63)920-905-8875</p>
                            </div>
                            <div class="flex flex-row gap-2 justify-center md:justify-start">
                                <div class="min-w-[19px]">
                                    <img src="{{ url('/images/guest/location.svg') }}" alt="Location">
                                </div>
                                <p class="text-left">
                                    Unit 804, CTP Alpha Tower Investment Drv.,
                                    Madrigal Business Park, Brgy. Ayala Alabang, Muntinlupa City
                                    National Capital Region (NCR), 1781
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-row mt-5">
                            {{-- <a href="#" class="cursor-pointer">
                                <img src="{{ url('/images/landing/youtube.svg') }}">
                            </a> --}}
                            <a href="https://www.linkedin.com/company/repay-digital-solutions/" target="_blank"
                                class="cursor-pointer">
                                <img src="{{ url('/images/guest/linkedin.svg') }}" alt="LinkedIn">
                            </a>
                            <a href="https://www.facebook.com/repayph" target="_blank" class="cursor-pointer">
                                <img src="{{ url('/images/guest/facebook.svg') }}" alt="Facebook">
                            </a>
                            <a href="https://www.tiktok.com/@repayph" target="_blank" class="cursor-pointer">
                                <img src="{{ url('/images/guest/tiktok.svg') }}" alt="Tiktok">
                            </a>
                            <a href="https://www.instagram.com/repay_ph?igsh=MWI0dzFkcG1yYXNxdA==" target="_blank"
                                class="cursor-pointer">
                                <img src="{{ url('/images/guest/insta.svg') }}" alt="Instagram">
                            </a>
                        </div>
                    </div>
                </div>
                {{-- <div class="text-white">
                        RePay is an e-wallet service powered by Repay Digital Banking Solutions. All trademarks pertaining to RePay is owned by RePay Digital Solutions. All rights reserved.
                    </div> --}}
            </div>
        </div>
        <div class="bg-[#37474F] py-10 text-center text-white px-3">
            <p class="text-[13.33px]">RePay.ph is regulated by the Banko Sentral ng Pilipinas. All trademarks pertaining
                to RePay.ph are owned by RePay Digital Solutions. All rights reserved © 2024.</p>
        </div>
    </div>

    @stack('modals')
    @livewireScripts(['nonce' => csp_nonce()])
    @stack('scripts')
</body>

</html>
