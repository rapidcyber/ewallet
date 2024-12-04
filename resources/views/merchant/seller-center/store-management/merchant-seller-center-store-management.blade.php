<x-main.content x-data="{ featuredServices: false, featuredProducts: false, showStockRange: false, showPriceRange: false }">

    <x-main.action-header>
        <x-slot:title>Store Management</x-slot:title>
        <x-slot:actions>
            @if ($can_update)
                <x-button.filled-button class="w-32" wire:click="save">save</x-button.filled-button>
            @endif
            {{-- <x-button.outline-button class="w-32">preview live</x-button.outline-button> --}}
        </x-slot:actions>
    </x-main.action-header>

    <div class="h-[540px]">
        {{-- BANNER IMAGE AS A BACKGROUND --}}
        @if ($banner_file)
            @php
                $src = $banner_file->temporaryUrl();
                @endphp
        @else
            @php
                $src = $merchant_details->getFirstMediaUrl('merchant_banner') ? $this->get_media_url($merchant_details->getFirstMedia('merchant_banner')): null;
            @endphp
        @endif
        <div class="h-[414px] bg-no-repeat place-items-end grid bg-cover bg-center rounded-lg"
            style="background-image: url({{ $src }})">
            @if ($can_update)
                {{-- CHANGE BANNER BUTTON --}}
                <input type="file" name="banner_src" id="banner_src" class="hidden" wire:model="banner_file" accept="image/*">
                <button @click="document.getElementById('banner_src').click()"
                    class="mb-4 mr-4 rounded-lg bg-rp-neutral-400 bg-opacity-60 w-[161px] px-3 py-2 text-sm text-white font-bold hover:bg-opacity-100">
                    CHANGE BANNER
                </button>
            @endif
        </div>

        <div class="-translate-y-6 px-[23px] flex  justify-between">
            <div class="flex ">
                {{-- USER AVATAR --}}
                <div class="relative">
                    <div class="w-36 h-36">
                        {{-- :uploaded_images="$description_banner" --}}
                        @if ($photo_file)
                            @php
                                $src = $photo_file->temporaryUrl();
                            @endphp
                        @else
                            @php
                                if ($merchant_logo = $merchant->getFirstMedia('merchant_logo')) {
                                    $src = $this->get_media_url($merchant_logo);
                                } else {
                                    $src = url('/images/user/default-avatar.png');
                                }
                            @endphp
                        @endif

                        <img class="object-cover w-full h-full rounded-full " src="{{ $src }}" alt="">
                    </div>
                    @if ($can_update)
                        <input type="file" name="photo_src" id="photo_src" class="hidden" wire:model="photo_file" accept="image/*">
                        <button class="absolute right-0 p-1 bg-white bg-opacity-75 rounded-full bottom-1"
                            @click="document.getElementById('photo_src').click()">
                            <svg width="30" height="30" viewBox="0 0 24 24"
                                fill="none">
                                <path opacity="0.4"
                                    d="M15.48 3H7.52C4.07 3 2 5.06 2 8.52V16.47C2 19.94 4.07 22 7.52 22H15.47C18.93 22 20.99 19.94 20.99 16.48V8.52C21 5.06 18.93 3 15.48 3Z"
                                    fill="#647887" />
                                <path
                                    d="M21.02 2.98028C19.23 1.18028 17.48 1.14028 15.64 2.98028L14.51 4.10028C14.41 4.20028 14.38 4.34028 14.42 4.47028C15.12 6.92028 17.08 8.88028 19.53 9.58028C19.56 9.59028 19.61 9.59028 19.64 9.59028C19.74 9.59028 19.84 9.55028 19.91 9.48028L21.02 8.36028C21.93 7.45028 22.38 6.58028 22.38 5.69028C22.38 4.79028 21.93 3.90028 21.02 2.98028Z"
                                    fill="#647887" />
                                <path
                                    d="M17.8601 10.4198C17.5901 10.2898 17.3301 10.1598 17.0901 10.0098C16.8901 9.88984 16.6901 9.75984 16.5001 9.61984C16.3401 9.51984 16.1601 9.36984 15.9801 9.21984C15.9601 9.20984 15.9001 9.15984 15.8201 9.07984C15.5101 8.82984 15.1801 8.48984 14.8701 8.11984C14.8501 8.09984 14.7901 8.03984 14.7401 7.94984C14.6401 7.83984 14.4901 7.64984 14.3601 7.43984C14.2501 7.29984 14.1201 7.09984 14.0001 6.88984C13.8501 6.63984 13.7201 6.38984 13.6001 6.12984C13.4701 5.84984 13.3701 5.58984 13.2801 5.33984L7.9001 10.7198C7.5501 11.0698 7.2101 11.7298 7.1401 12.2198L6.7101 15.1998C6.6201 15.8298 6.7901 16.4198 7.1801 16.8098C7.5101 17.1398 7.9601 17.3098 8.4601 17.3098C8.5701 17.3098 8.6801 17.2998 8.7901 17.2898L11.7601 16.8698C12.2501 16.7998 12.9101 16.4698 13.2601 16.1098L18.6401 10.7298C18.3901 10.6498 18.1401 10.5398 17.8601 10.4198Z"
                                    fill="#647887" />
                            </svg>
                        </button>
                    @endif
                </div>
                {{--  USER NAME --}}
                <div class="px-3 pt-6">
                    <h1 class="text-rp-neutral-700 font-bold text-[23px] mt-5">{{ $merchant->name }}</h1>
                </div>
            </div>

            <div class="flex gap-3 pt-9">
                <div class="flex flex-col w-56 break-words 2xl:w-64 ">
                    <div class="flex w-full">
                        <div class="flex flex-col w-1/2">
                            <p class="font-bold text-rp-neutral-600">Products:</p>
                        </div>
                        <div class="flex flex-col w-1/2">
                            {{-- total products --}}
                            <p class="text-rp-purple-400 text-end">{{ $merchant->owned_products->count() }}</p>
                        </div>
                    </div>
                    <div class="flex w-full">
                        <div class="flex flex-col w-1/2">
                            <p class="font-bold text-rp-neutral-600">Ratings:</p>
                        </div>

                        <div class="w-1/2">
                            {{-- rating --}}
                            <p class="text-rp-purple-400 text-end">
                                {{ $this->merchant_rating . ' out of 5' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col w-64 2xl:w-72">
                    <div class="flex w-full">
                        <div class="flex flex-col w-1/2">
                            <p class="font-bold text-rp-neutral-600">Services:</p>
                        </div>
                        <div class="w-1/2">
                            <p class="text-end text-rp-purple-400">{{ $merchant->owned_services->count() }}</p>
                        </div>
                    </div>

                    <div class="flex w-full">
                        <div class="flex flex-col w-1/2">
                            <p class="font-bold text-rp-neutral-600">Joined:</p>
                        </div>
                        <div class="w-1/2 text-rp-purple-400 text-end">
                            <p class="text-end text-rp-purple-400">{{ $merchant->created_at->timezone('Asia/Manila')->format('F j, Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- STORE ABOUT SECTION --}}
    <div class="pt-8">
        <div class="flex items-center gap-5 mb-5">
            <h1 class="text-rp-neutral-700 text-[28px] font-bold">About Store</h1>
            @if ($can_update)
                {{-- EDIT FEATURED ABOUT SECTION BUTTON --}}
                <div class="h-[25px]">
                    <button @click="$wire.featuredAbout=true">
                        <x-icon.edit />
                    </button>
                </div>
            @endif
        </div>

        {{-- DEFAULT STORE BANNER --}}
        @if ($description_banner = $merchant_details->getFirstMedia('description_banner'))
            <img class="object-cover h-[44rem] max-h-[44rem] w-full mb-8 rounded-lg"
                src="{{ $this->get_media_url($description_banner) }}" alt="description_banner">
        @endif

        {{-- STORE DESCRIPTION --}}
        @if ($merchant_details->description)
            <p class="break-all text-start text-rp-neutral-600">
                {!! nl2br($merchant_details->description) !!}
            </p>
        @else
            <p class="italic">
                No description added ...
            </p>
        @endif
    </div>

    {{-- Featured Products --}}
    <div class="mt-8">
        <div class="flex flex-row justify-between">
            <div class="flex items-center gap-5 mb-5">
                <h1 class="text-rp-neutral-700 text-[28px] font-bold">Featured Products</h1>
                @if ($can_update)
                    {{-- EDIT BUTTON --}}
                    <div class="h-[25px]">
                        <button @click="featuredProducts=true" wire:click="loadProductsList">
                            <x-icon.edit />
                        </button>
                    </div>
                @endif
            </div>

            <div>

                <button
                    class="px-2 py-1  rounded-md font-bold {{ $featured_products_currentPageNumber === 1 ? 'bg-gray-200' : 'bg-white' }}"
                    {{ $featured_products_currentPageNumber === 1 ? 'disabled' : '' }}
                    wire:click.prevent="handlePageArrow('fp','left')">
                    &#171; </button>
                <button
                    class="px-2 py-1  rounded-md font-bold {{ $featured_products_currentPageNumber != $featured_products_totalPages ? 'bg-white' : 'bg-gray-200' }}"
                    {{ $featured_products_currentPageNumber != $featured_products_totalPages ? '' : 'disabled' }}
                    wire:click.prevent="handlePageArrow('fp','right')">
                    &#187; </button>
            </div>
        </div>

        {{-- List --}}
        <div class="flex flex-row gap-4">
            @forelse ($this->featured_products as $product)
                <div class="w-[215px] mb-4">
                    <div class=" h-[166px] rounded-t-[13px] overflow-hidden">
                        <div class="relative w-[215px] h-full">
                            <img class="object-cover w-full h-full"
                                src="{{ $this->get_media_url($product->first_image, 'thumbnail') }}" alt="">
                            <div class="absolute top-0 flex flex-wrap gap-2 p-2">
                                {{-- PRODUCT TAGS --}}
                                @if ($product->sale_amount > 0)
                                    <div
                                        class="text-[7px] bg-[#CE0058] text-white px-2 py-1 text-center object-contain rounded-3xl font-medium">
                                        Sale</div>
                                @endif

                                {{-- <div
                                    class="text-[7px] bg-[#EFF8FF] text-[#175CD3] px-2 py-1 text-center object-contain rounded-3xl font-medium">
                                    3 variants</div> --}}
                                <div
                                    class="text-[7px] bg-[#EEF4FF] text-[#3538CD] px-2 py-1 text-center object-contain rounded-3xl font-medium">
                                    {{ $product->condition->name }}</div>
                                {{-- <div
                                    class="text-[7px] bg-[#ECFDF3] text-[#027A48] px-2 py-1 text-center object-contain rounded-3xl font-medium">
                                    Free Shipping</div> --}}
                            </div>
                        </div>
                    </div>

                    <div class=" p-[13px] bg-white rounded-b-[14px] shadow-md">
                        <p class="text-rp-neutral-500 text-[9px]">Product</p>
                        {{-- PRODUCT NAME --}}
                        <h3
                            class="text-rp-neutral-800 text-[13px] font-bold mb-1 max-h-[3em] overflow-hidden text-ellipsis">
                            {{ $product->name }}
                        </h3>
                        {{-- PRODUCT PRICE AFTER DISCOUNT --}}
                        <h2 class="text-rp-purple-600 text-[19px] font-bold">{{ \Number::currency($product->price - $product->price * $product->sale_amount, 'PHP') }}</h2>

                        @if ($product->sale_amount > 0)
                            <div class="flex gap-2">
                                {{-- PRODUCT ORIGINAL PRICE --}}
                                <h3 class="line-through text-rp-neutral-500">{{ \Number::currency($product->price, 'PHP') }}
                                </h3>
                                {{-- DISCOUNT --}}
                                <p>-{{ round($product->sale_amount * 100, 2) }}%</p>
                            </div>
                        @endif
                        <div class="flex gap-1">
                            {{-- QUANTITY OF SOLD ITEMS --}}
                            <p class="text-rp-neutral-500 font-bold text-[9px]">{{ $product->sold_count }}</p>
                            <p class="text-rp-neutral-500 text-[9px]">Sold</p>
                        </div>
                        <div class="flex gap-1 items-center pt-[6px]">
                            {{-- OVER-ALL STAR RATINGS --}}
                            <div class="flex items-center gap-1">
                                @php
                                    $productRating = $product->rating;
                                @endphp
                                @for ($i = 1; $i <= 5; $i++)
                                    @php
                                        $fillType = 'none';
                                        if ($productRating >= $i) {
                                            $fillType = 'full';
                                        } elseif ($productRating > $i - 1 && $productRating < $i) {
                                            $fillType = 'half';
                                        }
                                    @endphp
                                    <x-icon.product.star key="star{{ $product->id . $i }}"
                                        fillType="{{ $fillType }}" width="12" height="12" />
                                @endfor
                            </div>
                            <p class="text-rp-neutral-500 text-[11px]">
                                ({{ $product->reviews_count }})
                            </p>
                        </div>
                    </div>
                </div>

            @empty
                <p class="italic">
                    No featured products ...
                </p>
            @endforelse
        </div>
    </div>

    {{-- Featured Services --}}
    <div class="mt-8">
        <div class="flex flex-row justify-between">
            <div class="flex items-center gap-5 mb-5">
                <h1 class="text-rp-neutral-700 text-[28px] font-bold">Featured Services</h1>
                @if ($can_update)
                    {{-- EDIT BUTTON --}}
                    <div class="h-[25px]">
                        <button @click="featuredServices=true" wire:click="loadServicesList">
                            <x-icon.edit />
                        </button>
                    </div>
                @endif
            </div>
            <div>
                <button
                    class="px-2 py-1  rounded-md font-bold {{ $featured_services_currentPageNumber === 1 ? 'bg-gray-200' : 'bg-white' }}"
                    {{ $featured_services_currentPageNumber === 1 ? 'disabled' : '' }}
                    wire:click.prevent="handlePageArrow('fs','left')">
                    &#171; </button>
                <button
                    class="px-2 py-1  rounded-md font-bold {{ $featured_services_currentPageNumber != $featured_services_totalPages ? 'bg-white' : 'bg-gray-200' }}"
                    {{ $featured_services_currentPageNumber != $featured_services_totalPages ? '' : 'disabled' }}
                    wire:click.prevent="handlePageArrow('fs','right')">
                    &#187; </button>
            </div>
        </div>

        {{-- List --}}
        <div class="flex flex-row gap-4">
            @forelse ($this->featured_services as $service)
                <div class="w-[215px] mb-4">
                    <div class=" h-[166px] rounded-t-[13px] overflow-hidden">
                        <div class="relative w-[215px] h-full">
                            <img class="object-cover w-full h-full"
                                src="{{ $this->get_media_url($service->first_image, 'thumbnail') }}" alt="">
                        </div>
                    </div>

                    <div class=" p-[13px] bg-white rounded-b-[14px] shadow-md">
                        <p class="text-rp-neutral-500 text-[9px]">Service</p>
                        {{-- PRODUCT NAME --}}
                        <h3
                            class="text-rp-neutral-800 text-[19px] font-bold mb-1 max-h-[3em] overflow-hidden text-ellipsis">
                            {{ $service->name }}
                        </h3>
                        {{-- PRODUCT PRICE AFTER DISCOUNT --}}
                        <h2 class="text-rp-purple-600 text-[13px] font-bold max-h-[3em] overflow-hidden text-ellipsis">
                            {{ $service->location->address }}
                        </h2>

                        <div class="flex gap-1 items-center pt-[6px]">
                            {{-- OVER-ALL STAR RATINGS --}}
                            <div class="flex gap-1">
                                @php
                                    $serviceRating = $service->rating;
                                @endphp
                                @for ($i = 1; $i <= 5; $i++)
                                    @php
                                        $fillType = 'none';
                                        if ($serviceRating >= $i) {
                                            $fillType = 'full';
                                        } elseif ($serviceRating > $i - 1 && $serviceRating < $i) {
                                            $fillType = 'half';
                                        }
                                    @endphp
                                    <x-icon.product.star key="star{{ $service->id . $i }}"
                                        fillType="{{ $fillType }}" width="12" height="12" />
                                @endfor
                            </div>

                            <p class="text-rp-neutral-500 text-[11px]">
                                ({{ $service->reviews_count }})
                            </p>


                        </div>
                    </div>
                </div>
            @empty
                <p class="italic">
                    No featured services ...
                </p>
            @endforelse
        </div>
    </div>

    {{-- MAP SECTION --}}
    @vite(['resources/js/leaflet-map.js'])
    <div class="mt-8">
        <h1 class="text-rp-neutral-700 text-[24px] font-bold mb-2">Map of Warehouses and Services</h1>
        {{-- LEAFLET MAP COMPONENT --}}
        <div>
            <div wire:ignore id="map" class="w-full h-[570px] border rounded-lg z-20" x-init="function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(position => {
                        $wire.set('latitude', position.coords.latitude);
                        $wire.set('longitude', position.coords.longitude);
                        $wire.dispatch('locationInitialized');
                    }, error => {
                        console.error('Error getting location:', error);
                        $wire.dispatch('locationInitialized');
                    });
                }

                const L = Leaflet;

                let map = L.map('map').setView([{{ $latitude }}, {{ $longitude }}], 12);
                let circle = L.circle([{{ $latitude }}, {{ $longitude }}], {
                    radius: 5000
                }).addTo(map);
                const markerGroup = L.layerGroup().addTo(map);

                map.on('click', function(ev) {
                    markerGroup.clearLayers();
                    map.removeLayer(circle);
                    circle = L.circle([ev.latlng.lat, ev.latlng.lng], {
                        radius: 5000
                    }).addTo(map);
                    @this.set('latitude', ev.latlng.lat);
                    @this.set('longitude', ev.latlng.lng);
                    $wire.dispatch('locationInitialized');
                });

                $wire.on('markers_initialized', data => {
                    console.log(data);
                    let services = data[0]['services'] ?? [];
                    let warehouses = data[0]['warehouses'] ?? [];

                    const serviceIcon = L.icon({
                        iconUrl: `${window.location.origin}/images/map/business.png`,
                        iconSize: [36, 51],
                        iconAnchor: [15, 51],
                    });
                    const warehouseIcon = L.icon({
                        iconUrl: `${window.location.origin}/images/map/industries.png`,
                        iconSize: [36, 51],
                        iconAnchor: [15, 51],
                    });

                    services.forEach(function(val) {
                        L.marker([val.latitude, val.longitude], {
                            icon: serviceIcon
                        }).bindPopup(val.name, {
                            offset: L.point(3, -46)
                        }).addTo(markerGroup);
                    })
                    warehouses.forEach(function(val) {
                        L.marker([val.latitude, val.longitude], {
                            icon: warehouseIcon
                        }).bindPopup(val.name, {
                            offset: L.point(3, -46)
                        }).addTo(markerGroup);
                    })

                    L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    }).addTo(map);
                });
            }">
            </div>
        </div>
    </div>

    {{-- PRODUCTS --}}
    <div class="mt-8">
        <div class="flex justify-between mb-5">
            {{-- USER STORE NAME --}}
            <h3 class="text-rp-neutral-700 text-[22px] font-bold">Products by {{ $merchant->name }}</h3>

            <div>
                <button
                    class="px-2 py-1  rounded-md font-bold {{ $products_currentPageNumber === 1 ? 'bg-gray-200' : 'bg-white' }}"
                    {{ $products_currentPageNumber === 1 ? 'disabled' : '' }}
                    wire:click.prevent="handlePageArrow('p','left')">
                    &#171; </button>
                <button
                    class="px-2 py-1  rounded-md font-bold {{ $products_currentPageNumber != $products_totalPages ? 'bg-white' : 'bg-gray-200' }}"
                    {{ $products_currentPageNumber != $products_totalPages ? '' : 'disabled' }}
                    wire:click.prevent="handlePageArrow('p','right')">
                    &#187; </button>
            </div>
        </div>

        {{-- List --}}
        <div class="flex flex-row gap-4">
            @forelse ($this->products as $product)
                <div class="w-[215px] mb-4">
                    <div class=" h-[166px] rounded-t-[13px] overflow-hidden">
                        <div class="relative w-[215px] h-full">
                            @if ($product->first_image)
                                <img class="object-cover w-full h-full"
                                    src="{{ $this->get_media_url($product->first_image, 'thumbnail') }}" alt="">
                            @endif
                            <div class="absolute top-0 flex flex-wrap gap-2 p-2">
                                {{-- PRODUCT TAGS --}}
                                @if ($product->sale_amount > 0)
                                    <div
                                        class="text-[7px] bg-[#CE0058] text-white px-2 py-1 text-center object-contain rounded-3xl font-medium">
                                        Sale</div>
                                @endif
                                {{-- <div
                                    class="text-[7px] bg-[#EFF8FF] text-[#175CD3] px-2 py-1 text-center object-contain rounded-3xl font-medium">
                                    3 variants</div> --}}
                                <div
                                    class="text-[7px] bg-[#EEF4FF] text-[#3538CD] px-2 py-1 text-center object-contain rounded-3xl font-medium">
                                    {{ $product->condition->name }}</div>
                                {{-- <div
                                    class="text-[7px] bg-[#ECFDF3] text-[#027A48] px-2 py-1 text-center object-contain rounded-3xl font-medium">
                                    Free Shipping</div> --}}
                            </div>
                        </div>
                    </div>

                    <div class=" p-[13px] bg-white rounded-b-[14px] shadow-md">
                        <p class="text-rp-neutral-500 text-[9px]">Product</p>
                        {{-- PRODUCT NAME --}}
                        <h3
                            class="text-rp-neutral-800 text-[13px] font-bold mb-1 max-h-[3em] overflow-hidden text-ellipsis">
                            {{ $product->name }}
                        </h3>
                        {{-- PRODUCT PRICE AFTER DISCOUNT --}}
                        <h2 class="text-rp-purple-600 text-[19px] font-bold">{{ \Number::currency($product->price - $product->price * $product->sale_amount, 'PHP') }}</h2>

                        @if ($product->sale_amount > 0)
                            <div class="flex gap-2">
                                {{-- PRODUCT ORIGINAL PRICE --}}
                                <h3 class="line-through text-rp-neutral-500">{{ \Number::currency($product->price, 'PHP') }}
                                </h3>
                                {{-- DISCOUNT --}}
                                <p>-{{ round($product->sale_amount * 100, 2) }}%</p>
                            </div>
                        @endif
                        <div class="flex gap-1">
                            {{-- QUANTITY OF SOLD ITEMS --}}
                            <p class="text-rp-neutral-500 font-bold text-[9px]">{{ $product->sold_count }}</p>
                            <p class="text-rp-neutral-500 text-[9px]">Sold</p>
                        </div>
                        <div class="flex gap-1 items-center pt-[6px]">
                            {{-- OVER-ALL STAR RATINGS --}}
                            <div class="flex items-center gap-1">
                                @php
                                    $productRating = $product->rating;
                                @endphp
                                @for ($i = 1; $i <= 5; $i++)
                                    @php
                                        $fillType = 'none';
                                        if ($productRating >= $i) {
                                            $fillType = 'full';
                                        } elseif ($productRating > $i - 1 && $productRating < $i) {
                                            $fillType = 'half';
                                        }
                                    @endphp
                                    <x-icon.product.star key="star{{ $product->id . $i }}"
                                        fillType="{{ $fillType }}" width="12" height="12" />
                                @endfor
                            </div>
                            <p class="text-rp-neutral-500 text-[11px]">
                                ({{ $product->reviews_count }})
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <p class="italic">
                    No services to show...
                </p>
            @endforelse
        </div>
    </div>

    {{-- SERVICES --}}
    <div class="mt-8">
        <div class="flex flex-row justify-between mb-4">
            <h3 class="text-rp-neutral-700 text-[22px] font-bold">Services by {{ $merchant->name }}</h3>
            <div>
                <button
                    class="px-2 py-1 rounded-md font-bold {{ $services_currentPageNumber === 1 ? 'bg-gray-200' : 'bg-white' }}"
                    {{ $services_currentPageNumber === 1 ? 'disabled' : '' }}
                    wire:click.prevent="handlePageArrow('s','left')">
                    &#171; </button>
                <button
                    class="px-2 py-1  rounded-md font-bold {{ $services_currentPageNumber != $services_totalPages ? 'bg-white' : 'bg-gray-200' }}"
                    {{ $services_currentPageNumber != $services_totalPages ? '' : 'disabled' }}
                    wire:click.prevent="handlePageArrow('s','right')">
                    &#187; </button>
            </div>
        </div>

        {{-- List --}}
        <div class="flex flex-row gap-4">
            @forelse ($this->services as $service)
                <div class="w-[215px] mb-4">
                    <div class=" h-[166px] rounded-t-[13px] overflow-hidden">
                        @if ($service->first_image)
                            <div class="relative w-[215px] h-full">
                                <img class="object-cover w-full h-full"
                                    src="{{ $this->get_media_url($service->first_image, 'thumbnail') }}" alt="">
                            </div>
                        @endif
                    </div>

                    <div class=" p-[13px] bg-white rounded-b-[14px] shadow-md">
                        <p class="text-rp-neutral-500 text-[9px]">Service</p>
                        {{-- PRODUCT NAME --}}
                        <h3
                            class="text-rp-neutral-800 text-[13px] font-bold mb-1 max-h-[3em] overflow-hidden text-ellipsis">
                            {{ $service->name }}
                        </h3>
                        {{-- PRODUCT PRICE AFTER DISCOUNT --}}
                        <h2 class="text-rp-purple-600 text-[19px] font-bold max-h-[3em] overflow-hidden text-ellipsis">
                            {{ $service->location->address }}
                        </h2>

                        <div class="flex gap-1 items-center pt-[6px]">
                            {{-- OVER-ALL STAR RATINGS --}}
                            <div class="flex items-center gap-1">
                                @php
                                    $serviceRating = $service->rating;
                                @endphp
                                @for ($i = 1; $i <= 5; $i++)
                                    @php
                                        $fillType = 'none';
                                        if ($serviceRating >= $i) {
                                            $fillType = 'full';
                                        } elseif ($serviceRating > $i - 1 && $serviceRating < $i) {
                                            $fillType = 'half';
                                        }
                                    @endphp
                                    <x-icon.product.star key="star{{ $service->id . $i }}"
                                        fillType="{{ $fillType }}" width="12" height="12" />
                                @endfor
                            </div>
                            <p class="text-rp-neutral-500 text-[11px]">({{ $service->reviews_count }})</p>
                        </div>
                    </div>
                </div>
            @empty
                <p class="italic">
                    No services to show...
                </p>
            @endforelse
        </div>
    </div>

    {{-- ------ MODALS STARTS HERE -------- --}}
    {{-- EDIT FEATURED SERVICES MODAL --}}
    <x-modal x-model="featuredServices" aria-modal="true">
        @if ($showServicesList)
            <livewire:merchant.seller-center.store-management.services-list :merchant="$merchant" />
        @endif
    </x-modal>

    {{-- EDIT FEATURED PRODUCTS MODAL --}}
    <x-modal x-model="featuredProducts" aria-modal="true">
        @if ($showProductsList)
            <livewire:merchant.seller-center.store-management.products-list :merchant="$merchant" />
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

    {{-- ABOUT STORE MODAL --}}
    <x-modal x-model="$wire.featuredAbout" aria-modal="true">
        <x-modal.form-modal title="About Store" class="w-[720px]" @click.outside="$wire.featuredAbout = false">
            <div class="space-y-3">
                {{-- Image Upload Input --}}
                <x-input.input-group>
                    <x-slot:label><span class="text-[#F0146C]">*</span>Banner Image</x-slot:label>
                    <livewire:components.input.interactive-upload-images :uploaded_images="$banner" :max="1"
                        function="updateDescriptionBanner" />
                </x-input.input-group>

                <x-input.input-group>
                    <x-slot:label><span class="text-[#F0146C]">*</span>Description</x-slot:label>

                    <x-input.textarea x-ref="description"
                        name="" id="" cols="30" rows="10" maxlength="1200"
                        wire:model='description' />
                    <p class="text-right text-[11px]"><span x-html="$wire.description.length"></span>/<span
                            x-html="$refs.description.maxLength"></span></p>

                </x-input.input-group>
            </div>
            <x-slot:action_buttons>
                <x-button.outline-button class="w-1/2" @click="$wire.featuredAbout=false">cancel</x-button.outline-button>
                <x-button.filled-button class="w-1/2" wire:click="submitEdit"
                    wire:target="submitEdit">submit</x-button.filled-button>
            </x-slot:action_buttons>
        </x-modal.form-modal>
    </x-modal>

    <x-loader.black-screen wire:loading wire:target='submitEdit,save' class="z-50">
        <x-loader.clock />
    </x-loader.black-screen>
</x-main.content>
