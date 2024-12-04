<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'REPAY') }} | Digital Banking Solution</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ url('/favicon.png') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased scrollbar-hide">
    <div class="min-h-screen bg-rp-neutral-100">

        <!-- Page Content -->
        <div class="h-screen">
            <div class="flex flex-row h-full w-full">
                <!-- Left Sidebar -->
                <div class="max-w-[250px] w-[250px] px-5 pb-5 pt-3 bg-purple-gradient-to-bottom h-full overflow-auto">
                    @livewire('admin.components.admin-left-sidebar')
                </div>

                <!-- Main Content -->
                <div class="w-[calc(100%-250px)] h-full shadow border">
                    <div class="bg-white shadow-md">
                        @livewire('admin.components.admin-top-navigation')
                    </div>
                    <main class="overflow-auto h-[calc(100vh-65px)]">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="bg-footer2 py-5 text-center">
            <div class="container text-white mx-auto px-3">
                RePay.ph is regulated by the Banko Sentral ng Pilipinas. All trademarks pertaining to RePay.ph are
                owned by RePay Digital Solutions. All rights reserved © 2024.
            </div>
        </div>
    </footer>

    @stack('modals')
    @stack('scripts')
    @livewireScripts
</body>

</html>
