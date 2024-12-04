<?php

namespace App\Merchant\SellerCenter\Logistics\WarehouseShipping;

use App\Models\Location;
use App\Models\Merchant;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class WarehouseCreate extends Component
{
    public Merchant $merchant;
    #[Locked]
    public $button_disabled = false;
    #[Locked]
    public $warehouse = null;
    #[Locked]
    public $title = 'Add warehouse';
    public $name = '';
    public $phone_number = '';
    public $email = '';
    #[Locked]
    public $latitude = '';
    #[Locked]
    public $longitude = '';
    public $location = '';
    public $availability_time = true;
    public $availability_times = [];
    public $availability_options = [];

    // TODO: change to dynamic add/edit instead
    public function mount($merchant_id, $warehouse_id = null)
    {
        $this->merchant = Merchant::find($merchant_id);

        $this->availability_options = $this->getDefaultDays();
        if ($warehouse_id) {
            $this->title = 'Edit warehouse';
            $this->warehouse = Warehouse::where('merchant_id', $merchant_id)->with(['location', 'availabilities'])->find($warehouse_id);
            $this->name = $this->warehouse->name;
            $this->phone_number = $this->warehouse->phone_number;
            $this->email = $this->warehouse->email;
            $this->latitude = $this->warehouse->location->latitude;
            $this->longitude = $this->warehouse->location->longitude;
            $this->location = $this->warehouse->location->address;

            if ($this->warehouse->availabilities->isNotEmpty()) {
                $this->availability_time = true;
                $availabilities = $this->warehouse->availabilities->mapWithKeys(function ($availability) {
                    return [
                        $availability->day_name => [
                            'start_time' => Carbon::parse($availability->start_time)->format('h:i A'),
                            'end_time' => Carbon::parse($availability->end_time)->format('h:i A'),
                        ],
                    ];
                })->toArray();

                foreach ($this->availability_options as $key => $day) {
                    if (isset($availabilities[$day['dayName']])) {
                        $this->availability_options[$key]['checked'] = true;
                        $this->availability_options[$key]['timeslot']['start_time'] = $availabilities[$day['dayName']]['start_time'];
                        $this->availability_options[$key]['timeslot']['end_time'] = $availabilities[$day['dayName']]['end_time'];
                    }
                }
            }

            $this->dispatch('updateMap', [
                'lat' => $this->latitude,
                'lng' => $this->longitude,
            ]);
        }
    }

    private function getDefaultDays()
    {
        return [
            [
                'id' => 1,
                'dayName' => 'Monday',
                'checked' => false,
                'timeslot' => [
                    'start_time' => '',
                    'end_time' => ''
                ]
            ],
            [
                'id' => 2,
                'dayName' => 'Tuesday',
                'checked' => false,
                'timeslot' => [
                    'start_time' => '',
                    'end_time' => ''
                ]
            ],
            [
                'id' => 3,
                'dayName' => 'Wednesday',
                'checked' => false,
                'timeslot' => [
                    'start_time' => '',
                    'end_time' => ''
                ]
            ],
            [
                'id' => 4,
                'dayName' => 'Thursday',
                'checked' => false,
                'timeslot' => [
                    'start_time' => '',
                    'end_time' => ''
                ]
            ],
            [
                'id' => 5,
                'dayName' => 'Friday',
                'checked' => false,
                'timeslot' => [
                    'start_time' => '',
                    'end_time' => ''
                ]
            ],
            [
                'id' => 6,
                'dayName' => 'Saturday',
                'checked' => false,
                'timeslot' => [
                    'start_time' => '',
                    'end_time' => ''
                ]
            ],
            [
                'id' => 7,
                'dayName' => 'Sunday',
                'checked' => false,
                'timeslot' => [
                    'start_time' => '',
                    'end_time' => ''
                ]
            ],
        ];
    }

    // CONFIGURE LOCATION
    public function fetchMap($data)
    {
        $apiKey = config('services.google.geocoder_key');
        $searched = '';

        if (is_string($data)) {
            $searched = 'address=' . urlencode($data);
        } elseif (is_array($data)) {
            $searched = 'latlng=' . $data[0] . ',' . $data[1];
        }

        $response = Http::get("https://maps.googleapis.com/maps/api/geocode/json?{$searched}&key={$apiKey}");

        return $response->json();
    }

    #[On('getMapAddress')]
    public function getMapAddress($lat, $lng)
    {
        $data = $this->fetchMap([$lat, $lng]);

        if (isset($data['results'][0]['formatted_address'])) {
            $mapAddress = $data['results'][0]['formatted_address'];
            $this->location = $mapAddress;
            $this->latitude = $lat;
            $this->longitude = $lng;
        } else {
            $mapAddress = 'Address not found';
        }

        $this->dispatch('updateMap', $mapAddress);
    }

    public function getMapLatlng()
    {
        $address = $this->location;
        $data = $this->fetchMap($address);

        if ($data['status'] === 'OK') {
            $mapLatlng = [
                'lat' => $data['results'][0]['geometry']['location']['lat'],
                'lng' => $data['results'][0]['geometry']['location']['lng'],
            ];
            $this->latitude = $data['results'][0]['geometry']['location']['lat'];
            $this->longitude = $data['results'][0]['geometry']['location']['lng'];
        } else {
            $mapLatlng = 'Location not found';
        }

        $this->dispatch('updateMap', $mapLatlng);
    }

    private function validateAvailabilityDays()
    {
        $atLeastOneDayCheckedWithTimeslots = false;

        foreach ($this->availability_times as $key => $day) {
            if (!$day['checked']) {
                continue;
            }

            if (empty($day['start_time'])) {
                return $this->addError('availability_times.' . $key . '.start_time', 'Missing start time on ' . $day['dayName']);
            }

            if (empty($day['end_time'])) {
                return $this->addError('availability_times.' . $key . '.end_time', 'Missing end time on ' . $day['dayName']);
            }

            $atLeastOneDayCheckedWithTimeslots = true;

            if (strtotime($day['start_time']) >= strtotime($day['end_time'])) {
                return $this->addError('availability_times.' . $key . '.end_time', 'End time must be after start time on ' . $day['dayName']);
            }
        }

        if ($this->availability_time && !$atLeastOneDayCheckedWithTimeslots) {
            return $this->addError('availability_times', 'At least one day must be checked with timeslots');
        }

        return null;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:50',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email:rfc,dns|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location' => 'required|string|max:255',
            'availability_time' => 'boolean',
            'availability_times' => ['array', 'size:7', function ($attribute, $value, $fail) {
                $atLeastOneDayChecked = false;

                foreach ($value as $day) {
                    if ($day['checked']) {
                        $atLeastOneDayChecked = true;
                        break;
                    }
                }

                if (!$atLeastOneDayChecked) {
                    $fail('At least one day must be checked');
                }
            }],
            'availability_times.*' => 'array:dayName,checked,start_time,end_time',
            'availability_times.*.dayName' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'availability_times.*.checked' => 'boolean',
            'availability_times.*.start_time' => 'nullable|required_if:availability_times.*.checked,true|date_format:h:i A',
            'availability_times.*.end_time' => 'nullable|required_if:availability_times.*.checked,true|date_format:h:i A|after:availability_times.*.start_time',
        ], [
            'latitude.required' => 'Select a location on the map',
            'location.required' => 'Search or Select a location on the map',
            'availability_times.*.start_time.required_if' => 'The start time of a checked day is missing',
            'availability_times.*.start_time.date_format' => 'One of the start times is not in the correct format',
            'availability_times.*.end_time.required_if' => 'The end time of a checked day is missing',
            'availability_times.*.end_time.date_format' => 'One of the end times is not in the correct format',
            'availability_times.*.end_time.after' => 'One of the end times is before the start time',
        ]);

        $check_availability_days = $this->validateAvailabilityDays();

        if (!empty($check_availability_days)) {
            return;
        }

        if ($this->warehouse == null) {
            $this->create_warehouse();
        } else {
            $this->update_warehouse();
        }

        
    }

    private function create_warehouse()
    {
        $warehouse = new Warehouse;

        $warehouse->merchant_id = $this->merchant->id;
        $warehouse->name = $this->name;
        $warehouse->phone_number = $this->phone_number;
        $warehouse->email = $this->email;

        try {
            DB::beginTransaction();
            $warehouse->save();

            $location = new Location;
            $location->entity_type = Warehouse::class;
            $location->entity_id = $warehouse->id;
            $location->latitude = $this->latitude;
            $location->longitude = $this->longitude;
            $location->address = $this->location;
            $location->save();

            if ($this->availability_time) {
                foreach ($this->availability_times as $availability_time) {
                    if ($availability_time['checked']) {
                        $warehouse->availabilities()->create([
                            'day_name' => $availability_time['dayName'],
                            'start_time' => Carbon::parse($availability_time['start_time']),
                            'end_time' => Carbon::parse($availability_time['end_time']),
                        ]);
                    }
                }
            }

            DB::commit();

            $this->button_disabled = true;
            $this->dispatch('addWarehouseSuccess', 'Warehouse created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WarehouseCreate.create_warehouse: ' . $e->getMessage());
            $this->dispatch('addWarehouseFailed', 'Something went wrong. Please try again later.');
        }
    }

    private function update_warehouse()
    {
        $warehouse = $this->warehouse;

        $warehouse->name = $this->name;
        $warehouse->phone_number = $this->phone_number;
        $warehouse->email = $this->email;

        try {
            DB::beginTransaction();
            $warehouse->save();

            $location = $warehouse->location;
            $location->latitude = $this->latitude;
            $location->longitude = $this->longitude;
            $location->address = $this->location;
            $location->save();

            if ($this->availability_time) {
                foreach ($this->availability_times as $availability_time) {
                    if ($availability_time['checked']) {
                        $warehouse->availabilities()->updateOrCreate([
                            'day_name' => $availability_time['dayName']
                        ],[
                            'start_time' => Carbon::parse($availability_time['start_time']),
                            'end_time' => Carbon::parse($availability_time['end_time']),
                        ]);
                    } else {
                        $day_availability = $warehouse->availabilities()->where('day_name', $availability_time['dayName'])->first();
                        if ($day_availability) {
                            $day_availability->delete();
                        }
                    }
                }
            } else {
                if ($warehouse->availabilities()->count() > 0) {
                    foreach ($warehouse->availabilities as $availability) {
                        $availability->delete();
                    }
                }
            }

            DB::commit();

            $this->button_disabled = true;
            $this->dispatch('addWarehouseSuccess', 'Warehouse updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WarehouseCreate.update_warehouse: ' . $e->getMessage());
            $this->dispatch('addWarehouseFailed', 'Something went wrong. Please try again later.');
        }
    }

    public function render()
    {
        return view('merchant.seller-center.logistics.warehouse-shipping.warehouse-create');
    }
}
