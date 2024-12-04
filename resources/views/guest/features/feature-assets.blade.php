<div>
    <div class="max-w-6xl mx-auto">
        <livewire:components.layout.guest.guest-navigation />
        <x-guest.features-tab />
    </div>
    <div class="w-screen h-[40rem] bg-cover bg-no-repeat assets-hero">
        <div class="h-full flex flex-col items-center justify-center">
            <h1 class="uppercase text-5xl font-bold text-rp-neutral-700">assets</h1>
            <p class="text-center w-[50%] tracking-wide text-lg">Post Rental Real Estate Options with Ease! Looking to post your rental properties? RePay provides you with a stellar marketplace section that makes uploading properties easy</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto space-y-4 px-3 md:py-9">
        <div class="w-full flex flex-col sm:flex-row gap-3 bg-primary-600 rounded-xl px-7 py-4">
            <div class="text-white flex flex-col justify-center">
                <h1 class="text-2xl font-bold mb-2">Sell Things that People Need and Earn Income</h1>
                <p class="tracking-wide">Get extra funds from selling your items on RePay’s built-in marketplace.</p>
            </div>
            <div>
                <img src="{{url('/images/guest/repay-shopping-items-interface.svg')}}" alt="Repay Mobile Shopping Interface">
            </div>
        </div>

        <div class="w-full flex flex-col-reverse sm:flex-row gap-3 bg-primary-600 rounded-xl px-7 py-4 overflow-hidden">
            <div class="sm:-mb-36">
                <img src="{{url('/images/guest/repay-merchant-store.svg')}}" alt="Repay Mobile Merchant Store Interface">
            </div>
            <div class="text-white flex flex-col justify-center items-start sm:items-end">
                <h1 class="text-2xl font-bold mb-2">Safely Interact with Buyers</h1>
                <p class="tracking-wide text-left sm:text-right">Freely interact with trustworthy buyers and create a list of contacts to help you on your selling journey.</p>
            </div>
        </div>
    </div>
    <x-guest.download-app-section />
</div>
