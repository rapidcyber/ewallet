<div>
    <div class="max-w-6xl mx-auto">
        <livewire:components.layout.guest.guest-navigation />
        <x-guest.features-tab />
    </div>
    <div class="w-screen h-[40rem] bg-cover bg-no-repeat payments-hero">
        <div class="h-full flex flex-col items-center justify-center">
            <h1 class="uppercase text-5xl font-bold text-rp-neutral-700">payments</h1>
            <p class="text-center w-[50%] tracking-wide text-lg">Easily Organize and Complete Bill Payments! All of your bills can now be set up and paid via RePay’s easy payments system!</p>
        </div>
    </div>
    <div class="max-w-6xl mx-auto px-3 md:px-9">
        <div class="flex flex-col md:flex-row gap-5">
            <div class="text-white bg-primary-600 p-7 flex-[45%] rounded-xl overflow-hidden">
                <h1 class="font-bold text-2xl mb-2">Collect Receipts from Customers Automatically</h1>
                    <p class="tracking-wide mb-5">Too busy to keep track of each incoming payment? Our automated system will automatically register each incoming paid transaction with an official receipt that will be permanently logged into your system. </p>
                <div class="-mb-60">
                    <img src="{{url('/images/guest/paid-invoice.svg')}}" alt="Repay Mobile Paid Invoice Interface" class="mx-auto">
                </div>
            </div>
            <div class="text-white flex flex-col-reverse md:flex-col bg-primary-600 p-7 flex-[55%] rounded-xl overflow-hidden">
                <div>
                    <img src="{{url('/images/guest/create-invoice-interface.svg')}}" alt="Repay Mobile Create New Invoice Interface" class="mx-auto">
                </div>
                <div>
                    <h1 class="font-bold text-2xl mb-2">Issue Business Invoices while On-the-Go</h1>
                    <p class="tracking-wide mb-5">Are you constantly on the move while running your business? Users can now issue invoices easily and even set notifications for business clients when needed.</p>
                </div>
            </div>
        </div>

        <div class="bg-primary-600 p-7 text-white flex flex-col-reverse md:flex-row gap-3 md:gap-16 rounded-xl mt-5 overflow-hidden">
            <div class="sm:-mb-36">
                <img src="{{url('/images/guest/cashflow.svg')}}" alt="Repay Mobile Cashflow Interface" class="w-[50rem]">
            </div>
            <div class="flex flex-col justify-center">
                <h1 class="font-bold text-2xl mb-2">Keep Track of Transactions </h1>
                <p class="tracking-wide">Need to see where your money has been going? Our Transaction section will keep you updated with a list of every transaction you’ve made so far, along with the accompanying details per entry.</p>
            </div>
        </div>
    </div>
    <div class="pt-9">
        <x-guest.download-app-section />
    </div>
</div>