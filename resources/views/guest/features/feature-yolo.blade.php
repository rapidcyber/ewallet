<div>
    <div class="max-w-6xl mx-auto">
        <livewire:components.layout.guest.guest-navigation />
        <x-guest.features-tab />
    </div>
    <div class="max-w-6xl text-white mx-auto h-[40rem] bg-cover bg-center bg-no-repeat flex flex-col items-center justify-center yolo-hero">
        <h1 class="uppercase text-5xl font-bold">YOLO</h1>
        <p class="text-center w-[50%] tracking-wide text-lg">Message Your Loved Ones and Earn Daily Rewards with Repay! Enjoy the convenience of communication and the benefits of earning rewards, all in one app.</p>
    </div>

    <div class="flex flex-col md:flex-row gap-3 max-w-6xl mx-auto md:h-[28rem] px-3 md:px-9">
        <div class="h-[33rem] md:flex-1 md:h-full rounded-xl bg-primary-600 text-white px-10 py-9 overflow-hidden">
            <h1 class="font-bold text-2xl mb-3">Directly Send Messages to your Contacts</h1>
            <p class="tracking-wide">Whether you’re running a business or just putting together a list of friends, our contact list can help you contact anyone at your own convenience. </p>
            <div class="mt-6">
                <img src="{{url('/images/guest/repay-chat-interface.svg')}}" alt="Repay Mobile Chat Feature Interface" class="mx-auto">
            </div>
        </div>
        <div class="h-[38rem] md:flex-1 md:h-full rounded-xl bg-primary-600 text-white px-10 py-9 overflow-hidden">
            <h1 class="font-bold text-2xl mb-3">Build Up Points for Amazing Rewards</h1>
            <p class="tracking-wide">RePay users will continously earn reward points for participating in purchases, referrals and claiming daily rewards. These reward points can be exchanged for discounted meals, merchandise and gadgets from the hottest establishments in the Philippines. </p>
            <div class="mt-6">
                <img src="{{url('/images/guest/repay-daily-reward.svg')}}" alt="Repay Mobile Daily Reward Interface" class="mx-auto">
            </div>
        </div>
    </div>

    <div class="md:pt-48">
        <x-guest.download-app-section />
    </div>
</div>
