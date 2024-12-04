<div {{ $attributes->merge(['class' => "bg-white flex items-center gap-[8px] border rounded-lg pl-[8px] border-rp-neutral-500 focus-within:ring-1 "])}}>
	<x-icon.calendar />
	<select {{ $attributes->except('class') }} class="rounded-lg border-none px-0 w-full outline-none focus:ring-0 cursor-pointer text-base h-full truncate p-1">
		{{ $slot }}
	</select>
</div>