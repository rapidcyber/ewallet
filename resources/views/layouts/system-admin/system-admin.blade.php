<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'REPAY SYSAD') }} | Digital Banking Solution</title>

    {{-- Favicon --}}
    {{-- <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"> --}}
    <link rel="icon" href="{{ url('/favicon.png') }}">

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body class="antialiased scrollbar-hide">
    <div class="bg-gray-200">
        @livewire('system-admin.system-admin-navigation-menu')
    </div>
    <div class="p-6">
        {{ $slot }}
    </div>
    @stack('scripts')
    @livewireScripts
</body>

</html>
