<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }} - Authorization</title>

    <!-- Styles -->
    {{-- <link href="{{ asset('/css/app.css') }}" rel="stylesheet"> --}}

    @vite(['resources/css/app.css'])
</head>
<body>
    <x-guest.hero-section-wrapper class="!h-screen !justify-center">     
        <div class="flex flex-col justify-between gap-6 bg-white px-6 py-5 rounded-lg w-[490px] max-w-[90%]">
            <div class="space-y-7">
                <div class="mt-5">
                    <img src="{{ url('images/isolated-repay-logo-colored.png') }}" class="mx-auto" alt="Repay Logo"> 
                </div>
                <h1 class="text-center text-rp-neutral-700 font-bold text-2xl">Authorize App</h1>
                <div class="flex items-center justify-center">
                    @php
                        $profile_picture = auth()->user()->getFirstMedia('profile_picture') 
                            ? (auth()->user()->getFirstMedia('profile_picture')->disk === 's3' 
                                ? auth()->user()->getFirstMedia('profile_picture')->getTemporaryUrl(\Carbon\Carbon::now()->addMinutes(5), 'thumbnail')
                                : auth()->user()->getFirstMedia('profile_picture')->getUrl()) 
                            : url('images/user/default-avatar.png');                        
                    @endphp
                    {{-- User Profile Photo --}}
                    <div class="w-20 h-auto">
                        <img src="{{$profile_picture}}" class="w-full h-full object-cover rounded-full" alt="User Profile Photo">
                    </div>
                    <div class="ml-8 mr-6">
                        <img src="{{ url('images/icon/iconamoon-swap-light.png') }}" alt="Authorize">
                    </div>
                    {{-- Realholmes Logo --}}
                    <div class="w-[104px] h-auto">
                        <img src="{{ url('images/realholmes-logo.png') }}" class="w-full h-full" alt="RealHolmes Logo">
                    </div>
                </div>
                <div class="text-center">
                    <h2 class="text-rp-neutral-700 font-bold text-lg mb-2">Hi, {{ auth()->user()->profile->first_name }} {{ auth()->user()->profile->surname }}!</h2>
                    <p class="text-rp-neutral-700"><strong>{{ ucfirst($client->name) }}</strong> is requesting permission to access your account.</p>
                </div>
                <div class="border rounded-xl py-3 pr-2 border-rp-neutral-500">
                    <ul class="ml-9">
                        @foreach ($scopes as $scope)
                            <li class="list-disc text-rp-neutral-700">{{ $scope->description }}</li>
                        @endforeach
                        {{-- <li class="list-disc text-rp-neutral-700">Profile:  username, email, profile picture.</li>
                        <li class="list-disc text-rp-neutral-700">Another scope description</li>
                        <li class="list-disc text-rp-neutral-700">And another scope desc.</li> --}}
                    </ul>
                </div>
            </div>

            <div class="flex flex-row gap-3">
                <form method="post" action="{{ route('passport.authorizations.deny') }}" class="w-1/2">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="state" value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <x-button.outline-button class="w-full">Decline</x-button.outline-button>
                </form>
                
                
                <form method="post" action="{{ route('passport.authorizations.approve') }}"  class="w-1/2">
                    @csrf
                    <input type="hidden" name="state" value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <x-button.filled-button class="w-full">Accept</x-button.filled-button>
                </form>
            </div>
        </div>
    </x-guest.hero-section-wrapper>
</body>
</html>
