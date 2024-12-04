@props([
    'title',
    'message'
])

<div {{ $attributes->merge(['class' => "max-w-96 w-[90%] px-[24px] py-[26px] space-y-4 bg-white rounded-3xl "]) }}>
    @if (!empty($title))
        <h3 class="text-3.5xl font-bold text-rp-neutral-700 text-center">{{ $title }}</h3>
    @endif

    @if (!empty($message))
        <p class="text-center">{{ $message }} </p>
    @endif

    <div class="w-full flex flex-row gap-2">
        @if ($action_buttons->isNotEmpty())
            {{ $action_buttons }}
        @endif
    </div>
</div>