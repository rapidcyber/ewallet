@props([
    'title',
    'color' => 'red',
])

@php 
    $colors = [
        'primary' => 'text-primary-600',
        'red' => 'text-rp-red-500' 
    ];

@endphp
<div>
    <h3 class="text-[19.2px] font-bold mb-3 w-full {{ $colors[$color] }}">{{ $title }}</h3>
    <div>
        {{ $data }}
    </div>
</div>