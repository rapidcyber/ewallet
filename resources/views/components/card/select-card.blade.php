@props([
    'title',
    'description',  
])

<div
    {{ $attributes->merge(['class' => 'hover:bg-rp-neutral-100 cursor-pointer border border-rp-neutral-500 bg-white rounded-lg px-4 py-3 flex flex-row justify-between items-center gap-2 flex-1'])}}
    x-data="{ isSelected: false }" x-modelable="isSelected">
    <div>
        <h2 class="font-bold text-rp-neutral-700">{{ $title }}</h2>
        <p class="text-[13.33px]">{{ $description }} </p>
    </div>
    <div x-cloak x-show="isSelected">
        <x-icon.check />
    </div>
</div>