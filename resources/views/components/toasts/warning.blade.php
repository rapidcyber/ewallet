{{-- Disappears after 5 seconds --}}
<div x-ref="toastWarning"
    class="fixed bottom-3 left-[50%] translate-x-[-50%] z-[9999] shadow-lg w-[90vw] max-w-[796px] gap-6 p-4 rounded-xl bg-white">
    <div class="flex items-center justify-between w-full">
        <div class="flex items-center gap-2">
            <svg width="44" height="45" viewBox="0 0 44 45" fill="none" >
                <rect y="0.5" width="44" height="44" rx="12" fill="#F79009" />
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M12 22.5C12 16.977 16.477 12.5 22 12.5C27.523 12.5 32 16.977 32 22.5C32 28.023 27.523 32.5 22 32.5C16.477 32.5 12 28.023 12 22.5ZM22 16.75C21.8011 16.75 21.6103 16.829 21.4697 16.9697C21.329 17.1103 21.25 17.3011 21.25 17.5L21.25 23.5C21.25 23.6989 21.329 23.8897 21.4697 24.0303C21.6103 24.171 21.8011 24.25 22 24.25C22.1989 24.25 22.3897 24.171 22.5303 24.0303C22.671 23.8897 22.75 23.6989 22.75 23.5L22.75 17.5C22.75 17.086 22.414 16.75 22 16.75ZM22 27.5C21.7348 27.5 21.4804 27.3946 21.2929 27.2071C21.1054 27.0196 21 26.7652 21 26.5C21 26.2348 21.1054 25.9804 21.2929 25.7929C21.4804 25.6054 21.7348 25.5 22 25.5C22.2652 25.5 22.5196 25.6054 22.7071 25.7929C22.8946 25.9804 23 26.2348 23 26.5C23 26.7652 22.8946 27.0196 22.7071 27.2071C22.5196 27.3946 22.2652 27.5 22 27.5Z"
                    fill="white" />
            </svg>
            <div class="">
                {{-- Title --}}
                <h6 class="text-lg font-bold text-rp-neutral-700">{{ session('warning') }}</h6>
                {{-- Description --}}
                <p class="text-sm">{{ session('warning_message') }}</p>
            </div>
        </div>
        <div @click="$refs.toastWarning?.remove()" class="cursor-pointer">
            <svg  width="14" height="15" viewBox="0 0 14 15" fill="none">
                <path d="M1.18848 13.4056L12.9998 1.59424" stroke="#A8B5BE" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
                <path d="M12.9998 13.4056L1.18848 1.59424" stroke="#A8B5BE" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </div>
    </div>
</div>
