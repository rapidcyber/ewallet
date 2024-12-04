<?php

namespace App\Merchant\SellerCenter\Services;

use App\Models\Merchant;
use App\Models\PreviousWork;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MerchantSellerCenterServicesEdit extends Component
{
    use WithFileUploads, WithImageUploading, WithImage;

    public Merchant $merchant;
    public Service $service;
    public $service_images = [];
    #[Locked]
    public $delete_images = [];
    public $service_name;
    public $category;
    public $service_description;
    public $service_days = [];
    public $service_form = [];
    public $latitude, $longitude, $location;
    public $previous_works = [];
    public $previous_work_images = [];
    public $previous_work_title;
    public $previous_work_description;
    public $delete_service_questions = [];
    public $showPreviousWorkModal = false;

    #[Locked]
    public $edit_previous_work_index;

    #[Locked]
    public $delete_previous_works = [];

    public function mount(Merchant $merchant, Service $service)
    {
        $this->merchant = $merchant;
        $this->service = $service->load([
            'previous_works.media', 'form_questions.choices', 'media'
        ]);

        $this->service_name = $service->name;
        $this->category = $service->service_category_id;
        $this->service_description = $service->description;
        $service_days = $service->service_days;

        $location = $service->location;
        $this->latitude = $location->latitude;
        $this->longitude = $location->longitude;
        $this->location = $location->address;

        $defaultDays = $this->getDefaultDays();
        foreach ($defaultDays as $key => $defaultDay) {
            $dayName = strtolower($defaultDay['dayName']);
            if (isset($service_days[$dayName])) {
                $defaultDays[$key]['checked'] = true;
                $defaultDays[$key]['timeslots'] = array_map(function ($timeslot) use ($service_days) {
                    return [
                        'start_time' => [
                            'option' => $this->getTimeOptions(),
                            'selected' => Carbon::parse($timeslot['start_time'])->format('h:i A')
                        ],
                        'end_time' => [
                            'option' => $this->getTimeOptions(),
                            'selected' => Carbon::parse($timeslot['end_time'])->format('h:i A')
                        ]
                    ];
                }, $service_days[$dayName]);
            }
        }

        $this->service_days = $defaultDays;

        $form_questions = $this->service->form_questions;
        foreach ($form_questions as $key => $form_question) {
            $this->service_form[] = [
                'id' => $form_question->id,
                'question' => $form_question->question,
                'type' => $form_question->type,
                'important' => $form_question->is_important,
                'choices' => $form_question->choices->pluck('value')->toArray(),
                'order' => $form_question->order_column
            ];
        }

        $service_images = $service->getMedia('service_images');
        if ($service_images->isNotEmpty()) {
            foreach ($service_images as $key => $image) {
                $this->service_images[] = [
                    'id' => $image->id,
                    'name' => $image->file_name,
                    'image' => $this->get_media_url($image),
                    'size' => $image->human_readable_size,
                    'order' => $key
                ];
            }
        }

        $previous_works = $service->previous_works;
        if ($previous_works->isNotEmpty()) {
            foreach ($previous_works as $key => $previous_work) {
                $medias = $previous_work->getMedia('previous_work_images');
                $images = [];

                foreach ($medias as $mediaKey => $media) {
                    $images[] = [
                        'id' => $media->id,
                        'name' => $media->file_name,
                        'image' => $this->get_media_url($media),
                        'size' => $media->human_readable_size,
                        'order' => $mediaKey
                    ];
                }

                $this->previous_works[] = [
                    'id' => $previous_work->id,
                    'title' => $previous_work->title,
                    'description' => $previous_work->description,
                    'order' => $key,
                    'images' => $images
                ];
            }
        }

        $this->initMapLatLng();
    }

    #[On('updateServiceImages')]
    public function updateServiceImages($images)
    {
        $this->service_images = $images;

        foreach($this->service_images as $key => $image) {
            $this->service_images[$key]['image'] = $this->service_images[$key]['id'] ? $this->service_images[$key]['image'] : new TemporaryUploadedFile($image['image'], config('filesystems.default'));
        }
    }

    #[On('updateDeletedImages')]
    public function updateDeletedImages($images)
    {
        $this->delete_images = $images;
    }

    private function getDefaultDays()
    {
        return [
            [
                'id' => 1,
                'dayName' => 'Monday',
                'checked' => false,
                'timeslots' => []
            ],
            [
                'id' => 2,
                'dayName' => 'Tuesday',
                'checked' => false,
                'timeslots' => []
            ],
            [
                'id' => 3,
                'dayName' => 'Wednesday',
                'checked' => false,
                'timeslots' => []
            ],
            [
                'id' => 4,
                'dayName' => 'Thursday',
                'checked' => false,
                'timeslots' => []
            ],
            [
                'id' => 5,
                'dayName' => 'Friday',
                'checked' => false,
                'timeslots' => []
            ],
            [
                'id' => 6,
                'dayName' => 'Saturday',
                'checked' => false,
                'timeslots' => []
            ],
            [
                'id' => 7,
                'dayName' => 'Sunday',
                'checked' => false,
                'timeslots' => []
            ],
        ];
    }

    private function getTimeOptions()
    {
        return [
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
        ];
    }

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

    #[On('initMap')]
    public function initMapLatLng()
    {
        $data = $this->fetchMap([$this->latitude, $this->longitude]);

        if ($data['status'] === 'OK') {
            $mapLatlng = [
                'lat' => $data['results'][0]['geometry']['location']['lat'],
                'lng' => $data['results'][0]['geometry']['location']['lng'],
            ];
        } else {
            $mapLatlng = 'Location not found';
        }

        $this->dispatch('updateMap', $mapLatlng);
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

    #[Computed]
    public function categories()
    {
        return ServiceCategory::whereNull('parent')
            ->with(['sub_categories' => function ($query) {
                $query->orderBy('name');
            }])
            ->orderBy('name')
            ->get();
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
            if ($this->service_form[$index]['id'] !== null) {
                $this->delete_service_form[] = $this->service_form[$index];
            }

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

    public function deletePreviousWork($index)
    {
        if ($this->previous_works[$index]['id'] !== null) {
            $this->delete_previous_works[] = $this->previous_works[$index];
        }
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

        foreach($this->previous_work_images as $key => $image) {
            $this->previous_work_images[$key]['image'] = $this->previous_work_images[$key]['id'] ? $this->previous_work_images[$key]['image'] : new TemporaryUploadedFile($image['image'], config('filesystems.default'));
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
        foreach($this->service_days as $key => $day) {
            if (!$day['checked']) {
                continue;
            }
            
            $dayKey = strtolower($day['dayName']);
            foreach($day['timeslots'] as $timeslot) {
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
                'service_images.*.id' => 'nullable|exists:media,id',
                'service_images.*.name' => 'required',
                // 'service_images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
                'service_images.*.size' => 'required',
                'service_images.*.order' => 'required',
    
                'service_name' => 'required|string|max:120',
                'category' => ['required', function ($attribute, $value, $fail) {
                    if (!ServiceCategory::whereNotNull('parent')->where('id', $value)->exists()) {
                        $fail('The selected category is invalid.');
                    }
                }],
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
                'service_days.*.checked' => 'boolean',
                'service_days.*.timeslots' => 'array',
                'service_days.*.timeslots.*' => 'array:start_time,end_time',
                'service_days.*.timeslots.*.start_time' => 'nullable|date_format:h:i A',
                'service_days.*.timeslots.*.end_time' => 'nullable|date_format:h:i A|after:service_days.*.timeslots.*.start_time',
    
                'service_form' => 'array|min:1',
                'service_form.*.question' => 'required|string|max:255',
                'service_form.*.type' => 'required|in:paragraph,multiple,checkbox,dropdown',
                'service_form.*.important' => 'boolean',
                'service_form.*.choices' => 'array',
                'service_form.*.choices.*' => 'string|max:255',
                'service_form.*.order' => 'required',
    
                'location' => 'required|string|max:255',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
    
                'previous_works' => 'array',
                'previous_works.*.images' => 'array|min:1|max:5',
                'previous_works.*.images.*' => 'array:name,image,size,id,order',
                'previous_works.*.images.*.name' => 'required',
                // 'previous_works.*.images.*.image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
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

        if (!empty($check_service_form) || !empty($check_service_days)) {
            return;
        }

        $service_days = $this->getServiceDays();

        $this->service->name = $this->service_name;
        $this->service->service_category_id = $this->category;
        $this->service->description = $this->service_description;
        $this->service->service_days = $service_days;

        $location = $this->service->location;
        $location->address = $this->location;
        $location->latitude = $this->latitude;
        $location->longitude = $this->longitude;

        DB::beginTransaction();
        try {
            $this->service->save();
            $location->save();

            foreach ($this->delete_images as $delete_image) {
                if ($delete_image['id'] !== null) {
                    $media = Media::find($delete_image['id']);

                    $media->delete();
                }
            }

            $image_ids = [];
            foreach ($this->service_images as $service_image) {
                if ($service_image['id'] !== null) {
                    $image_ids[] = $service_image['id'];
                } else {
                    $image = $this->upload_file_media($this->service, $service_image['image'], 'service_images');
                    $image_ids[] = $image->id;
                }
            }

            Media::setNewOrder($image_ids);

            foreach ($this->delete_service_questions as $question_id) {
                $question = Question::whereHasMorph('entity', [Service::class], function ($q) {
                    $q->where('entity_id', $this->service->id);
                })->find($question_id);

                $question->delete();
            }

            foreach ($this->service_form as $key => $service_form) {
                if (isset($service_form['id'])) {
                    $question = Question::with('choices')->whereHasMorph('entity', [Service::class], function ($query) {
                        $query->where('entity_id', $this->service->id);
                    })->find($service_form['id']);

                    $question->question = $service_form['question'];
                    $question->type = $service_form['type'];
                    $question->is_important = $service_form['important'];
                    $question->order_column = $service_form['order'];
                    $question->save();
        
                    $choices = $question->choices;
        
                    foreach ($choices as $choice) {
                        if ($question->type == 'paragraph' or !in_array($choice->value, $service_form['choices'])) {
                            $choice->delete();
                        }
                    }
                } else {
                    $question = new Question;
                    $question->entity_id = $this->service->id;
                    $question->entity_type = Service::class;
                    $question->question = $service_form['question'];
                    $question->type = $service_form['type'];
                    $question->is_important = $service_form['important'];
                    $question->order_column = $service_form['order'];

                    $question->save();
                }

                if ($service_form['type'] !== 'paragraph') {
                    foreach ($service_form['choices'] as $choice) {
                        QuestionChoice::firstOrCreate(['question_id' => $question->id, 'value' => $choice]);
                    }
                }
            }

            foreach ($this->delete_previous_works as $delete_previous_work) {
                if ($delete_previous_work['id'] !== null) {
                    $previous_work = PreviousWork::where('service_id', $this->service->id)->find($delete_previous_work['id']);
                    $previous_work->delete();
                }
            }

            foreach ($this->previous_works as $previous_work_data) {
                if ($previous_work_data['id'] !== null) {
                    $previous_work = PreviousWork::where('service_id', $this->service->id)->find($previous_work_data['id']);
                } else {
                    $previous_work = new PreviousWork;
                    $previous_work->service_id = $this->service->id;
                }

                $previous_work->title = $previous_work_data['title'];
                $previous_work->description = $previous_work_data['description'];
                $previous_work->save();

                $previous_work_images = [];
                foreach ($previous_work_data['images'] as $image) {
                    if ($image['id'] === null) {
                        $previous_work_image = $this->upload_file_media($previous_work, $image['image'], 'previous_work_images');
                        $previous_work_images[] = $previous_work_image->id;
                    } else {
                        $previous_work_images[] = $image['id'];
                    }
                    
                }

                Media::setNewOrder($previous_work_images);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('MerchantSellerCenterServicesEdit - save - ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'Service updated successfully.');
        return $this->redirect(route('merchant.seller-center.services.show.edit', ['merchant' => $this->merchant, 'service' => $this->service]));
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.services.merchant-seller-center-services-edit');
    }
}
