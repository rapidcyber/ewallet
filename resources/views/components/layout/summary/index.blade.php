<div class="min-w-[35%] max-w-[35%] w-[35%] h-full px-5 py-[30px] overflow-auto break-words">
    <h1 class="text-[23.04px] font-bold text-rp-neutral-700 mb-10">Summary</h1>

    @if (!empty($profile))
        <div>
            {{ $profile }}
        </div>
    @endif

    <div class="p-9 bg-white flex flex-col gap-9 rounded-2xl mb-10 w-full">
        {{ $body }}
    </div>

    <div>
        {{ $action }}
    </div>
</div>