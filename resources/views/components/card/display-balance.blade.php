@props([
    'title' => "Available Balance",
    'balance' => 0,
    'color' => 'red',
])

@php
    $colors = [
        'primary' => 'bg-purple-gradient-to-right',
        'red' => 'bg-pink-gradient-to-right'
    ];

@endphp

<div {{ $attributes->merge(['class' => "flex justify-center text-white shadow-lg flex-1 flex-col px-6 py-7 bg-white text-lg rounded-xl mt-3 ". $colors[$color]]) }}>
    <p class="text-[19.2px] ">{{ $title }}</p>
    <h1 class="text-3.5xl font-bold">{{ \Number::currency($balance, 'PHP') }}</h1>
</div>