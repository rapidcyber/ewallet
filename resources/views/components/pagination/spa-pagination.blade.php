@props([
    // 'hasPages' => false,
    'currentPageNumber' => 1,
    'totalPages' => 0,
    'numberOfPageToShowEllipsis' => 11,
    'threshold' => 5,
    'maxPageBeforeEllipsis' => 7,
    'onPageClick',
])  

<div class="flex flex-row justify-center items-center h-10 gap-0 mt-8 {{-- flex flex-row items-center h-10 gap-0 mt-12 w-max mx-auto rounded-md overflow-hidden --}}">
    <div class="{{1 === $currentPageNumber ? 'cursor-default opacity-50' : 'cursor-pointer hover:bg-rp-neutral-100'}} py-[1px] px-[6px] h-full border border-rp-neutral-500 border-r-0 rounded-l bg-white flex items-center" wire:click="handlePageArrow('left')">
        <x-icon.chevron-left />
    </div>
    @if($totalPages < $numberOfPageToShowEllipsis)
        @for ($i = 1; $i <= $totalPages; $i++)
            <div class="{{$i === $currentPageNumber ? 'bg-rp-neutral-200' : 'bg-white hover:bg-rp-neutral-100'}} flex justify-center items-center h-[40px] w-[40px] border border-rp-neutral-500 cursor-pointer border-r-0" wire:click="handlePageNumberClick({{$i}})">{{$i}}</div>
        @endfor
    @elseif ($totalPages >= $numberOfPageToShowEllipsis)
        @if ($currentPageNumber > $threshold && $currentPageNumber < $totalPages - $threshold + 1)
            <div wire:key="{{1}}" class="{{1 === $currentPageNumber ? 'bg-rp-neutral-200' : 'bg-white hover:bg-rp-neutral-100'}} flex justify-center items-center h-[40px] w-[40px] border border-rp-neutral-500 cursor-pointer border-r-0" wire:click="handlePageNumberClick(1)">
                1
            </div> 
            <div class="flex justify-center items-center h-[40px] w-[40px] border border-rp-neutral-500 border-r-0">
                ...
            </div>
            @for($i = $currentPageNumber - 2; $i <= $currentPageNumber + 2; $i++)
                <div wire:key="{{$i}}" class="{{$i === $currentPageNumber ? 'bg-rp-neutral-200' : 'bg-white hover:bg-rp-neutral-100'}} flex justify-center items-center h-[40px] w-[40px] border border-rp-neutral-500 cursor-pointer border-r-0" wire:click="handlePageNumberClick({{$i}})">
                    {{$i}}
                </div> 
            @endfor
            <div class="flex justify-center items-center h-[40px] w-[40px] border border-rp-neutral-500 border-r-0">
                ...
            </div>
            <div wire:key="{{$totalPages}}" class="{{$totalPages === $currentPageNumber ? 'bg-rp-neutral-200' : 'bg-white hover:bg-rp-neutral-100'}} flex justify-center items-center h-[40px] w-[40px] border border-rp-neutral-500 border-r-0" wire:click="handlePageNumberClick({{$totalPages}})">
                {{$totalPages}}
            </div>
        @elseif ($currentPageNumber <= $threshold)
            @for ($i = 1; $i <= $maxPageBeforeEllipsis; $i++)
                <div wire:key="{{$i}}" class="{{$i === $currentPageNumber ? 'bg-rp-neutral-200' : 'bg-white hover:bg-rp-neutral-100'}} flex justify-center items-center h-[40px] w-[40px] border   border-rp-neutral-500 cursor-pointer border-r-0" wire:click="handlePageNumberClick({{$i}})">{{$i}}</div>
            @endfor
            <div class="flex justify-center items-center h-[40px] w-[40px] border border-rp-neutral-500 border-r-0">
                ...
            </div>
            <div wire:key="{{$totalPages}}" class="{{$totalPages === $currentPageNumber ? 'bg-rp-neutral-200' : 'bg-white hover:bg-rp-neutral-100'}} flex justify-center items-center h-[40px] w-[40px] border border-rp-neutral-500 cursor-pointer border-r-0" wire:click="handlePageNumberClick({{$totalPages}})">
                {{$totalPages}}
            </div>
        @elseif ($currentPageNumber > $threshold) 
            <div wire:key="{{1}}" class="{{1 === $currentPageNumber ? 'bg-rp-neutral-200' : 'bg-white hover:bg-rp-neutral-100'}} flex justify-center items-center h-[40px] w-[40px] border border-rp-neutral-500 cursor-pointer border-r-0" wire:click="handlePageNumberClick(1)">
                1
            </div> 
            <div class="flex justify-center items-center h-[40px] w-[40px] border border-rp-neutral-500 border-r-0">
                ...
            </div>
            @for ($i = $totalPages - $maxPageBeforeEllipsis + 1; $i <= $totalPages; $i++)
                <div wire:key="{{$i}}" class="{{$i === $currentPageNumber ? 'bg-rp-neutral-200' : 'bg-white hover:bg-slate-100'}} flex justify-center items-center h-[40px] w-[40px] border border-rp-neutral-500 bg-white cursor-pointer" wire:click="handlePageNumberClick({{$i}})">{{$i}}</div>
            @endfor
        @endif                      
    @endif

    <div class="{{$totalPages === $currentPageNumber ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-rp-neutral-100'}} py-[1px] px-[6px] h-full border border-rp-neutral-500 rounded-r bg-white flex items-center" wire:click="handlePageArrow('right')">
        <x-icon.chevron-right />
    </div>
</div>