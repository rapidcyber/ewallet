{{-- Disappears after 5 seconds --}}
<div x-ref="toastSuccess"
    class="fixed bottom-3 left-[50%] translate-x-[-50%] z-[9999] shadow-lg w-[90vw] max-w-[796px] gap-6 p-4 rounded-xl bg-white">
    <div class="flex items-center justify-between w-full">
        <div class="flex items-center gap-2">
            <svg width="44" height="45" viewBox="0 0 44 45" fill="none" >
                <rect y="0.5" width="44" height="44" rx="12" fill="#149D8C" />
                <path
                    d="M22 12.5C16.49 12.5 12 16.99 12 22.5C12 28.01 16.49 32.5 22 32.5C27.51 32.5 32 28.01 32 22.5C32 16.99 27.51 12.5 22 12.5ZM26.78 20.2L21.11 25.87C20.97 26.01 20.78 26.09 20.58 26.09C20.38 26.09 20.19 26.01 20.05 25.87L17.22 23.04C16.93 22.75 16.93 22.27 17.22 21.98C17.51 21.69 17.99 21.69 18.28 21.98L20.58 24.28L25.72 19.14C26.01 18.85 26.49 18.85 26.78 19.14C27.07 19.43 27.07 19.9 26.78 20.2Z"
                    fill="white" />
            </svg>

            <div class="">
                {{-- Title --}}
                <h6 class="text-lg font-bold text-rp-neutral-700">{{ session('success') }}</h6>
                {{-- Description --}}
                <p class="text-sm">{{ session('success_message') }}</p>
            </div>
        </div>
        <div @click="$refs.toastSuccess?.remove()" class="cursor-pointer">
            <svg  width="14" height="15" viewBox="0 0 14 15" fill="none">
                <path d="M1.18848 13.4056L12.9998 1.59424" stroke="#A8B5BE" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                <path d="M12.9998 13.4056L1.18848 1.59424" stroke="#A8B5BE" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </div>
    </div>
</div>
