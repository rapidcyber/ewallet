{{-- Disappears after 5 seconds --}}
<div x-ref="toastError"
    class="fixed bottom-3 left-[50%] translate-x-[-50%] z-[9999] shadow-lg w-[90vw] max-w-[796px] gap-6 p-4 rounded-xl bg-white">
    <div class="flex items-center justify-between w-full">
        <div class="flex items-center gap-2">
            <svg width="44" height="45" viewBox="0 0 44 45" fill="none" >
                <rect y="0.5" width="44" height="44" rx="12" fill="#F70068" />
                <path
                    d="M26.19 12.5H17.81C14.17 12.5 12 14.67 12 18.31V26.68C12 30.33 14.17 32.5 17.81 32.5H26.18C29.82 32.5 31.99 30.33 31.99 26.69V18.31C32 14.67 29.83 12.5 26.19 12.5ZM25.36 24.8C25.65 25.09 25.65 25.57 25.36 25.86C25.21 26.01 25.02 26.08 24.83 26.08C24.64 26.08 24.45 26.01 24.3 25.86L22 23.56L19.7 25.86C19.55 26.01 19.36 26.08 19.17 26.08C18.98 26.08 18.79 26.01 18.64 25.86C18.35 25.57 18.35 25.09 18.64 24.8L20.94 22.5L18.64 20.2C18.35 19.91 18.35 19.43 18.64 19.14C18.93 18.85 19.41 18.85 19.7 19.14L22 21.44L24.3 19.14C24.59 18.85 25.07 18.85 25.36 19.14C25.65 19.43 25.65 19.91 25.36 20.2L23.06 22.5L25.36 24.8Z"
                    fill="white" />
            </svg>
            <div class="">
                {{-- Title --}}
                <h6 class="text-lg font-bold text-rp-neutral-700">{{ session('error') }}</h6>
                {{-- Description --}}
                <p class="text-sm">{{ session('error_message') }}</p>
            </div>
        </div>
        <div @click="$refs.toastError?.remove()" class="cursor-pointer">
            <svg  width="14" height="15" viewBox="0 0 14 15" fill="none">
                <path d="M1.18848 13.4056L12.9998 1.59424" stroke="#A8B5BE" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                <path d="M12.9998 13.4056L1.18848 1.59424" stroke="#A8B5BE" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </div>
    </div>
</div>
