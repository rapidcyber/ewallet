<div>
    <div class="max-w-6xl mx-auto">
        <livewire:components.layout.guest.guest-navigation />
        <x-guest.features-tab />
    </div>
    <div class="w-screen h-[40rem] bg-cover bg-no-repeat features-explore-background">
        <div class="h-full flex flex-col items-center justify-center">
            <h1 class="uppercase text-5xl font-bold text-rp-neutral-700">explore</h1>
            <p class="text-center w-[50%] tracking-wide text-lg">Explore the Best Philippine Real Estate Options! Working in tandem with RealHolmes.ph, RePay affords users access to some of the finest listings in the local real estate market.</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row gap-3 px-3 md:px-9">
        <div class="flex-1 text-white bg-primary-600 rounded-xl p-7 sm:px-11 sm:py-12">
            <div class="space-y-3 ">
                <h1 class="font-bold text-xl">Reserve and Arrange Rental Payments with Ease</h1>
                <p class="tracking-wide">Buy and Rent each property using your RePay funds or many of the other easy payment options available</p>
            </div>
            <div class="mt-3">
                <img src="{{url('/images/guest/property.svg')}}" alt="Property">
            </div>
        </div>
        <div class="flex-1 text-white bg-primary-600 rounded-xl p-7 sm:px-11 sm:py-12">
            <div class="space-y-3">
                <h1 class="font-bold text-xl">Shop for Necessities and Other Items</h1>
                <p class="tracking-wide">RePay offers a diverse selection of products in an easy-to-navigate marketplace.</p>
            </div>
            <div class="mt-3">
                <img src="{{url('/images/guest/shop-items.svg')}}" alt="Items Display">
            </div>
        </div>
    </div>

    <div class="pt-9">
        <x-guest.download-app-section />
    </div>
    
</div>

