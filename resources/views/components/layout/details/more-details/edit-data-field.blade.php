@props([
    'field',
    'value'
])
<div class="flex gap-2 break-words w-full {{ $attributes->get('class','')}}">
    <p class="{{-- text-xs 2xl:text-base --}} text-base w-1/3">{{ $field }}</p>
    <div class="w-2/3">
        <div class="flex items-center gap-3">
            <p class="{{-- text-xs 2xl:text-base --}} text-base font-bold max-w-[calc(100%-40px)]">{{ $value }}</p>
            <div class="cursor-pointer" {{ $attributes->except('class')}}>
                <x-icon.edit />
            </div>
        </div>
    </div>
</div>