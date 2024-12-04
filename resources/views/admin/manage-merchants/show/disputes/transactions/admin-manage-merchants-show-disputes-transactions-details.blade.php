<x-main.content>
    <x-main.title class="mb-8">Dispute Details</x-main.title>

    <x-layout.details.more-details class="mb-8">
        <x-layout.details.more-details.section title="User Details" title_text_color="primary">
            <div class="space-y-2">
                <x-layout.details.more-details.data-field field="Full Name" value="John M. Doe" />
                <x-layout.details.more-details.data-field field="Number" value="(+63) 9234-567-8910" />
                <x-layout.details.more-details.data-field field="Email" value="johndoe@email.com" />
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="General Dispute Details" title_text_color="primary">
            <div class="space-y-2">
                <div class="flex gap-2 break-words w-full">
                    <p class="text-base w-1/3">Status</p>
                    <div class="text-base font-bold w-2/3">
                        <x-status color="neutral" class="w-36">Pending</x-status>
                    </div>
                </div> 
                <x-layout.details.more-details.data-field field="Category" value="Unauthorised Transaction" />
                <x-layout.details.more-details.data-field field="Transaction Date" value="02/05/2024" />
                <x-layout.details.more-details.data-field field="Transaction Amount" value="₱5,000" />
                <x-layout.details.more-details.data-field field="Transaction Reference Number" value="11111111111111111" />
                <x-layout.details.more-details.data-field field="Date Created" value="02/05/2024" />
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Message" title_text_color="primary">
            <div class="space-y-2">
               <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris</p>
            </div>
        </x-layout.details.more-details.section>
        <x-layout.details.more-details.section title="Attachments" title_text_color="primary">
            <div class="space-y-3">
                {{-- Attachment 1 --}}
                <div class="flex">
                    <div class="w-44 h-28">
                        <img src="{{ url('images/products/sofa.png') }}" class="w-full h-full object-cover rounded-[4px]" alt="Sofa" />
                    </div>
                    <div class="px-2">
                        <strong>one-uptown-bedroom.jpg</strong>
                        <p>1.2mb</p>
                    </div>
                </div>
                {{-- Attachment 2 --}}
                <div class="flex">
                    <div class="w-44 h-28">
                        <img src="{{ url('images/products/sofa.png') }}" class="w-full h-full object-cover rounded-[4px]" alt="Sofa" />
                    </div>
                    <div class="px-2">
                        <strong>one-uptown-bedroom.jpg</strong>
                        <p>1.2mb</p>
                    </div>
                </div>
                {{-- Attachment 3 --}}
                <div class="flex">
                    <div class="w-44 h-28">
                        <img src="{{ url('images/products/sofa.png') }}" class="w-full h-full object-cover rounded-[4px]" alt="Sofa" />
                    </div>
                    <div class="px-2">
                        <strong>one-uptown-bedroom.jpg</strong>
                        <p>1.2mb</p>
                    </div>
                </div>
            </div>
        </x-layout.details.more-details.section>
    </x-layout.details.more-details>

    <div>
        <p>Decisions:</p>
        <div class="flex items-center gap-3">
            <x-button.primary-gradient-button class="w-[244px]">pay full transaction amount</x-button.primary-gradient-button>
            <x-button.primary-gradient-button class="w-[244px]">pay custom amount</x-button.primary-gradient-button>
            <x-button.primary-gradient-button class="w-[244px]">deny</x-button.primary-gradient-button>
        </div>
    </div>
</x-main.content>