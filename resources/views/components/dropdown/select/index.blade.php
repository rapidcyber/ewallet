<div class="flex focus-within:ring-2 items-center bg-white gap-[8px] border rounded-lg pl-[8px] border-rp-neutral-500 overflow-hidden {{ $attributes->get('class','') }}">
    <select {{ $attributes->except('class') }} class="rounded-lg border-none px-0 py-2 flex-1 w-full outline-none focus:ring-0 cursor-pointer text-base h-full truncate">
        {{ $slot }}
    </select>
</div>
