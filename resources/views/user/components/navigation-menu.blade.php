<header class="px-5 py-2 h-[60px] flex flex-row justify-end items-center bg-white " x-data="{
    isDropdownVisible: false,
    isSwitchToMerchantVisible: false,
    isMerchant: false {{-- @js(auth()->user()->isMerchant()) --}},
    isLogoutModalVisible: false
}">

    <div class="relative">
        <button @click="isDropdownVisible=!isDropdownVisible" class="flex flex-row items-center gap-5 cursor-pointer">
            <div class="bg-gray-200 rounded-full w-11 h-11">
                @if ($profile_picture = auth()->user()->getMedia('profile_picture')->first())
                    <img src="{{ $profile_picture->disk === 's3' ? $profile_picture->getTemporaryUrl(Carbon::now()->addMinutes(5), 'thumbnail') : $profile_picture->getUrl('thumbnail') }}"
                        alt="{{ auth()->user()->username . ' Profile Picture' }}"
                        class="object-cover w-full h-full rounded-full" />
                @else
                    <img src="{{ url('images/user/default-avatar.png') }}" alt="Default Avatar"
                        class="object-cover w-full h-full rounded-full" />
                @endif
            </div>
            <div>
                <svg  width="14" height="8" viewBox="0 0 14 8" fill="none">
                    <path
                        d="M12.9202 0.180054H6.69024H1.08024C0.120237 0.180054 -0.359763 1.34005 0.320237 2.02005L5.50024 7.20005C6.33024 8.03005 7.68024 8.03005 8.51024 7.20005L10.4802 5.23005L13.6902 2.02005C14.3602 1.34005 13.8802 0.180054 12.9202 0.180054Z"
                        fill="#647887" />
                </svg>
            </div>
        </button>

        {{-- Dropdown --}}
        <div x-cloak x-show="isDropdownVisible" @click.away="isDropdownVisible=false"
            class="top-[100%] right-[10%] absolute flex flex-col w-52 bg-white border rounded-md z-10">
            <a  href="{{ route('user.dashboard') }}"
                 class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Dashboard</a>
            <a  href="{{ route('user.cash-inflow') }}"
                 class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Cash Inflow</a>
            <a  href="{{ route('user.cash-outflow.index') }}"
                 class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Cash Outflow</a>
            <a  href="{{ route('user.bills') }}"
                 class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Bills</a>
            <a  href="{{ route('user.orders.index') }}"
                 class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Orders</a>
            <a  href="{{ route('user.return-orders.index') }}"
                 class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Return Orders</a>
            <a  href="{{ route('user.disputes.index') }}"
                class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Disputes</a>
            <hr class="my-2"/>
            {{-- auth()->user()->isMerchant() and auth()->user()->merchants()->count() > 0 --}}
            @if (auth()->user()->employee()->exists())
                <div role="button" tabindex="0" 
                @keyup.enter="isSwitchToMerchantVisible=true;isDropdownVisible=false;"
                @click="isSwitchToMerchantVisible=true;isDropdownVisible=false;"
                    class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Switch to Merchant
                    Account</div>
            @endif
            {{-- auth()->user()->isAdmin() --}}
            @if (auth()->user()->hasRole('administrator'))
                <a  href="{{ route('admin.dashboard') }}"
                    class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Switch to Admin
                    Dashboard</a>
            @endif
            <div role="button" tabindex="0" @keyup.enter="isLogoutModalVisible=true;" @click="isLogoutModalVisible=true" class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Logout</div>
        </div>


        <template x-teleport="body">
            <x-modal x-model="isSwitchToMerchantVisible">
                <x-modal.selection-modal title="Switch to Merchant Account">
                    <x-slot:items>
                        @php
                            $employees = auth()->user()->employee()->with(['merchant.media' => function ($query) {
                                $query->where('collection_name', 'merchant_logo');
                            }, 'role'])->get();    
                        @endphp
                        @foreach ($employees as $key => $employee)
                            <div
                                class="flex flex-col items-center justify-between h-64 px-3 py-2 border rounded-xl shadow-small-dark">
                                <div class="flex flex-col w-full">
                                    <div class="w-20 h-20 mx-auto mb-3 shadow-sm">
                                        @if ($merchant_logo = $employee->merchant->media->first())
                                            <img src="{{ $merchant_logo->disk === 's3' 
                                                ? $merchant_logo->getTemporaryUrl(\Carbon\Carbon::now()->addMinutes(5), 'thumbnail')
                                                : $merchant_logo->getUrl('thumbnail') }}"
                                                alt="{{ $employee->merchant->name . ' Logo' }}"
                                                class="object-cover w-full h-full rounded-full" />
                                        @else
                                            <img src="{{ url('images/user/default-avatar.png') }}" alt="Default Logo"
                                                class="object-cover w-full h-full rounded-full" />
                                        @endif
                                    </div>
                                    <h3 class="text-xl font-bold text-center truncate">{{ $employee->merchant->name }}</h3>
                                    <p class="text-center">Access level: 
                                        <span class="text-rp-pink-500">
                                            {{ $employee->role->name }}
                                        </span>
                                    </p>
                                </div>
                                <div class="w-full h-[1px] bg-[#BBC5CD]"></div>
                                @if ($employee->merchant->status == 'verified')
                                    @if ($employee->role->slug == 'employee')
                                        <x-button.filled-button
                                            size="sm"
                                            href="{{ route('merchant.seller-center.dashboard', $employee->merchant->account_number) }}"
                                            class="w-36">
                                            Switch
                                        </x-button.filled-button>
                                    @else
                                        <x-button.filled-button
                                            size="sm"
                                            href="{{ route('merchant.financial-transactions.dashboard', $employee->merchant->account_number) }}"
                                            class="w-36">
                                            Switch
                                        </x-button.filled-button>
                                    @endif
                                @else
                                    <p class="italic text-center text-stale-500">Merchant account is under review</p>
                                @endif
                            </div>
                        @endforeach
                    </x-slot:items>
                </x-modal.selection-modal>
            </x-modal>
        </template>


    </div>

    <x-modal x-model="isLogoutModalVisible">
        <x-modal.confirmation-modal title="Logout" message="Are you sure you want to log out?">
            <x-slot:action_buttons>
                <x-button.outline-button class="w-1/2" @click="isLogoutModalVisible=false;">Cancel</x-button.outline-button>
                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                    @csrf
                </form>
                <x-button.filled-button x-data @click.prevent="document.getElementById('logout-form').submit()" class="w-1/2">Logout</x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>
    </x-modal>
</header>
