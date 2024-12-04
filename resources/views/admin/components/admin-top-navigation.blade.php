<header class="h-[64px] flex flex-row justify-end bg-white px-12 py-3" x-data="{
    isDropdownVisible: false,
    isLogoutModalVisible: false,
}">
    <div class="relative">
        <button @click="isDropdownVisible=true;" class="flex flex-row items-center gap-3 cursor-pointer">
            <div class="flex flex-row items-center gap-2">
                <div class="w-10 h-10">
                    <img src="{{ url('images/user/default-avatar.png') }}" alt="" class="w-full h-full object-cover rounded-full" />
                </div>
                <p>Repay Admin User</p>
            </div>
            <div @click="isDropdownVisible=true">
                <x-icon.solid-arrow-down />
            </div>
        </button>

        {{-- Dropdown --}}
        <div x-cloak x-show="isDropdownVisible" @click.away="isDropdownVisible=false"
            class="top-[100%] right-[5%] absolute flex flex-col w-52 bg-white border rounded-md items-start z-30">
            <a  href="{{ route('user.dashboard') }}" class="block hover:bg-slate-100 px-3 py-2">Switch to User
                Dashboard</a>
            <hr>
            <div role="button" tabindex="0" @keyup.enter="isLogoutModalVisible=true"  @click="isLogoutModalVisible=true" class="w-full px-3 py-2 text-left cursor-pointer hover:bg-rp-neutral-50 h-max">Logout</div>
        </div>
    </div>

    <x-modal x-model="isLogoutModalVisible">
        <x-modal.confirmation-modal title="Logout" message="Are you sure you want to log out?">
            <x-slot:action_buttons>
                <x-button.outline-button color="primary" class="w-1/2" @click="isLogoutModalVisible=false;">Cancel</x-button.outline-button>
                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                    @csrf
                </form>
                <x-button.filled-button color="primary" x-data @click.prevent="document.getElementById('logout-form').submit()" class="w-1/2">Logout</x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.confirmation-modal>
    </x-modal>
</header>
