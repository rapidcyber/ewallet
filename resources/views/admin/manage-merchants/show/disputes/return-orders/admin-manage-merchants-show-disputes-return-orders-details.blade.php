<x-main.content>
    <x-main.title class="mb-8">Return Dispute Details</x-main.title>

    <div class="flex flex-col px-4 py-3 bg-white rounded-md w-full text-sm break-words mb-6">
        <div class="flex flex-row justify-between pb-2 border-b w-full">
            <div class="flex flex-col max-w-[50%]">
                <p>Buyer: <span class="text-primary-600">John Doe</span></p>
                <p>Merchant: <span class="text-primary-600">Joan Doe</span></p>
            </div>
            <div class="max-w-[50%]">
                <p>Delivered to buyer on May 27, 2023</p>
                <p>Return requested on May 27, 2023</p>
            </div>
        </div>
        <div class="flex flex-row py-3 justify-between break-words">
            {{-- Order details --}}
            <div class="flex flex-row gap-3 w-5/12">
                <div class="flex-[20%]">
                    <img src="/images/guest/sofabed.png" />
                </div>
                <div class="flex-[80%] overflow-hidden">
                    <h2 class="font-bold text-lg truncate overflow-hidden">Sofa Bed 3-4 Seats</h2>
                    <p class="truncate">Green, Microfiber, Small</p>
                    <div class="flex flex-col mt-3">
                        <div>
                            <span>Order Number:</span>
                            <span class="text-primary-600">4912345678765432</span>
                        </div>
                        <div>
                            <span>Tracking Number:</span>
                            <span class="text-primary-600">RH012345678921</span>
                        </div>
                        <div>
                            <span>Return Request Number:</span>
                            <span class="text-primary-600">4912345678765432</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total --}}
            <div class="flex flex-col justify-center w-4/12 p-4">
                <div class="flex flex-col gap-2">
                    <p class="text-rp-neutral-500">Paid Price + Shipping Fee: <span class="text-rp-neutral-700">P 10,000</span></p>
                    <p class="text-rp-neutral-500">Refund Amount: <span class="text-rp-neutral-700">0</span></p>
                    <p class="text-rp-neutral-500">Return Reason: <span class="text-rp-neutral-700">Received wrong item</span></p>
                </div>
            </div>

            {{-- Status --}}
            <div class="flex items-center w-5/12">
                <div class="flex flex-row justify-between items-center w-full">
                    <x-status color="neutral" class="w-44">
                        <p>Pending Merchant Response</p>
                    </x-status>

                    <div class="cursor-pointer">
                        <x-icon.thin-chevron-right />
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="px-4 py-3 bg-white rounded-md w-full break-words mb-6">
        <h2 class="font-bold text-lg truncate overflow-hidden mb-3">Buyer Dispute Details</h2>
        <div class="space-y-3">
            <div class="flex text-sm">
                <p class="font-bold w-[85px]">Comments:</p>
                <p class="w-[calc(100%-85px)]">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            </div>
            <div class="text-sm">
                <p class="font-bold mb-1">Attached Images (2)</p>
                <div class="grid grid-cols-9 gap-2">
                    {{-- Image --}}
                    <div class="relative pt-[100%] w-full">
                        <div class="absolute top-0 left-0 w-full h-full">
                            <img class="rounded-xl w-full h-full object-cover" src="{{ url('images/products/sofa.png') }}"
                            alt="">
                        </div>
                    </div>
                    {{-- Image --}}
                    <div class="relative pt-[100%] w-full">
                        <div class="absolute top-0 left-0 w-full h-full">
                            <img class="rounded-xl w-full h-full object-cover" src="{{ url('images/products/sofa.png') }}"
                            alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 

    <div class="px-4 py-3 bg-white rounded-md w-full break-words mb-6">
        <h2 class="font-bold text-lg truncate overflow-hidden mb-3">Merchant Response</h2>
        <div class="space-y-3">
            <div class="flex text-sm">
                <p class="font-bold w-[85px]">Comments:</p>
                <p class="w-[calc(100%-85px)]">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            </div>
            <div class="text-sm">
                <p class="font-bold mb-1">Attached Images (2)</p>
                <div class="grid grid-cols-9 gap-2">
                    {{-- Image --}}
                    <div class="relative pt-[100%] w-full">
                        <div class="absolute top-0 left-0 w-full h-full">
                            <img class="rounded-xl w-full h-full object-cover" src="{{ url('images/products/sofa.png') }}"
                            alt="">
                        </div>
                    </div>
                    {{-- Image --}}
                    <div class="relative pt-[100%] w-full">
                        <div class="absolute top-0 left-0 w-full h-full">
                            <img class="rounded-xl w-full h-full object-cover" src="{{ url('images/products/sofa.png') }}"
                            alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 

    <div>
        <p>Decisions:</p>
        <div class="flex items-center gap-3">
            <x-button.primary-gradient-button class="w-[150px]" >refund</x-button.primary-gradient-button>
            <x-button.primary-gradient-button class="w-[150px]">return only</x-button.primary-gradient-button>
            <x-button.primary-gradient-button class="w-[150px]">return & refund </x-button.primary-gradient-button>
            <x-button.primary-gradient-button class="w-[150px]">cancel</x-button.primary-gradient-button>
        </div>
    </div>
</x-main.content>