@props(['width' => '24', 'height' => '24', 'fillType' => 'none', 'key'])

@php
    $left_side_fill = "#D9D9D9";
    $right_side_fill = "#D9D9D9";

    if ($fillType === 'full') {
        $left_side_fill = "#FAA90C";
        $right_side_fill = "#FAA90C";
    
    } else if ($fillType === 'half') {
        $left_side_fill = "#FAA90C";
        $right_side_fill = "#D9D9D9";

    } else if ($fillType === 'none') {
        $left_side_fill = "#D9D9D9";
        $right_side_fill = "#D9D9D9";
    }
@endphp
<svg {{ $attributes }}  viewBox="0 0 24 24" width="{{ $width }}" height="{{ $height }}">
    <defs>
      <linearGradient id="half-full-{{$key}}" x1="0%" y1="0%" x2="100%" y2="0%">
        <stop offset="50%" style="stop-color: {{ $left_side_fill }}; stop-opacity: 1" />
        <stop offset="50%" style="stop-color: {{ $right_side_fill }}; stop-opacity: 1" />
      </linearGradient>
    </defs>
    <path fill="url(#half-full-{{$key}})" d="M12 .587l3.668 7.429 8.2 1.191-5.93 5.773 1.4 8.17L12 18.896l-7.338 3.854 1.4-8.17L.13 9.207l8.2-1.191L12 .587z"/>
</svg>