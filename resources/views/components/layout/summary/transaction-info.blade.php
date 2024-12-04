{{-- @props(['image' => null]) --}}
<div class="flex flex-col items-center mb-5">
    @if (!empty($image))
        <div class="w-[140px] h-[140px] 2xl:w-[179px] 2xl:h-[179px] overflow-hidden mb-5">
            <img src="{{ $image->attributes->get('src') }}" class="w-full h-full object-cover rounded-full" alt="{{ $image->attributes->get('alt') }}"/>
        </div>
    @endif
    
    <p>Transaction to:</p>

    @if (!empty($info_block))
        {{ $info_block }}
    @endif
    {{-- <h2 class="text-[23.04px] font-bold text-rp-neutral-700">{{ $info_block_middle }}</h2>
    <p>{{{ $info_block_bottom }}}</p> --}}
</div>