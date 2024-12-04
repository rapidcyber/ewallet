@props(['image_path' => null, 'info_block_top' => null])
<div class="text-center mb-5 w-full">
    @if (!empty($image_path))
        <div class="w-[140px] h-[140px] 2xl:w-[179px] 2xl:h-[179px] rounded-full mb-5 mx-auto">
            <img src="{{ $image_path }}" class="w-full h-full rounded-full object-cover" alt="Profile Image">
        </div>
    @endif
    @if (!empty($info_block_top))
        <p class="w-f">{{ $info_block_top }}</p>
    @endif
    <h2 class="text-[23.04px] font-bold text-rp-neutral-700">{{ $info_block_middle }}</h2>
    <p class="">{{ $info_block_bottom }}</p>
</div>