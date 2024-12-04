<div>
    <x-guest.hero-section-wrapper>
        <livewire:components.layout.guest.guest-navigation whiteLogo="true" whiteText="true" />
        <div class="flex flex-col sm:flex-row max-w-6xl items-center h-full gap-12 p-9">
            <div class="flex flex-col justify-center gap-2 basis-1/2 leading-7 space-y-3">
                <h1 class="text-4xl font-bold text-white text-center sm:text-left">RePay.ph is a new digital mobile wallet </h1>
                <p class="text-white tracking-wide text-center sm:text-left">Created to give OFW’s, businesses and other individuals an easier online alternative to traditional payment methods. </p>
                <button onclick="document.getElementById('downloap-app-section').scrollIntoView();"
                    class="w-max mx-auto md:mx-0 block sm:hidden font-semibold text-white rounded-lg px-3 py-2 bg-red-600 hover:bg-red-700">Download
                    RePay</button>
            </div>
            <div class="basis-1/2">
               <div>
                    <img src="{{url('/images/guest/repay-digital-mobile-interfaces.png')}}" alt="Repay Digital Mobile Wallet Interfaces"/>
               </div>
            </div>
        </div>
    </x-guest.hero-section-wrapper>
    <div class="flex flex-col sm:flex-row max-w-6xl mx-auto gap-12 py-12 px-9">
        <div class="flex-1 flex flex-col justify-center">
            <h1 class="font-bold text-center sm:text-left text-4xl mb-3 text-rp-neutral-700">Mission</h1>
            <p class="text-center sm:text-left">RePay Digital Solutions intends to uplift Filipino families by providing safe and easy access tools for managing and improving their personal or business finances worldwide.</p>
        </div>
        <div class="flex-1">
            <div>
                <img src="{{ url('/images/guest/happy-family.svg') }}" alt="Happy Family"/>
            </div>
        </div>  
    </div>

    <div class="flex flex-col-reverse sm:flex-row max-w-6xl mx-auto gap-12 py-12 px-9">
       
        <div class="flex-1">
            <div>
                <img src="{{ url('/images/guest/mother-and-baby.svg') }}" alt="A mother is holding her baby in her arms while both look at a smartphone screen together."/>
            </div>
        </div>  

        <div class="flex-1 flex flex-col justify-center">
            <h1 class="font-bold text-4xl mb-3 text-center sm:text-right text-rp-neutral-700">Vision</h1>
            <p class="text-center sm:text-right">To create a great product that every Filipino will use daily, improving their lives and securing their future.</p>
        </div>
    </div>
    

    <div class="max-w-6xl mx-auto py-12 px-9 ">
        <h1 class="text-4xl font-bold text-center sm:text-left text-rp-neutral-700">What Can RePay Do?</h1>
        <p class="text-center sm:text-left">Through our light-weight and easy-to-install app, you can turn your mobile phone into a digital wallet where you can:</p>
        @vite(['resources/js/swiper-repay-about.js'])
        <div class="swiper mt-5 !overflow-visible">
            <div class="swiper-wrapper text-white">
                <div class="swiper-slide rounded-xl bg-primary-600 !w-full sm:!w-80 !h-64  px-9 py-5">
                    <div>
                        <img src="{{url('/images/guest/about-transfer.svg')}}" alt="Receive and Transfer" class="mx-auto"/>
                    </div>
                    <h1 class="text-2xl font-bold text-center">Receive and transfer money</h1>
                </div>
                <div class="swiper-slide rounded-xl bg-primary-600 !w-full sm:!w-80 !h-64 px-9 py-5">
                    <div>
                        <img src="{{url('/images/guest/about-schedule.svg')}}" alt="Schedule and Pay bills" class="mx-auto"/>
                    </div>
                    <h1 class="text-2xl font-bold text-center">Schedule and pay bills</h1>
                </div>
                <div class="swiper-slide rounded-xl bg-primary-600 !w-full sm:!w-80 !h-64 px-9 py-5">
                    <div>
                        <img src="{{url('/images/guest/about-accounting.svg')}}" alt="Invest" class="mx-auto"/>
                    </div>
                    <h1 class="text-2xl font-bold text-center">Invest in local businesses</h1>
                </div>
                <div class="swiper-slide rounded-xl bg-primary-600 !w-full sm:!w-80 !h-64 px-9 py-5">
                    <div>
                        <img src="{{url('/images/guest/about-realestate.svg')}}" alt="Property" class="mx-auto"/>
                    </div>
                    <h1 class="text-2xl font-bold text-center">Buy and rent homes</h1>
                </div>
                <div class="swiper-slide rounded-xl bg-primary-600 !w-full sm:!w-80 !h-64 px-9 py-5">
                    <div>
                        <img src="{{url('/images/guest/about-shop.svg')}}" alt="Online Store" class="mx-auto"/>
                    </div>
                    <h1 class="text-2xl font-bold text-center">Shop online</h1>
                </div>
                <div class="swiper-slide rounded-xl bg-primary-600 !w-full sm:!w-80 !h-64 px-9 py-5">
                    <div>
                        <img src="{{url('/images/guest/about-shop-offline.svg')}}" alt="Shop" class="mx-auto"/>
                    </div>
                    <h1 class="text-2xl font-bold text-center">Shop offline</h1>
                </div>
            </div>
            <div class="swiper-pagination !relative !mt-4 scale-150 !block md:!hidden"></div>
        </div>
        {{-- <div class="flex flex-row justify-center sm:justify-start flex-wrap gap-5 text-white mt-5"> --}}
            
           
            {{-- <div class="rounded-xl bg-purple-600 w-80 px-9 py-5">
                <div>
                    <img src="{{url('/images/guest/about-transfer.svg')}}" class="mx-auto"/>
                </div>
                <h1 class="text-2xl font-bold text-center">Receive and transfer money</h1>
            </div>
            <div class="rounded-xl bg-purple-600 w-80 px-9 py-5">
                <div>
                    <img src="{{url('/images/guest/about-schedule.svg')}}" class="mx-auto"/>
                </div>
                <h1 class="text-2xl font-bold text-center">Schedule and pay bills</h1>
            </div>
            <div class="rounded-xl bg-purple-600 w-80 px-9 py-5">
                <div>
                    <img src="{{url('/images/guest/about-accounting.svg')}}" class="mx-auto"/>
                </div>
                <h1 class="text-2xl font-bold text-center">Invest in local businesses</h1>
            </div>
            <div class="rounded-xl bg-purple-600 w-80 px-9 py-5">
                <div>
                    <img src="{{url('/images/guest/about-realestate.svg')}}" class="mx-auto"/>
                </div>
                <h1 class="text-2xl font-bold text-center">Buy and rent homes</h1>
            </div>
            <div class="rounded-xl bg-purple-600 w-80 px-9 py-5">
                <div>
                    <img src="{{url('/images/guest/about-shop.svg')}}" class="mx-auto"/>
                </div>
                <h1 class="text-2xl font-bold text-center">Shop online</h1>
            </div>
            <div class="rounded-xl bg-purple-600 w-80 px-9 py-5">
                <div>
                    <img src="{{url('/images/guest/about-shop-offline.svg')}}" class="mx-auto"/>
                </div>
                <h1 class="text-2xl font-bold text-center">Shop offline</h1>
            </div> --}}
        {{-- </div> --}}
    </div>
    
    <div class="pt-14">
        <x-guest.download-app-section />
    </div>
</div>