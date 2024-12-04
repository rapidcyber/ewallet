@props([
    'cols' => 1
])


@php
    $grid_columns = "grid-cols-". $cols;
@endphp

<div {{ $attributes->merge(['class' => 'relative grid gap-[15px] ' . $grid_columns])}}>
    {{ $slot }}
</div>