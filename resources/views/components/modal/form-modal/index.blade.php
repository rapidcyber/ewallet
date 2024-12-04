@props([
    'title',
])

<div {{ $attributes->merge(['class' => "absolute flex flex-col gap-8 bg-white p-10 rounded-2xl w-[600px] max-w-[90%] max-h-[95%] overflow-y-auto"])}}>
    {{-- CLOSE BUTTON --}} {{-- visible is accessible from the modal component --}}
    <button class="absolute top-6 right-6" @click="$dispatch('closeModal');visible=false"> 
        <x-icon.close />
    </button>
    @if (!empty($title))
        <h3 class="text-2xl font-bold mb-2 text-left">{{ $title }}</h3>
    @endif
    <div class="">
        {{ $slot }}
    </div>
    <div class="w-full flex flex-row gap-2">
        @if (isset($action_buttons) && $action_buttons->isNotEmpty()) 
            {{ $action_buttons }}
        @endif
    </div>
</div>