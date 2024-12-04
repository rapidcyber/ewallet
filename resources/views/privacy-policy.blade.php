<div class="py-5 bg-rp-neutral-100">
    <div class="min-h-screen flex flex-col items-center py-6 sm:pt-0">
        <a href="{{ route('home') }}" class="mb-4">
            <img width="200" src="{{ url('/images/repay-logo-colored.svg') }}" alt="Repay Logo">
        </a>

        <div class="w-full sm:max-w-2xl mt-6 p-6 bg-white shadow-md overflow-hidden sm:rounded-lg prose">
            {!! $policy !!}
        </div>
    </div>
</div>
