@props([
    // 'placeholder' => 'Search',
    'icon_position' => 'right',
    // 'type' => 'text',
    'name' => 'search',
    'id' => '',    
])


<div class="focus-within:ring-1 relative flex items-center border rounded-lg overflow-hidden bg-white border-rp-neutral-500 {{ $attributes->get('class','') }} {{ $icon_position === 'left' ? 'pl-[13px]' : 'pr-[13px]'}}">

    @if ($icon_position === 'left')
        <div>
            <svg  width="26" height="26" viewBox="0 0 24 24" fill="none" class="">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M18.8875 17.4734C20.2086 15.8415 21 13.7632 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21C13.7631 21 15.8414 20.2087 17.4732 18.8876L20.2865 21.7009C20.677 22.0914 21.3102 22.0914 21.7007 21.7009C22.0912 21.3103 22.0912 20.6772 21.7007 20.2866L18.8875 17.4734ZM19 11.5C19 15.6421 15.6421 19 11.5 19C7.35786 19 4 15.6421 4 11.5C4 7.35786 7.35786 4 11.5 4C15.6421 4 19 7.35786 19 11.5Z" fill="#42505A" />
            </svg>
        </div>
    @endif
    
    <input {{ $attributes->except('class')->merge(['type' => 'search', 'placeholder' => "Search", 'name' => 'search']) }} class="text-sm outline-none focus:ring-0 border-none w-full px-2 py-2" >


    @if ($icon_position === 'right')
        <div>
            <svg  width="26" height="26" viewBox="0 0 24 24" fill="none" class="">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M18.8875 17.4734C20.2086 15.8415 21 13.7632 21 11.5C21 6.25329 16.7467 2 11.5 2C6.25329 2 2 6.25329 2 11.5C2 16.7467 6.25329 21 11.5 21C13.7631 21 15.8414 20.2087 17.4732 18.8876L20.2865 21.7009C20.677 22.0914 21.3102 22.0914 21.7007 21.7009C22.0912 21.3103 22.0912 20.6772 21.7007 20.2866L18.8875 17.4734ZM19 11.5C19 15.6421 15.6421 19 11.5 19C7.35786 19 4 15.6421 4 11.5C4 7.35786 7.35786 4 11.5 4C15.6421 4 19 7.35786 19 11.5Z" fill="#42505A" />
            </svg>
        </div>
    @endif
    
</div>