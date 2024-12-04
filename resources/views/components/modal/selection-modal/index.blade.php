@props([
    'title',
])

{{-- TODO for front-end: create props for width and max width --}}
<div class="absolute flex flex-col gap-8 bg-white p-10 rounded-2xl w-[667px] max-w-[90%] max-h-[95%] overflow-y-auto">
    {{-- CLOSE BUTTON --}} {{-- visible is accessible from the modal component --}}
    <button class="absolute top-6 right-6" @click="visible=false"> 
        <x-icon.close />
    </button>
    @if (!empty($title))
        <h3 class="text-2xl font-bold mb-2 text-center">{{ $title }}</h3>
    @endif
    <div class="grid grid-cols-3 gap-3">
        @if (!$items->isEmpty())
            {{ $items }}
        @endif
    </div>
</div>