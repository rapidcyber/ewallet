@props([
    'size' => 'md',
    'color' => 'red',
    'href' => null,
    'disabled' => false,
])

@php
  
    $baseClass = "text-center uppercase font-bold duration-300 transition focus:ring-4 focus:outline-none";

    $sizes = [
        'sm' => 'text-xs px-1 py-2 rounded-md',
        'md' => 'text-sm p-[10px] rounded-[9px]',
        'lg' => 'text-base px-[10px] py-[10px] rounded-xl '
    ];

    $colors = [
        'primary' => 'border-2 border-primary-500 text-primary-500 hover:text-primary-700 hover:border-primary-700',
        'red' => 'border-2 border-rp-red-500 text-rp-red-500 hover:text-rp-dark-pink-600 hover:border-rp-dark-pink-600'
    ];

@endphp

@if (!empty($href))
    <a href="{{ $href }}"  {{ $attributes->merge(['class' => "block" . " ". $baseClass . " " . $sizes[$size] . " " . $colors[$color]])}}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $baseClass . " " . $sizes[$size] . " " . $colors[$color]]) }}>
        {{ $slot }} 
    </button>
@endif
