@props([
    'color' => '',
])

@php

    $baseClass = "rounded-md text-center p-1.5 text-sm";
    $sizes = [
    ];
    $colors = [
        'purple' => 'border border-rp-purple-600 bg-rp-purple-200 text-rp-purple-600',
        'primary' => 'border border-primary-600 bg-primary-200 text-primary-600',
        'neutral' => 'border border-rp-neutral-600 bg-rp-neutral-200 text-rp-neutral-600',
        'red' => 'border border-rp-red-600 bg-rp-red-200 text-rp-red-600',
        'yellow' => 'border border-rp-yellow-600 bg-rp-yellow-200 text-rp-yellow-600',
        'blue' => 'border border-[#1C7EBF] bg-[#ADDCFF] text-[#1C7EBF]',
        'green' => 'border border-rp-green-600 bg-rp-green-200 text-rp-green-600',
    ];

    $colorClass = '';
    if(!empty($color)) {
        $colorClass = $colors[$color];
    }

@endphp
<div {{ $attributes->merge(['class' => $baseClass . " " . $colorClass])}}> 
    {{ $slot }}
</div>
