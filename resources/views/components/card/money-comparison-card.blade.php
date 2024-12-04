@props([
    'title',
    'present' => 0,
    'past' => 0,
    'color' => 'red',
    'date',
    'is_full' => true,
])

@php
    $colors = [
        'primary' => 'bg-purple-gradient-to-right text-white shadow-lg mb-8',
        'red' => 'bg-pink-gradient-to-right text-white shadow-lg mb-8',
        'white' => 'bg-white',
    ];

@endphp

<div
    {{ $attributes->merge(['class' => 'flex justify-center flex-1 flex-col px-6 py-7 bg-white text-lg rounded-xl ' . $colors[$color]]) }}>
    <p class="text-[19.2px]">{{ $title }}</p>
    <div class="flex items-center justify-between {{ $is_full ? 'w-72' : '' }}">
        <span class="font-bold {{ $is_full ? 'text-3.5xl' : 'text-2xl 2xl:text-3.5xl' }}">{{ \Number::currency($present, 'PHP') }}</span>
        <span class="flex items-center text-sm bg-white {{ $color == 'white' ? '' : 'rounded-3xl pl-2 pr-3' }}">
            @if ($past > $present and $past !== 0)
                <div><x-icon.solid-arrow-down fill="#dc2626" /></div>
                <p class="text-red-600">
                    {{ round(abs((($present - $past) / $past) * 100), 2) }}%</p>
            @elseif($present > $past)
                <div><x-icon.solid-arrow-up fill="#149D8C" /></div>
                @if ($past === 0)
                    <p class="text-rp-green-600">100%</p>
                @else
                    <p class="text-rp-green-600">
                        {{ round(abs((($present - $past) / $past) * 100), 2) }}%</p>
                @endif
            @elseif($present === $past and $present !== 0)
                {{-- 0% --}}
                <div><x-icon.solid-arrow-up fill="#149D8C" /></div>
                <p class="text-rp-green-600">0%</p>
            @elseif($present !== $past and $past === 0)
                {{-- -100% --}}
                <div><x-icon.solid-arrow-down fill="#dc2626" /></div>
                <p class="text-red-600">-100%</p>
            @endif
        </span>
    </div>
    <div class="flex flex-row justify-between text-sm gap-2 {{ $is_full ? 'w-72' : '' }}">
        <span>vs. previous
            @switch($date)
                @case('past_year')
                    year
                @break

                @case('past_30_days')
                    30 days
                @break

                @case('past_week')
                    week
                @break

                @case('day')
                    day
                @break

                @default
            @endswitch
        </span>
        <span>{{ \Number::currency($past, 'PHP') }}</span>
    </div>
</div>
