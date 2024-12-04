<x-main.content x-data="{
    isPackageQuantityModalOpen: $wire.entangle('isPackageQuantityModalOpen').live,
    isAddWarehouseModalVisible: $wire.entangle('isAddWarehouseModalVisible').live,
}">
    <x-main.title class="mb-8">Logistics</x-main.title>
    {{-- Tabs --}}
    <x-tab class="mb-8">
        <x-tab.tab-item href="{{ route('merchant.seller-center.logistics.orders.index', ['merchant' => $merchant]) }}"
            :isActive="request()->routeIs('merchant.seller-center.logistics.orders.*')" class="w-56">Orders</x-tab.tab-item>
        <x-tab.tab-item
            href="{{ route('merchant.seller-center.logistics.return-orders.index', ['merchant' => $merchant]) }}"
            :isActive="request()->routeIs('merchant.seller-center.logistics.return-orders.*')" class="w-56">Return Orders</x-tab.tab-item>
        <x-tab.tab-item
            href="{{ route('merchant.seller-center.logistics.warehouse-shipping', ['merchant' => $merchant]) }}"
            :isActive="request()->routeIs('merchant.seller-center.logistics.warehouse-shipping')" class="w-56">Warehouse and Shipping</x-tab.tab-item>
    </x-tab>

    <div class="rounded-2xl bg-white px-5 py-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-lg">My warehouses</h2>
            <x-button.filled-button @click="$wire.set('isAddWarehouseModalVisible',true);">+ add
                warehouse</x-button.filled-button>
        </div>
        <div>
            <table class="w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-[#F5F6F8]">
                        <th class="border px-3 py-2 text-left">Name</th>
                        <th class="border px-3 py-2 text-left">Address</th>
                        <th class="border px-3 py-2 text-left">Phone</th>
                        <th class="border px-3 py-2 text-left">Email</th>
                        <th class="border px-3 py-2 text-left">Package Quantity</th>
                        <th class="border px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($this->warehouses->isNotEmpty())
                        @foreach ($this->warehouses as $key => $warehouse)
                            <tr wire:key='warehouse-{{ $key }}'>
                                <td class="border px-3 py-2 text-left">{{ $warehouse->name }}</td>
                                <td class="border px-3 py-2 text-left">{{ $warehouse->location->address }}</td>
                                <td class="border px-3 py-2 text-left">{{ $warehouse->phone_number }}</td>
                                <td class="border px-3 py-2 text-left">{{ $warehouse->email }}</td>
                                <td class="border px-3 py-2 text-left text-rp-red-500 cursor-pointer"
                                    wire:click='openPackageQuantityModal({{ $warehouse->id }})'>
                                    <button>{{ $warehouse->package_quantity ?? 0 }} ></button></td>
                                <td wire:click='openEditWarehouseModal({{ $warehouse->id }})'
                                    class="border px-3 py-2 cursor-pointer text-left text-rp-red-500">
                                    <button>Edit</button></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    @if ($this->shipping_options->isNotEmpty())
        <div class="rounded-2xl bg-white px-5 py-6">
            <h2 class="font-bold text-lg mb-1">Shipping Provider</h2>
            <p class="mb-4">Set up your preferred shipping providers here</p>
            <div class="flex flex-col gap-5">
                @foreach ($this->shipping_options as $key => $shipping_option)
                    <div class="border border-rp-neutral-200 rounded-lg cursor-pointer overflow-hidden"
                        x-data="{ isExpanded: false }" wire:key='shipping_option-{{ $key }}'>
                        <div tabindex="0" @keyup.enter.stop="isExpanded=!isExpanded" class="w-full rounded-lg flex px-7 py-4 items-center justify-between cursor-pointer hover:bg-rp-neutral-50"
                            @click="isExpanded=!isExpanded">
                            <p>{{ $shipping_option->name }}</p>
                            <div class="flex items-center gap-6">
                                <div tabindex="0" @keyup.enter.stop="() => { 
                                    const inputEl = document.getElementById('shipping_option-{{ $key }}');
                                    inputEl.checked = !inputEl.checked;
                                    $wire.update_merchant_shipping('{{ $shipping_option->slug }}', inputEl.checked);
                                }" class="toggle-switch" @click.stop>
                                    <input class="toggle-input" id="shipping_option-{{ $key }}"
                                        type="checkbox"
                                        @change="$wire.update_merchant_shipping('{{ $shipping_option->slug }}', $event.target.checked)"
                                        {{ in_array($shipping_option->id, $this->merchant_shipping_options) ? 'checked' : '' }}>
                                    <label class="toggle-label" for="shipping_option-{{ $key }}"></label>
                                </div>
                                <div>
                                    <svg  width="12" height="7"
                                        viewBox="0 0 12 7" fill="none"
                                        x-bind:style="!isExpanded ? 'transform: rotate(180deg); transition: transform 0.3s ease;' :
                                            'transform: rotate(0deg); transition: transform 0.3s ease;'">
                                        <path d="M1 5.94531L6 0.945312L11 5.94531" stroke="#647887"
                                            stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div x-cloak x-show="isExpanded"
                            x-transition:enter="transition-transform transition-opacity ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-end="opacity-0 transform -translate-y-3" class="border-t px-7 py-4">
                            <p class="mb-3">{{ $shipping_option->description }}</p>
                            {{-- <div>
                                <p class="font-thin text-sm">Constraints</p>
                                <p>Max weight: 100 kg</p>
                            </div> --}}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif



    {{-- ------ MODAL STARTS HERE --------- --}}

    <x-modal x-model="isPackageQuantityModalOpen">
        @if ($isPackageQuantityModalOpen and $warehouse_id)
            <livewire:merchant.seller-center.logistics.warehouse-shipping.warehouse-show :merchant_id="$merchant->id"
                :warehouse_id="$warehouse_id" />
        @endif
    </x-modal>

    {{-- Add Warehouse Modal --}}
    <x-modal x-model="isAddWarehouseModalVisible">
        @vite(['resources/js/leaflet-map.js'])
        @if ($isAddWarehouseModalVisible)
            <livewire:merchant.seller-center.logistics.warehouse-shipping.warehouse-create :merchant_id="$merchant->id"
                :warehouse_id="$warehouse_id" />
        @endif
    </x-modal>

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

    <x-loader.black-screen wire:loading.delay wire:target="openPackageQuantityModal,openEditWarehouseModal">
        <x-loader.clock />
    </x-loader.black-screen>
</x-main.content>

@push('style')
    <style>
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 24px;

        }

        .toggle-switch .toggle-input {
            display: none;
        }

        .toggle-switch .toggle-label {
            position: absolute;
            top: 0;
            left: 0;
            width: 40px;
            height: 24px;
            background-color: #90A1AD;
            border-radius: 34px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .toggle-switch .toggle-label::before {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            background-color: #fff;
            box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s;
        }

        .toggle-switch .toggle-input:checked+.toggle-label {
            background-color: #FF3D8F;
        }

        .toggle-switch .toggle-input:checked+.toggle-label::before {
            transform: translateX(16px);
        }

        .toggle-switch.light .toggle-label {
            background-color: #BEBEBE;
        }

        .toggle-switch.light .toggle-input:checked+.toggle-label {
            background-color: #9B9B9B;
        }

        .toggle-switch.light .toggle-input:checked+.toggle-label::before {
            transform: translateX(6px);
        }

        .toggle-switch.dark .toggle-label {
            background-color: #4B4B4B;
        }

        .toggle-switch.dark .toggle-input:checked+.toggle-label {
            background-color: #717171;
        }

        .toggle-switch.dark .toggle-input:checked+.toggle-label::before {
            transform: translateX(16px);
        }
    </style>
@endpush
