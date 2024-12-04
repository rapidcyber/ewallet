<div {{ $attributes->merge(['class' => "flex flex-col"]) }}>
    <label {{ $label->attributes->merge(['class' => 'block text-xs 2xl:text-sm text-rp-neutral-500']) }}>{{ $label }}</label>
    {{-- input --}}
    {{ $slot }}
</div>