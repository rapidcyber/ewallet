<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'REPAY') }} | Digital Banking Solution</title>

      
        {{-- Favicon --}}
        {{-- <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"> --}}
        <link rel="icon" href="{{url('/favicon.png')}}">
        
        <!-- Scripts -->
        {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('headscripts')

        <!-- Styles -->
        @livewireStyles
        @stack('style')

        {{-- LEAFLET --}}
        {{-- <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""/>

        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script> --}}
    </head>
    
    <body class="antialiased scrollbar-hide">
        <div class="bg-rp-neutral-100 w-full">
            <div class="h-screen flex flex-row w-full">
                <div class="max-w-[250px] w-[250px] px-5 pb-5 pt-3 bg-white">
                    @livewire('merchant.seller-center.components.left-sidebar')
                </div>
                <div class="w-[calc(100%-250px)] h-full">
                    <div class="bg-white">
                        @livewire('merchant.components.seller-center-navigation-menu')
                    </div>
                    <main class="overflow-auto h-[calc(100vh-60px)]">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
        <div>
            <footer>
                <div class="bg-footer2 py-5 text-center">
                    <div class="container text-white mx-auto px-3">
                        RePay.ph is regulated by the Banko Sentral ng Pilipinas. All trademarks pertaining to RePay.ph are owned by RePay Digital Solutions. All rights reserved © 2024.
                    </div>
                </div>
            </footer>
        </div>

        @stack('modals')
        @stack('scripts')
        @livewireScripts
    </body>
</html>
