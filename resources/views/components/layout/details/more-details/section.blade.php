@props([
    'title',
    'title_text_color' => 'red',
])

@php
    $colors = [
        'red' => 'text-rp-red-500',
        'primary' => 'text-primary-600'        
    ];

@endphp
<div {{ $attributes->merge(['class' => "[&:not(:last-child)]:border-b-2 [&:not(:first-child)]:pt-5 w-full pb-5"])}}>
    <h2 class="font-bold mb-4 {{-- 2xl:text-xl --}} text-xl {{ $colors[$title_text_color] }}">{{ $title }}</h2>
    <div class="w-full">
        {{ $slot }}
    </div>
</div>