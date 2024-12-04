<div {{ $attributes->merge(['class' => 'flex flex-row justify-between mb-8'])}}>
    <x-main.title>
        @if ($title->isNotEmpty())
            {{ $title }}
        @endif
    </x-main.title>
    
    <div class="flex flex-row gap-2">
        @if (isset($actions) && $actions->isNotEmpty())
            {{ $actions }}
        @endif
    </div>
</div>