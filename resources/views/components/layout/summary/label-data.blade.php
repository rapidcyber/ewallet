@props([
    'label' => '',
    'data' => ''
])
<div class="flex flex-row justify-between gap-2 py-2 [&:not(:last-child)]:border-b [&:not(:last-child)]:border-rp-neutral-300 w-full">
    <p {{ $label->attributes->merge(['class' => "w-2/5 break-words"]) }}>{{ $label }}</p>
    <p {{ $data->attributes->merge(['class' => "text-rp-neutral-700 font-bold w-3/5 break-words text-right" ]) }}>{{ $data }}</p>
</div>