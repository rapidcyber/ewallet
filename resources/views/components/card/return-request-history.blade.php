@props(['logs'])

<div {{ $attributes->merge(['class' => "flex flex-col gap-8 p-7 bg-white rounded-lg text-rp-neutral-700 h-[400px] max-h-[400px]" ])}}>
    <div>
        <h2 class="text-[19px] font-bold sticky top-0 bg-white">Return Request History</h2>
    </div>

    {{-- DETAILS --}}
    <div class="flex flex-col gap-8 w-full overflow-y-auto">
        @if ($logs->isNotEmpty())
            @foreach ($logs as $key => $log)
                @if ($loop->first)
                    <div class="tracker-dets flex gap-[11px]" wire:key='return-order-log-{{ $key }}'>
                        <span class="text-[11px] max-w-[120px] min-w-[120px] break-words">{{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}</span>
                        <div class="relative pl-10 grow">
                            <svg class="absolute top-0 left-0"  width="23" height="23" viewBox="0 0 23 23" fill="none">
                                <circle cx="11.4902" cy="11.3828" r="10.75" fill="#FF3D8F"/>
                            </svg>
                            <h5 class="font-bold">{{ $log->title }}</h5>
                            <p class="text-[11px]">{{ $log->description ?? '' }}</p>
                        </div>
                    </div>
                @else
                    <div class="tracker-dets flex gap-[11px] text-rp-neutral-300" wire:key='return-order-log-{{ $key }}'>
                        <span class="text-[11px] max-w-[120px] min-w-[120px] break-words">{{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}</span>
                        <div class="relative pl-10 grow">
                            <svg class="absolute top-0 left-0"  width="23" height="23" viewBox="0 0 23 23" fill="none">
                                <circle cx="11.4902" cy="11.3828" r="10.75" fill="#bbc5cd"/>
                            </svg>
                            <h5 class="font-bold">{{ $log->title }}</h5>
                            <p class="text-[11px]">{{ $log->description ?? '' }}</p>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</div>