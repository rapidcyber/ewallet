{{-- Add Warehouse Modal --}}
<x-modal.form-modal title="{{ $title }}" class="!w-3/5 !min-w-[750px]" x-data="{ ...days($wire.availability_options) }">
    <div class="w-full flex gap-8">
        <div class="w-2/5 space-y-3">
            {{-- Warehouse name --}}
            <x-input.input-group>
                <x-slot:label><span class="text-[#E31C79]">*</span>Warehouse name</x-slot:label>
                <x-input wire:model='name' type="text" />
                @error('name')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>

            {{-- Phone number --}}
            <x-input.input-group>
                <x-slot:label><span class="text-[#E31C79]">*</span>Phone number</x-slot:label>
                <x-input wire:model='phone_number' type="text" />
                @error('phone_number')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>

            {{-- Email address --}}
            <x-input.input-group>
                <x-slot:label><span class="text-[#E31C79]">*</span>Email address</x-slot:label>
                <x-input wire:model='email' type="text" />
                @error('email')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>

            {{-- Pin locaton --}}
            <x-input.input-group>
                <x-slot:label><span class="text-[#E31C79]">*</span>Pin location</x-slot:label>
                <x-input value="{{ $latitude && $longitude ? $latitude . ',' . $longitude : '' }}" type="text"
                    readonly />
                @error('latitude')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>

            {{-- Location --}}
            <x-input.input-group>
                <x-slot:label><span class="text-[#E31C79]">*</span>Location</x-slot:label>
                <form wire:submit.prevent='getMapLatlng' id="searchAddress">
                    <x-input wire:model='location' id="inp_search" type="text" />
                </form>
                @error('location')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </x-input.input-group>

            {{-- Time --}}
            <div>
                <div class="flex items-center gap-2 mb-5">
                    {{-- <x-input wire:model.live='availability_time' type="checkbox" id="set_specific_availability_time" /> --}}
                    <label for="set_specific_availability_time" class="cursor-pointer">Set specific availability
                        time</label>
                </div>

                <div x-show="$wire.availability_time">
                    <div class="bg-rp-neutral-50 space-y-3 px-3 py-2 h-[250px] rounded-xl overflow-auto">
                        <template x-for="day in days" :key="day.id">
                            <div>
                                <div class="flex items-center gap-2 mb-8">
                                    <x-input type="checkbox" ::id="day.dayName" x-model="day.checked" />
                                    <label :for="day.dayName" class="cursor-pointer" x-text="day.dayName"></label>
                                </div>

                                <template x-if="day.checked == true">
                                    <div class="flex flex-row gap-1 justify-between items-center ">
                                        <div class="relative flex-1">
                                            <p class="absolute bottom-[100%] text-sm"><span
                                                    class="text-[#E31C79]">*</span>Start
                                                time</p>
        
                                            <x-dropdown.select x-model="day.timeslot['start_time']">
                                                <template x-for="(time, index) in time_options">
                                                    <x-dropdown.select.option ::value="time" ::selected="time === day.timeslot['start_time']"
                                                        ::key="index" x-text="time === '' ? '---' : time"
                                                        ::hidden="time === ''"></x-dropdown.select.option>
                                                </template>
                                            </x-dropdown.select>
                                        </div>
                                        <div>-</div>
                                        <div class="relative flex-1">
                                            <p class="absolute bottom-[100%] text-sm"><span
                                                    class="text-[#E31C79]">*</span>End
                                                time</p>
        
                                            <x-dropdown.select x-model="day.timeslot['end_time']">
                                                <template x-for="(time, index) in time_options">
                                                    <x-dropdown.select.option ::value="time" ::selected="time === day.timeslot['end_time']"
                                                        ::key="index" x-text="time === '' ? '---' : time"
                                                        ::hidden="time === ''"></x-dropdown.select.option>
                                                </template>
                                            </x-dropdown.select>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </template>
                    </div>
                    @error('availability_times')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @error('availability_times.*')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>
        <div class="w-3/5">
            <div>
                <div wire:ignore id="map" class="w-full h-[570px] border rounded-lg z-10" x-init="setTimeout(function () { window.dispatchEvent(new Event('resize')); }, 500)"></div>
            </div>
        </div>
    </div>
    <x-slot:action_buttons>
        <x-button.outline-button @click="$dispatch('closeWarehouseModal')"
            class="w-1/2">cancel</x-button.outline-button>
        <x-button.filled-button @click="submitForm" class="w-1/2" :disabled="$button_disabled">confirm</x-button.filled-button>
    </x-slot:action_buttons>
    {{-- Loader --}}
    <x-loader.black-screen wire:loading wire:target='save' class="z-30">
        <x-loader.clock />
    </x-loader.black-screen>
</x-modal.form-modal>

@script
    <script>
        Alpine.data('days', (availability_options) => ({
            days: availability_options,

            time_options: [
                '',
                '12:00 AM',
                '12:30 AM',
                '01:00 AM',
                '01:30 AM',
                '02:00 AM',
                '02:30 AM',
                '03:00 AM',
                '03:30 AM',
                '04:00 AM',
                '04:30 AM',
                '05:00 AM',
                '05:30 AM',
                '06:00 AM',
                '06:30 AM',
                '07:00 AM',
                '07:30 AM',
                '08:00 AM',
                '08:30 AM',
                '09:00 AM',
                '09:30 AM',
                '10:00 AM',
                '10:30 AM',
                '11:00 AM',
                '11:30 AM',
                '12:00 PM',
                '12:30 PM',
                '01:00 PM',
                '01:30 PM',
                '02:00 PM',
                '02:30 PM',
                '03:00 PM',
                '03:30 PM',
                '04:00 PM',
                '04:30 PM',
                '05:00 PM',
                '05:30 PM',
                '06:00 PM',
                '06:30 PM',
                '07:00 PM',
                '07:30 PM',
                '08:00 PM',
                '08:30 PM',
                '09:00 PM',
                '09:30 PM',
                '10:00 PM',
                '10:30 PM',
                '11:00 PM',
                '11:30 PM',
            ],

            submitForm() {
                const data = this.days.map(day => ({
                    dayName: day.dayName || null,
                    checked: day.checked,
                    start_time: day.timeslot.start_time || null,
                    end_time: day.timeslot.end_time || null
                }));

                @this.set('availability_times', data);

                @this.save();
            },
        }));

        let leafletMap = null;

        // LEAFLET MAP
        let L = Leaflet;
        console.log(leafletMap)
        if (leafletMap != null) {
            leafletMap.off();
            leafletMap.remove();
            leafletMap = null;
        }

        leafletMap = L.map('map');
        leafletMap.setView([14.599512, 120.984222], 13)

        const markerGroup = L.layerGroup().addTo(leafletMap);

        // TILE
        L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        }).addTo(leafletMap);

        // ICON
        const pinIcon = L.icon({
            iconUrl: `${window.location.origin}/images/map/map_pin.png`,
            iconSize: [30, 51],
            iconAnchor: [15, 51]
        });

        // MAP CLICK
        leafletMap.on('click', (e) => {
            // remove all markers
            markerGroup.clearLayers()

            const lat = e.latlng.lat;
            const lng = e.latlng.lng;

            // add new marker
            L.marker([lat, lng], {
                icon: pinIcon
            }).addTo(markerGroup);

            $wire.dispatch('getMapAddress', {
                lat,
                lng
            });
        })

        // EMIT UPDATE
        Livewire.on('updateMap', (data) => {
            if (typeof data[0] !== 'string') {
                //remove all markers
                markerGroup.clearLayers()
                // fly
                leafletMap.flyTo([data[0].lat, data[0].lng], 17);
                // add new marker
                L.marker([data[0].lat, data[0].lng], {
                    icon: pinIcon
                }).addTo(markerGroup);
            }
        });
    </script>
@endscript
