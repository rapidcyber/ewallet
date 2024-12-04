@props([
    'alignment' => 'left',
])

@php
    $position = '';
    if ($alignment === 'left') {
        $position = 'justify-start';
    }
    
    if($alignment === 'middle') {
        $position = 'justify-center';
    }

   
@endphp

<div {{ $attributes->merge(['class' => "flex flex-row " . $position ])}} wire:ignore>
    {{ $slot }}
</div>