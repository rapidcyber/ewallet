@props([
    'size' => 'md'
])

@php
    $sizes = [
        'sm' => 'text-[8px] px-1 py-2 rounded-md',
        'md' => 'text-[13.33px] p-[10px] rounded-[9px]',
        'lg' => 'text-base px-[10px] py-[10px] rounded-xl ',
    ];
@endphp
<button {{ $attributes->merge(['class' => "bg-purple-gradient-to-right text-white text-center uppercase font-bold duration-300 transition focus:ring-4 focus:outline-none disabled:bg-rp-neutral-200 ". $sizes[$size] ]) }}>
    {{ $slot }}
    
</button>