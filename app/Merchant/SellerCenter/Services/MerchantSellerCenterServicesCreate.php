<?php

namespace App\Merchant\SellerCenter\Services;

use App\Models\Location;
use App\Models\Merchant;
use App\Models\Question;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Traits\WithImageUploading;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class MerchantSellerCenterServicesCreate extends Component
{
    use WithFileUploads, WithImageUploading;

    public Merchant $merchant;
    public $service_images = [];
    public $service_name;
    public $category = '';
    public $service_description;
    public $service_days = [];
    public $service_form = [];
    public $latitude, $longitude, $location;
    public $previous_works = [];
    public $previous_work_images = [];
    public $previous_work_title;
    public $previous_work_description;
    public $edit_previous_work_index;

    public $showPreviousWorkModal = false;

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        $this->service_form[] = [
            'question' => '',
            'answer' => null,
            'type' => 'paragraph',
            'choices' => [''],
            'important' => 0,
            'order' => 0,
        ];
    }

    #[Computed(persist: true)]
    public function categories()
    {
        return ServiceCategory::whereNull('parent')
            ->with(['sub_categories' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();
    }

    #[On('updateServiceImages')]
    public function updateServiceImages($images)
    {
        $this->service_images = $images;

        foreach ($this->service_images as $key => $image) {
            $this->service_images[$key]['image'] = new TemporaryUploadedFile($image['image'], config('filesystems.default'));
        }
    }

    public function addQuestion()
    {
        $this->service_form[] = [
            'question' => '',
            'answer' => null,
            'type' => 'paragraph',
            'choices' => [''],
            'important' => 0,
            'order' => count($this->service_form),
        ];
    }

    public function sortQuestion($oldIndex, $newIndex)
    {
        $question = $this->service_form[$oldIndex];

        array_splice($this->service_form, $oldIndex, 1);
        array_splice($this->service_form, $newIndex, 0, [$question]);

        foreach ($this->service_form as $key => $question) {
            $this->service_form[$key]['order'] = $key;
        }
    }

    public function removeQuestion($index)
    {
        if (count($this->service_form) > 1) {
            unset($this->service_form[$index]);
            $this->service_form = array_values($this->service_form);
        }
    }

    public function addQuestionChoice($index)
    {
        $this->service_form[$index]['choices'][] = '';
    }

    public function removeQuestionChoice($index)
    {
        if (count($this->service_form[$index]['choices']) > 1) {
            unset($this->service_form[$index]['choices'][$index]);
            $this->service_form[$index]['choices'] = array_values($this->service_form[$index]['choices']);
        }
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
            $this->location = $address;
            $this->latitude = $data['results'][0]['geometry']['location']['lat'];
            $this->longitude = $data['results'][0]['geometry']['location']['lng'];
        } else {
            $mapLatlng = 'Location not found';
        }

        $this->dispatch('updateMap', $mapLatlng);
    }

    public function addPreviousWork()
    {
        $this->validate([
            'previous_work_images' => 'array|min:1|max:5',
            'previous_work_images.*' => 'array:name,image,size,id,order',
            'previous_work_images.*.name' => 'required',
            'previous_work_images.*.image' => 'image|mimes:png,jpg,jpeg|max:5120',
            'previous_work_images.*.size' => 'required',
            'previous_work_images.*.id' => 'nullable',
            'previous_work_images.*.order' => 'required',
            'previous_work_title' => 'required|string|max:120',
            'previous_work_description' => 'required|string|max:500',
        ]);

        $this->previous_works[] = [
            'images' => $this->previous_work_images,
            'title' => $this->previous_work_title,
            'description' => $this->previous_work_description,
        ];

        $this->reset(['previous_work_images', 'previous_work_title', 'previous_work_description', 'edit_previous_work_index', 'showPreviousWorkModal']);
        $this->dispatch('resetImage', 'updatePreviousWorkImages');
    }

    public function editPreviousWork($index)
    {
        $this->edit_previous_work_index = $index;
        $this->previous_work_title = $this->previous_works[$index]['title'];
        $this->previous_work_description = $this->previous_works[$index]['description'];
        $this->previous_work_images = $this->previous_works[$index]['images'];

        $this->showPreviousWorkModal = true;
    }

    public function updatePreviousWork()
    {
        $index = $this->edit_previous_work_index;
        $this->previous_works[$index]['title'] = $this->previous_work_title;
        $this->previous_works[$index]['description'] = $this->previous_work_description;
        $this->previous_works[$index]['images'] = $this->previous_work_images;

        $this->edit_previous_work_index = null;
        $this->reset(['previous_work_images', 'previous_work_title', 'previous_work_description', 'edit_previous_work_index', 'showPreviousWorkModal']);
        $this->dispatch('resetImage', 'updatePreviousWorkImages');
    }

    public function removePreviousWork($index)
    {
        unset($this->previous_works[$index]);
        $this->previous_works = array_values($this->previous_works);
    }

    public function cancelPreviousWork()
    {
        $this->reset(['previous_work_images', 'previous_work_title', 'previous_work_description', 'edit_previous_work_index']);
        $this->dispatch('resetImage', 'updatePreviousWorkImages');
    }

    #[On('updatePreviousWorkImages')]
    public function updatePreviousWorkImages($images)
    {
        $this->previous_work_images = $images;

        foreach ($this->previous_work_images as $key => $image) {
            $this->previous_work_images[$key]['image'] = new TemporaryUploadedFile($image['image'], config('filesystems.default'));
        }
    }

    private function validateServiceForm()
    {
        $this->resetErrorBag(['service_form', 'service_form.*']);

        $service_form = [];
        foreach ($this->service_form as $key => $question) {
            if ($question['important'] && $question['type'] !== 'paragraph' && empty($question['choices'][0])) {
                return $this->addError('service_form.' . $key . '.choices', 'Please add at least one choice');
            }

            $service_form[] = [
                'question' => $question['question'],
                'type' => $question['type'],
                'important' => $question['important'],
                'choices' => $question['choices'],
            ];
        }

        $questions = array_column($service_form, 'question');

        if (count($questions) !== count(array_unique($questions))) {
            return $this->addError('service_form', 'Please make sure there are no duplicate questions');
        }

        return null;
    }

    private function validateServiceDays()
    {
        $atLeastOneDayCheckedWithTimeslots = false;
        $this->resetErrorBag(['service_days', 'service_days.*.timeslots']);

        foreach ($this->service_days as $key => $day) {
            if (!$day['checked']) {
                continue;
            }

            $timeslots = array_filter($day['timeslots'], function ($slot) {
                return $slot['start_time'] !== null && $slot['end_time'] !== null;
            });

            if (count($timeslots) === 0) {
                return 'At least one time slot must be available for a checked operating day';
            }

            $atLeastOneDayCheckedWithTimeslots = true;

            usort($timeslots, function ($a, $b) {
                return strtotime($a['start_time']) - strtotime($b['start_time']);
            });

            // Check for overlaps in the sorted timeslots
            for ($i = 1; $i < count($timeslots); $i++) {
                $previousEndTime = strtotime($timeslots[$i - 1]['end_time']);
                $currentStartTime = strtotime($timeslots[$i]['start_time']);

                if ($currentStartTime < $previousEndTime) {
                    return 'Overlapping time slots found';
                }
            }
        }

        if (!$atLeastOneDayCheckedWithTimeslots) {
            return 'At least one operating day and time must be available';
        }

        return null;
    }

    private function getServiceDays()
    {
        $service_days = [];
        foreach ($this->service_days as $key => $day) {
            if (!$day['checked']) {
                continue;
            }

            $dayKey = strtolower($day['dayName']);
            foreach ($day['timeslots'] as $timeslot) {
                if (!isset($service_days[$dayKey])) {
                    $service_days[$dayKey] = [];
                }

                if (!empty($timeslot['start_time']) && !empty($timeslot['end_time'])) {
                    $service_days[$dayKey][] = [
                        'start_time' => Carbon::parse($timeslot['start_time'])->format('H:i'),
                        'end_time' => Carbon::parse($timeslot['end_time'])->format('H:i'),
                    ];
                }
            }
        }

        return $service_days;
    }

    public function save()
    {
        try {
            $this->validate([
                'service_images' => 'array|min:1|max:5',
                'service_images.*' => 'array:name,image,size,id,order',
                'service_images.*.name' => 'required',
                'service_images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
                'service_images.*.size' => 'required',
    
                'service_name' => 'required|string|max:180',
                'category' => 'required|exists:service_categories,id',
                'service_description' => 'required|string|max:1000',
    
                'service_days' => [
                    'required',
                    'array',
                    'size:7',
                    function ($attribute, $value, $fail) {
                        if (!in_array(true, array_column($value, 'checked'))) {
                            $fail('At least one service day must be checked');
                        }
                        if ($msg = $this->validateServiceDays()) {
                            $fail($msg);
                        }
                    },
                ],
                'service_days.*' => 'array:dayName,checked,timeslots',
                'service_days.*.dayName' => 'in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
                'service_days.*.checked' => ['boolean'],
                'service_days.*.timeslots' => 'array',
                'service_days.*.timeslots.*' => 'array:start_time,end_time',
                'service_days.*.timeslots.*.start_time' => 'nullable|date_format:h:i A',
                'service_days.*.timeslots.*.end_time' => 'nullable|date_format:h:i A|after:service_days.*.timeslots.*.start_time',
    
                'service_form' => 'required|array|min:1|max:15',
                'service_form.*.question' => 'required|string|distinct|max:255',
                'service_form.*.type' => 'required|in:paragraph,multiple,checkbox,dropdown',
                'service_form.*.important' => 'boolean',
                'service_form.*.choices' => 'required_unless:service_form.*.type,paragraph|array',
                'service_form.*.choices.*' => 'required_unless:service_form.*.type,paragraph|string|max:255',
    
                'location' => 'required|string|max:255',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
    
                'previous_works' => 'array',
                'previous_works.*.images' => 'array|min:1|max:5',
                'previous_works.*.images.*' => 'array:name,image,size,id,order',
                'previous_works.*.images.*.name' => 'required',
                'previous_works.*.images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
                'previous_works.*.images.*.size' => 'required',
                'previous_works.*.images.*.id' => 'nullable',
                'previous_works.*.images.*.order' => 'required',
                'previous_works.*.title' => 'required|string|max:120',
                'previous_works.*.description' => 'required|string|max:1000',
            ],[],[
                'service_days.*.timeslots.*.start_time' => 'start time',
                'service_days.*.timeslots.*.end_time' => 'end time',
                'service_form.*.question' => 'question',
                'service_form.*.type' => 'type',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            $this->setErrorBag($ex->validator->getMessageBag());
            $errorCount = count($ex->validator->getMessageBag());
            session()->flash('error', "$errorCount error(s) prohibited this service from being saved.");
            session()->flash('error_message', 'Please check the form and resubmit.');
            return;
        }


        $check_service_form = $this->validateServiceForm();

        $check_service_days = $this->validateServiceDays();

        if (!$check_service_days) {
            $this->addError('service_days', $check_service_days);
        }

        if (!empty($check_service_form)) {
            return;
        }

        $service_days = $this->getServiceDays();

        $service = new Service;
        $service->merchant_id = $this->merchant->id;
        $service->name = $this->service_name;
        $service->service_category_id = $this->category;
        $service->description = $this->service_description;
        $service->service_days = $service_days;

        $location = new Location;
        $location->address = $this->location;
        $location->latitude = $this->latitude;
        $location->longitude = $this->longitude;

        DB::beginTransaction();
        try {
            $service->save();

            foreach ($this->service_images as $image) {
                $this->upload_file_media($service, $image['image'], 'service_images');
            }

            $location->entity()->associate($service);
            $location->save();

            foreach ($this->service_form as $form) {
                $question = new Question;
                $question->entity_id = $service->id;
                $question->entity_type = Service::class;
                $question->question = $form['question'];
                $question->type = $form['type'];
                $question->is_important = $form['important'];
                $question->order_column = $form['order'];

                $question->save();

                if ($form['type'] == 'multiple' || $form['type'] == 'checkbox' || $form['type'] == 'dropdown') {
                    foreach ($form['choices'] as $choice) {
                        $question->choices()->create([
                            'question_id' => $question->id,
                            'value' => $choice,
                        ]);
                    }
                }
            }

            foreach ($this->previous_works as $work) {
                $previous_work = $service->previous_works()->create([
                    'title' => $work['title'],
                    'description' => $work['description']
                ]);

                foreach ($work['images'] as $image) {
                    $this->upload_file_media($previous_work, $image['image'], 'previous_work_images');
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Merchant/SellerCenter/ServiceCreate ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'Service created successfully.');
        return $this->redirect(route('merchant.seller-center.services.index', $this->merchant));
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.services.merchant-seller-center-services-create');
    }
}
