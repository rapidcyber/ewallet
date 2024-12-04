@props([
    'label',
    'data',
    'isActive',
    'color' => 'red', 
])

@php

    $notactive = "text-rp-neutral-600 bg-white border-transparent";

    $activeColor = [
        'primary' => 'text-primary-500 bg-primary-100 border-primary-500', 
        'red' => 'text-rp-red-500 bg-rp-red-100 border-rp-red-500'
    ];

    $active = $activeColor[$color];

    if($color === 'primary') {
        $notactive.= ' hover:bg-primary-100';
    }

    if($color === 'red') {
        $notactive.= ' hover:bg-rp-red-100';
    }
    
@endphp

<button {{ $attributes->merge(['class' => 'cursor-pointer flex flex-col justify-center px-4 py-4 rounded-xl border-2 transition duration-300 '. ($isActive ? $active : $notactive)])}}>
<span class="text-base break-words text-left">{{ $label }}</span>
<span class="text-3.5xl font-bold break-words">{{ $data }}</span>
</button>