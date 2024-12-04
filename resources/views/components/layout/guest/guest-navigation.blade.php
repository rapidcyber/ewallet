<div wire:ignore class="max-w-6xl flex flex-row justify-between items-center bg-transparent w-full p-9"
    x-data="isVisible">
    @if ($whiteLogo)
        <a href="{{ route('home') }}">
            <x-logo.white-repay-logo />
        </a>
    @else
        <div>
            <a href="{{ route('home') }}">
                <x-logo.colored-repay-logo />
            </a>
        </div>
    @endif

    @php
        $agent = new Jenssegers\Agent\Agent();
        $isMobile = $agent->isMobile();
        $os = $agent->platform();
    @endphp

    <ul class="{{ $whiteText ? 'text-white' : '' }} flex-row gap-6  font-thin hidden md:flex">
        @auth
            <li class="{{ request()->routeIs('user.dashboard') ? 'underline font-bold' : '' }}">
                <a href="{{ route('user.dashboard') }}">Dashboard</a>
            </li>
        @endauth
        <li class="{{ request()->routeIs('home') ? 'underline font-bold' : '' }}">
            <a href="{{ route('home') }}">Home</a>
        </li>
        <li class="{{ request()->routeIs('features.*') ? 'text-primary-600 underline font-bold' : '' }}">
            <a href="{{ route('features.remit') }}">Features</a>
        </li>
        <li class="{{ request()->routeIs('about-us') ? 'underline font-bold' : '' }}">
            <a href="{{ route('about-us') }}">About</a>
        </li>
        <li class="{{ request()->routeIs('contact-us') ? 'underline font-bold' : '' }}">
            <a href="{{ route('contact-us') }}">Contact us</a>
        </li>
        @if ($isMobile)
            @php
                $openLink = '#';
                if ($os === 'iOS') {
                    // $openLink = "https://apps.apple.com/us/app/repay-digital-banking/id6446475056";
                    $openLink = 'https://apps.apple.com/us/app/repay-digital-banking/id6446475056';
                } elseif ($os === 'AndroidOS') {
                    $openLink = 'https://play.google.com/store/apps/details?id=com.repay.app';
                }
            @endphp
            <li><a href={{ $openLink }}
                    class="-mt-1 bg-white text-primary-600 px-2 py-1 rounded-md font-semibold hover:bg-opacity-90">Open
                    the app</a></li>
        @else
            @guest
                <li>
                    <button wire:click="redirect_sign_in"
                        class="{{ request()->routeIs('sign-in') ? 'underline font-bold' : '' }}">Sign-in</button>
                </li>
            @endguest
            @auth
                <li>
                    <div role="button" tabindex="0" @keyup.enter="setLogoutVisible" @click="setLogoutVisible">
                        Logout</div>
                </li>
            @endauth
        @endif
    </ul>

    <div role="button" tabindex="0" @keyup.enter="setMobileMenuVisible" class="cursor-pointer block md:hidden"
        @click="setMobileMenuVisible">
        <svg fill="{{ $whiteText ? 'white' : '#42505A' }}" height="32px" version="1.1" viewBox="0 0 32 32"
            width="32px" xml:space="preserve">
            <path
                d="M4,10h24c1.104,0,2-0.896,2-2s-0.896-2-2-2H4C2.896,6,2,6.896,2,8S2.896,10,4,10z M28,14H4c-1.104,0-2,0.896-2,2  s0.896,2,2,2h24c1.104,0,2-0.896,2-2S29.104,14,28,14z M28,22H4c-1.104,0-2,0.896-2,2s0.896,2,2,2h24c1.104,0,2-0.896,2-2  S29.104,22,28,22z" />
        </svg>
    </div>


    <template x-teleport="body">
        <div x-cloak x-show="isMobileMenuVisible" class="fixed inset-0 bg-black bg-opacity-50 md:hidden">
            <div @click.away="setMobileMenuHidden"
                class="{{ request()->routeIs('home') || request()->routeIs('about') || request()->routeIs('about') || request()->routeIs('contact-us') || request()->routeIs('sign-in') ? 'bg-[#7F56D9] text-white' : 'bg-white' }} ml-auto  h-full flex flex-col px-3 py-2 space-y-3 min-w-48 max-w-60">
                <div class="w-7 ml-auto cursor-pointer" @click="setMobileMenuVisible">
                    <svg viewBox="0 0 50 50" width="100%"
                        fill="request()->routeIs('home') || request()->routeIs('about') || request()->routeIs('about') || request()->routeIs('contact-us') || request()->routeIs('sign-in')  ? 'white' : 'black'"
                        height="50px">
                        <path
                            d="M 7.71875 6.28125 L 6.28125 7.71875 L 23.5625 25 L 6.28125 42.28125 L 7.71875 43.71875 L 25 26.4375 L 42.28125 43.71875 L 43.71875 42.28125 L 26.4375 25 L 43.71875 7.71875 L 42.28125 6.28125 L 25 23.5625 Z" />
                    </svg>
                </div>
                <a href="{{ route('home') }}"
                    class="{{ request()->routeIs('home') ? 'underline font-bold' : '' }} {{ request()->routeIs('home') || request()->routeIs('about') || request()->routeIs('about') || request()->routeIs('contact-us') || request()->routeIs('sign-in') ? 'hover:bg-black hover:bg-opacity-10' : 'hover:bg-rp-neutral-100' }}  w-full px-3 py-2  text-center rounded-md">Home</a>
                <a href="{{ route('features.remit') }}"
                    class="{{ request()->routeIs('features.*') ? 'underline font-bold' : '' }} {{ request()->routeIs('home') || request()->routeIs('about') || request()->routeIs('about') || request()->routeIs('contact-us') || request()->routeIs('sign-in') ? 'hover:bg-black hover:bg-opacity-10' : 'hover:bg-rp-neutral-100' }} w-full px-3 py-2 text-center rounded-md">Features</a>
                <a href="{{ route('about-us') }}"
                    class="{{ request()->routeIs('about-us') ? 'underline font-bold' : '' }} {{ request()->routeIs('home') || request()->routeIs('about') || request()->routeIs('about') || request()->routeIs('contact-us') || request()->routeIs('sign-in') ? 'hover:bg-black hover:bg-opacity-10' : 'hover:bg-rp-neutral-100' }} w-full px-3 py-2 text-center rounded-md">About</a>
                <a href="{{ route('contact-us') }}"
                    class="{{ request()->routeIs('contact-us') ? 'underline font-bold' : '' }} {{ request()->routeIs('home') || request()->routeIs('about') || request()->routeIs('about') || request()->routeIs('contact-us') || request()->routeIs('sign-in') ? 'hover:bg-black hover:bg-opacity-10' : 'hover:bg-rp-neutral-100' }} w-full px-3 py-2 text-center rounded-md">Contact
                    us</a>
                @if ($isMobile)
                    @php
                        $openLink2 = '#';
                        if ($os === 'iOS') {
                            $openLink2 = 'https://apps.apple.com/us/app/repay-digital-banking/id6446475056';
                        } elseif ($os === 'AndroidOS') {
                            $openLink2 = 'https://play.google.com/store/apps/details?id=com.repay.app';
                        }
                    @endphp
                    <a href={{ $openLink2 }}
                        class="px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-center rounded-md">Open the
                        app</a>
                @else
                    @auth
                        <a href="{{ route('sign-in') }}"
                            class="{{ request()->routeIs('sign-in') ? 'underline font-bold' : '' }} {{ request()->routeIs('home') || request()->routeIs('about') || request()->routeIs('about') || request()->routeIs('contact-us') || request()->routeIs('sign-in') ? 'hover:bg-black hover:bg-opacity-10' : 'hover:bg-rp-neutral-100' }} w-full px-3 py-2 text-center rounded-md">Sign-in</a>
                    @endauth
                    @guest
                        <div role="button" tabindex="0" @keyup.enter="setLogoutVisible"
                            @click="setLogoutVisible"
                            class="{{ request()->routeIs('home') || request()->routeIs('about') || request()->routeIs('about') || request()->routeIs('contact-us') || request()->routeIs('sign-in') ? 'hover:bg-black hover:bg-opacity-10' : 'hover:bg-rp-neutral-100' }} w-full px-3 py-2 text-center rounded-md">
                            Logout</a>
                        @endguest
                @endif
            </div>
        </div>
    </template>

    @auth
        <div x-cloak x-show="isLogoutModalVisible" x-transition.duration.500ms x-transition.opacity
            class="fixed grid place-items-center inset-0 backdrop-blur-sm bg-black/20 z-50">
            <div class="max-w-96 w-[90%] px-[24px] py-[26px] space-y-4 bg-white rounded-3xl">
                <h3 class="text-3.5xl font-bold text-rp-neutral-700 text-center">Logout</h3>
            
                <p class="text-center">Are you sure you want to log out?</p>
            
                <div class="w-full flex flex-row gap-2">
                    <x-button.outline-button class="w-1/2" @click="setLogoutHidden">Cancel</x-button.outline-button>
                    <x-button.filled-button wire:click="logout" wire:target="logout" wire:loading.attr='disabled'
                        :disabled="$button_clickable == false" class="w-1/2">Logout</x-button.filled-button>
                </div>
            </div>
        </div>
    @endauth
</div>

@push('scripts')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('alpine:init', () => {
            Alpine.data('isVisible', () => {
                return {
                    isMobileMenuVisible: false,
                    isLogoutModalVisible: false,

                    setLogoutVisible() {
                        this.isLogoutModalVisible = true;
                    },

                    setLogoutHidden() {
                        this.isLogoutModalVisible = false;
                    },

                    setMobileMenuVisible() {
                        this.isMobileMenuVisible = true;
                    },

                    setMobileMenuHidden() {
                        this.isMobileMenuVisible = false;
                    }
                }
            });

            Alpine.data('modal', () => {
                return {
                    visible: false,
                    init() {
                        Livewire.on('showModal', () => {
                            this.visible = true;
                        });
                    },
                }
            });
        })
    </script>
@endpush
