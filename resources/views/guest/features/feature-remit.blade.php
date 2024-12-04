<div>
    <div class="max-w-6xl mx-auto">
        <livewire:components.layout.guest.guest-navigation />
        <x-guest.features-tab />

        <div class="w-full flex flex-row py-20">
            <div>
                <img src="{{url('/images/guest/repay-transaction-successful.svg')}}" alt="Repay Mobile Successful Transaction Interface">
            </div>
            <div class="flex flex-col justify-center items-center">
                <h1 class="text-5xl font-bold uppercase text-rp-neutral-700">remit</h1>
                <p class="text-center text-lg">Withdraw, deposit and transfer your funds whenever you want. RePay is always online and ready to serve your needs.</p>
            </div>
            <div class="-mt-11">
                <img src="{{url('/images/guest/repay-qr-interface.svg')}}" alt="Repay Mobile QR Code Interface">
            </div>
        </div>
    </div>
    
    <div class="max-w-6xl mx-auto mt-20 px-3 md:px-9">
        <div class="w-full flex flex-col-reverse sm:flex-row bg-primary-600 rounded-xl p-6">
            <div class="basis-1/3 flex justify-center">   
                <img src="{{url('/images/guest/security-symbol.svg')}}" alt="Big Green Security Symbol" class="w-64">
            </div>
            <div class="basis-2/3 text-white flex flex-col justify-center">
                <h1 class="text-2xl font-bold mb-3">RePay Secures a Safe and Dependable Service For You</h1>
                <p class="tracking-wide">Repay, secured and regulated by BSP, operates in partnership with UnionBank.</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 mt-3">
            <div class="flex-1 flex flex-col-reverse md:flex-col bg-primary-600 rounded-xl p-6 md:pb-24">
                <div class="h-80 flex justify-center">
                    <img src="{{url('/images/guest/clock.svg')}}" alt="Fast Bill Payment" class="w-80">
                </div>
                <div class="text-white">
                    <h1 class="text-white font-bold text-2xl mb-3">Deposit Any Time of the Day</h1>
                    <p class="trac  king-wide">No need to wait for business hours! Just use our app to deposit your money anytime</p>
                </div>
            </div>
            <div class="flex-1 flex flex-col-reverse md:flex-col bg-primary-600 rounded-xl p-9 md:pt-2 md:pb-24">
                <div class="h-80 flex justify-center">
                    <img src="{{url('/images/guest/money.svg')}}" alt="Money Conversion" class="w-80">
                </div>
                <div class="text-white">
                    <h1 class="text-white font-bold text-2xl mb-3">Convert PHP to Foreign Exchange</h1>
                    <p class="tracking-wide">Effortlessly convert pesos into different foreign currencies. Whether you're dealing with international transactions or expanding your financial reach</p>
                </div>
            </div>
        </div>
    </div>
    <div class="pt-9">
        <x-guest.download-app-section />
    </div>
</div>
