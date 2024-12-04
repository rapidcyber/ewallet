@props([
    'icon' => '',
    'disabled' => false,
    'checked' => false,
])


@php
    $position = '';
    if ($icon !== '') {
        $position = $icon->attributes->get('icon_position','left');
    }
@endphp

@switch($attributes->get('type'))
    @case('checkbox')
    @case('radio')
        <input {{ $attributes->merge(['class' => '{{--- focus-within:ring-1 --}} rounded-sm scale-125 cursor-pointer border border-rp-neutral-600 accent-rp-neutral-600']) }} {{$disabled ? 'disabled' : null }} {{ $checked ? 'checked' : null }}>
        @break  
    @case('text')
    @case('number')
    @case('date')
    @case('email')
    @case('password')
    @case('search')
    @case('tel')
        <div class="{{$attributes->get('class')}} focus-within:ring-1 flex flex-row border-rp-neutral-500 border-[1px] rounded-lg overflow-hidden bg-white items-center px-2 py-2 gap-2 text-rp-neutral-700">
            {{-- icon if any --}}
            @if ($icon !== '' && $position === 'left')
                <div>
                    {{ $icon }}
                </div>
            @endif
            
            <input 
            {{ $attributes->except('class') }}
            class="w-full appearance-none bg-transparent border-none font-thin outline-none focus:ring-0 p-0 placeholder:text-neutral-400 text-base" {{ $disabled ? 'disabled' : null }}>
                
            {{-- icon if any --}}
            @if ($icon !== '' && $position === 'right')
                <div>
                    {{ $icon }}
                </div>
            @endif
        </div>
        @break
    @default
        Type provided is currently not supported
@endswitch
