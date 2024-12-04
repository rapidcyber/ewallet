<x-main.content class="!px-16 !py-10">
    <x-main.title class="mb-8">Manage Users</x-main.title>

    {{-- Filters --}}
    <div class="grid grid-cols-5 gap-3 mb-8">
        <a href="{{ route('admin.manage-users.index') }}"
            class="{{ isset($is_active_page) == false ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2 2xl:gap-4">
                <x-icon.user width="24" height="24" fill="{{ isset($is_active_page) == false ? '#ffff' : '#7f56d9' }}" />
                <p  class="{{ isset($is_active_page) == false ? 'text-white' : 'text-rp-neutral-600' }}">All users</p>
            </div>
            <span class="font-bold">{{ $this->allUsersCount }}</span>
        </a>

        <a href="{{ route('admin.manage-users.index') }}"
            class="{{ isset($is_active_page) == false ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2 2xl:gap-4">
                <x-icon.hourglass width="24" height="24" fill="{{ isset($is_active_page) == false ? '#ffff' : '#7f56d9' }}" />
                <p  class="{{ isset($is_active_page) == false ? 'text-white' : 'text-rp-neutral-600' }}">Pending</p>
            </div>
            <span class="font-bold">{{ $this->pendingUsersCount }}</span>
        </a>

        <a href="{{ route('admin.manage-users.index') }}"
            class="{{ isset($is_active_page) == false ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2 2xl:gap-4">
                <x-icon.check width="24" height="24" fill="{{ isset($is_active_page) == false ? '#ffff' : '#7f56d9' }}" />
                <p class="{{ isset($is_active_page) == false ? 'text-white' : 'text-rp-neutral-600' }}">Active</p>
            </div>
            <span class="font-bold">{{ $this->verifiedUsersCount }}</span>
        </a>

        <a href="{{ route('admin.manage-users.index') }}"
            class="{{ isset($is_active_page) == false ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2 2xl:gap-4">
                <x-icon.close-filled fill="{{ isset($is_active_page) == false ? '#ffff' : '#7f56d9' }}" />
                <div class="{{ isset($is_active_page) == false ? 'text-white' : 'text-rp-neutral-600' }} flex flex-col 2xl:flex-row 2xl:gap-2 text-left">
                    <span>Denied /</span>
                    <span>Deactivated</span>
                </div>
            </div>
            <span class="font-bold">{{ $this->deniedUsersCount }}</span>
        </a>

        <a href="{{ route('admin.manage-users.requests.index') }}"
            class="{{ isset($is_active_page) ? 'bg-purple-gradient-to-right shadow-lg z-10 text-white' : 'bg-white hover:shadow-lg transition-all text-primary-600' }} flex flex-row items-center justify-between cursor-pointer px-4 py-6 rounded-1 break-words">
            <div class="flex items-center gap-2 2xl:gap-4">
                <div class="{{ isset($is_active_page) ? 'text-white' : 'text-rp-neutral-600' }} flex flex-col 2xl:flex-row 2xl:gap-2 text-left">
                    <span>Profile Update</span>
                    <span>Requests</span>
                </div>
            </div>
            <span class="font-bold">{{ $this->profileUpdateRequestsCount }}</span>
        </a>

    </div>

    {{-- <x-layout.search-container class="mb-8">
        <x-input.search wire:model.live.debounce.300ms='searchTerm' />
    </x-layout.search-container> --}}

    {{-- Table --}}
    <div class="overflow-auto p-3 bg-white rounded-2xl">
        <x-table.standard>
            <x-slot:table_header>
                <x-table.standard.th class="w-52 min-w-52 max-w-52">
                    Name
                </x-table.standard.th>
                <x-table.standard.th>
                    Email
                </x-table.standard.th>
                <x-table.standard.th class="w-48 min-w-48 max-w-48">
                    Contact Number
                </x-table.standard.th>
                <x-table.standard.th class="w-24 min-w-24 max-w-24">
                    Country
                </x-table.standard.th>
                <x-table.standard.th class="w-48 min-w-48 max-w-48">
                    <div class="flex flex-row items-center">
                        <span>Date Created</span>
                        <button wire:click="sortTable">
                            <x-icon.sort />
                        </button>
                    </div>
                </x-table.standard.th>
            </x-slot:table_header>
            <x-slot:table_data>
                @foreach ($profile_update_requests as $key => $request)
                    <x-table.standard.row wire:key="{{ $request->id }}">
                        <x-table.standard.td class="w-52 min-w-52 max-w-52">
                            <a  href="{{ route('admin.manage-users.requests.show', $request->id) }}" class="flex flex-row items-center gap-2 hover:underline">
                                <p class="break-words">{{ $request->user->name }}</p>
                            </a>
                        </x-table.standard.td>
                        <x-table.standard.td>
                            {{ $request->user->email }}
                        </x-table.standard.td>
                        <x-table.standard.td class="w-48 min-w-48 max-w-48">
                            {{ $this->format_phone_number($request->user->phone_number, $request->user->phone_iso) }}
                        </x-table.standard.td>
                        <x-table.standard.td class="w-24 min-w-24 max-w-24">
                            {{ $request->user->phone_iso }}
                        </x-table.standard.td>
                        <x-table.standard.td class="w-48 min-w-48 max-w-48">
                            {{ \Carbon\Carbon::parse($request->created_at)->timezone('Asia/Manila')->format('Y-m-d h:i A') }}
                        </x-table.standard.td>
                        <x-table.standard.td>
                            <div class="w-max">
                                <a  href="{{ route('admin.manage-users.requests.show', $request->id) }}"><x-icon.chevron-right class="w-full" /></a>
                            </div>
                        </x-table.standard.td>
                    </x-table.standard.row>
                @endforeach
            </x-slot:table_data>
        </x-table.standard>
    </div>

    {{-- Pagination --}}
    <div class="w-full flex items-center justify-center gap-8">
        @if ($profile_update_requests->hasPages())
            <div class="flex flex-row items-center h-10 gap-0 mt-4 w-max border rounded-md overflow-hidden">
                <button wire:click="previousPage" {{ $profile_update_requests->onFirstPage() ? 'disabled' : '' }} @click="$wire.set('checkedUsers', [])"
                    class="{{ $profile_update_requests->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M6 11.5001L1 6.50012L6 1.50012" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <button class="h-full bg-white border-r px-4 py-2 cursor-default">{{ $element }}</button>
                    @else
                        <button wire:click="gotoPage({{ $element }})"
                            class="h-full px-4 py-2 border-r {{ $element == $profile_update_requests->currentPage() ? 'cursor-default bg-rp-blue-600 text-white' : 'cursor-pointer  bg-white' }}">{{ $element }}</button>
                    @endif
                @endforeach
                <button wire:click="nextPage" {{ !$profile_update_requests->hasMorePages() ? 'disabled' : '' }}
                    class="{{ !$profile_update_requests->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }} px-4 h-full py-2 border-r bg-white flex items-center">
                    <svg  width="7" height="13" viewBox="0 0 7 13"
                        fill="none">
                        <path d="M1 1.50012L6 6.50012L1 11.5001" stroke="#647887" stroke-width="1.66667"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        @endif
    </div>

    {{-- Toast Notification --}}
    @if (session()->has('success'))
        <x-toasts.success />
    @endif

    @if (session()->has('error'))
        <x-toasts.error />
    @endif

    @if (session()->has('warning'))
        <x-toasts.warning />
    @endif

    <x-loader.black-screen wire:loading class="z-10"/>
</x-main.content>
