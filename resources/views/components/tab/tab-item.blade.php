@props([
    'href',
    'isActive' => false,
    'color' => 'red',
])


@php

    $colors = [
        'primary' => 'text-primary-500 border-b border-primary-500 font-bold',
        'red' => 'text-rp-red-500 border-b border-rp-red-500 font-bold',
    ];


    $activeClass = '';
    
    if($isActive) {
        $activeClass = $colors[$color] ?? '';
    }

@endphp
<a  href="{{$href}}" wire:ignore {{ $attributes->merge(['class' => $activeClass . ' ' . 'text-sm cursor-pointer text-center px-3 py-2']) }}>{{ $slot }}</a>