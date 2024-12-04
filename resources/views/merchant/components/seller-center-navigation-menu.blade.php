<header class="w-full h-[60px] px-5 py-2 flex flex-row justify-end items-center bg-white" 
x-data="{
    isDropdownVisible: false,
    isLogoutModalVisible: false,
}">
    <div class="relative flex flex-row items-center gap-5">
        {{-- @if (!empty($merchant->franchise))
            <a  href="{{ route('merchant.franchise.dashboard', ['merchantIdentifier' => $merchantIdentifier]) }}">
                Franchise
            </a>
        @endif --}}
        @canany(['merchant-cash-inflow', 'merchant-cash-outflow', 'merchant-invoices', 'merchant-bills', 'merchant-employees', 'merchant-payroll'], [request('merchant'), 'view'])
            <a  href="{{ route('merchant.seller-center.dashboard', ['merchant' => request('merchant')]) }}"
                class="{{ request()->routeIs('merchant.seller-center.*') ? 'underline' : '' }}">Seller Center</a>
            <a  href="{{ route('merchant.financial-transactions.dashboard', ['merchant' => request('merchant')]) }}"
                class="{{ request()->routeIs('merchant.financial-transactions.*') ? 'underline' : '' }}">
                Financial Transactions
            </a>
        @endcanany
        <div class="flex flex-row items-center gap-3">
            {{-- dropdown button --}}
            <button @click="isDropdownVisible=!isDropdownVisible" class="flex flex-row items-center gap-5 cursor-pointer" wire:ignore>
                <div class="pl-3 border-l-2 border-l-gray-700 border-3">
                    <div class="bg-gray-200 rounded-full w-11 h-11">
                        @php
                            $merchant = request()->route('merchant');
                            $merchant_logo = $merchant->getFirstMedia('merchant_logo')
                                ? ($merchant->getFirstMedia('merchant_logo')->disk === 's3' 
                                    ? $merchant->getFirstMedia('merchant_logo')->getTemporaryUrl(\Carbon\Carbon::now()->addMinutes(5), 'thumbnail')
                                    : $merchant->getFirstMedia('merchant_logo')->getUrl('thumbnail')) 
                                : url('images/user/default-avatar.png');                     
                        @endphp                        
                        <img src="{{ $merchant_logo }}" class="object-cover w-full h-full rounded-full" alt="">
                    </div>
                </div>
                <div>
                    <svg  width="14" height="8" viewBox="0 0 14 8"
                        fill="none">
                        <path
                            d="M12.9202 0.180054H6.69024H1.08024C0.120237 0.180054 -0.359763 1.34005 0.320237 2.02005L5.50024 7.20005C6.33024 8.03005 7.68024 8.03005 8.51024 7.20005L10.4802 5.23005L13.6902 2.02005C14.3602 1.34005 13.8802 0.180054 12.9202 0.180054Z"
                            fill="#647887" />
                    </svg>
                </div>
            </button>

            {{-- Dropdown --}}
            <div x-cloak x-show="isDropdownVisible" @click.away="isDropdownVisible=false"
                class="top-[125%] right-0 absolute flex flex-col w-40 bg-white border rounded-md z-40">
                <a  href="{{ route('merchant.seller-center.dashboard', ['merchant' => request('merchant')]) }}"
                class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Dashboard</a>
                @can('merchant-store-management', [request('merchant'), 'view'])
                    <a  href="{{ route('merchant.seller-center.store-management', ['merchant' => request('merchant')]) }}"
                    class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Store Management</a>
                @endcan
                @can('merchant-products', [request('merchant'), 'view'])
                    <a  href="{{ route('merchant.seller-center.assets.index', ['merchant' => request('merchant')]) }}"
                    class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Assets</a>
                @endcan
                @can('merchant-services', [request('merchant'), 'view'])
                    <a  href="{{ route("merchant.seller-center.services.index", ['merchant' => request('merchant')]) }}"
                    class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Services</a>
                @endcan
                @canany(['merchant-orders', 'merchant-return-orders', 'merchant-warehouse'], [request('merchant'), 'view'])
                    <a  href="{{ route("merchant.seller-center.logistics.orders.index", ['merchant' => request('merchant')]) }}"
                    class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Logistics</a>
                @endcanany
                @can('merchant-disputes', [request('merchant'), 'view'])
                    <a  href="{{ route('merchant.seller-center.disputes.index', ['merchant' => request('merchant')]) }}"
                    class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Disputes</a>
                @endcan
                <a class="w-full px-3 py-2 text-left cursor-pointer hover:bg-slate-100 h-max"
                    href="{{ route('user.dashboard') }}">
                    Switch to user dashboard
                </a>
                <hr>

                <div role="button" tabindex="0" @keyup.enter="isLogoutModalVisible=true" @click="isLogoutModalVisible=true" class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Logout</div>

            </div>
        </div>
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
