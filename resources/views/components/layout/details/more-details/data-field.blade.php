@props([
    'field',
    'value',
])

<div class="flex gap-2 break-words w-full">
    <p class="{{-- text-xs 2xl:text-base --}} text-base w-1/3">{{ $field }}</p>
    <p class="{{-- text-xs 2xl:text-base --}} text-base font-bold w-2/3">{!! $value !!}</p>
</div>