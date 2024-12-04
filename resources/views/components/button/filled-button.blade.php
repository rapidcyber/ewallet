@props([
    'size' => 'md',
    'color' => 'red',
    'href' => null,
    'disabled' => false,
])

@php
    $baseClass = "text-center border-2 uppercase font-bold duration-300 transition focus:ring-4 focus:outline-none disabled:bg-rp-neutral-200 disabled:border-rp-neutral-200";
    
    $sizes = [
        'sm' => 'text-xs px-1 py-2 rounded-md',
        'md' => 'text-sm p-[10px] rounded-[9px]',
        'lg' => 'text-base px-[10px] py-[10px] rounded-xl ',
    ];

    $colors = [
        'primary' => 'bg-primary-600 border-primary-600 text-white hover:bg-primary-700 hover:border-primary-700',
        'red' => 'bg-rp-red-500 border-rp-red-500 text-white hover:bg-rp-red-600 hover:border-rp-red-600',
        'purple' => 'text-white bg-rp-purple-600 border-rp-purple-600 hover:bg-rp-purple-700 hover:border-rp-purple-700 px-16 py-1 rounded-md', // New style added
    ];
@endphp

@if (!empty($href))
    <a href="{{ $href }}"  {{ $attributes->merge(['class' => "block" . " ". $baseClass . " " . $sizes[$size] . " " . $colors[$color]])}}>
        {{ $slot }}
    </a>
@else
    <button {{ $disabled ? 'disabled' : null }} {{ $attributes->merge(['class' => $baseClass . " " . $sizes[$size] . " " . $colors[$color]])}}>
        {{ $slot }}
    </button>
@endif
