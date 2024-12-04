<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\ServiceActiveCategoriesRequest;
use App\Http\Requests\Service\ServiceDetailsRequest;
use App\Http\Requests\Service\ServiceEnlistRequest;
use App\Http\Requests\Service\ServiceListRequest;
use App\Http\Requests\Service\ServiceOwnedRequest;
use App\Http\Requests\Service\ServiceUpdateInfoRequest;
use App\Http\Requests\Service\UpdateInquiryFormRequest;
use App\Traits\WithImageUploading;
use Illuminate\Support\Facades\DB;
use App\Traits\WithHttpResponses;
use App\Models\ServiceCategory;
use App\Models\QuestionChoice;
use App\Traits\WithEntity;
use App\Traits\WithImage;
use App\Models\Location;
use App\Models\Merchant;
use App\Models\Question;
use App\Models\Service;
use Exception;

class ServiceController extends Controller
{
    use WithHttpResponses, WithEntity, WithImage, WithImageUploading;

    /**
     * Get list of service.
     *  - all or by merchant (if account_number is provided)
     * 
     * @param \App\Http\Requests\Service\ServiceListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function list(ServiceListRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;
        $category = $validated['category'] ?? null;
        $featured = $validated['featured'] ?? null;
        $service_days = $validated['service_days'] ?? null;
        $latitude = $validated['latitude'] ?? null;
        $longitude = $validated['longitude'] ?? null;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }


        $query = Service::select(
            'services.id',
            'name',
            'service_category_id',
            'merchant_id',
            'is_featured'
        );

        if (empty($latitude) == false and empty($longitude) == false) {
            $query = $query
                ->join('locations', function ($q) {
                    $q->on('locations.entity_id', '=', 'services.id')
                        ->where('locations.entity_type', '=', Service::class);
                })
                ->selectRaw('ST_DISTANCE_SPHERE(
                    POINT(?,?),
                    POINT(longitude,latitude)
                ) AS distance', [$longitude, $latitude])
                ->orderBy('distance');
        }

        $query = $query->where([
            'approval_status' => 'approved',
            'is_active' => 1,
        ])->withAvg('reviews as rating', 'rating')
            ->withCount('reviews as reviews')
            ->with([
                'category',
                'location' => function ($q) use ($longitude, $latitude) {
                    /// ST_DISTANCE_SPHERE returns meters
                    /// aliased as `distance`
                    $q->selectRaw('*, (ST_DISTANCE_SPHERE(
                        POINT(?,?),
                        POINT(longitude,latitude)
                    )) AS distance', [$longitude, $latitude]);
                },
                'merchant:id,name,account_number',
                'category' => function ($q) {
                    $q->select('id', 'name', 'slug');
                }
            ]);


        if (!empty($service_days)) {
            $query = $query->where(function ($q) use ($service_days) {
                foreach ($service_days as $day) {
                    $q->orWhereJsonContainsKey('service_days->' . $day);
                }
            });
        }

        if (!empty($featured)) {
            $query = $query->where('is_featured', $featured);
        }

        if (!empty($category)) {
            $query = $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        if (empty($validated['account_number']) == false) {
            $query = $query->whereHas('merchant', function ($q) use ($validated) {
                $q->where('account_number', $validated['account_number']);
            });
        }

        $services = $query->paginate(
            $per_page,
            ['*'],
            'services',
            $page
        );

        foreach ($services->items() as $service) {
            $service->is_mine = $this->is_mine($service, $entity);
            $this->add_model_images($service, 'service_images', true);
        }

        return $this->success([
            'services' => $services->items(),
            'last_page' => $services->lastPage(),
            'total_item' => $services->total(),
        ]);
    }

    /**
     * Summary of owned
     * @param \App\Http\Requests\Service\ServiceOwnedRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function owned(ServiceOwnedRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $query = null;
        if (get_class($entity) == Merchant::class) {
            $query = $entity->owned_services();
        } else {
            $query = Service::whereHas('merchant', function ($q) use ($entity) {
                $q->where('user_id', $entity->id);
            })->where([
                        'approval_status' => 'approved',
                        'is_active' => true,
                    ]);
        }

        $services = $query->select(
            'id',
            'name',
            'service_category_id',
            'merchant_id',
            'is_featured',
            'is_active',
            'approval_status as status'
        )
            ->withAvg('reviews as rating', 'rating')
            ->withCount('reviews as reviews')
            ->with([
                'location',
                'merchant:id,name,account_number',
                'category' => function ($q) {
                    $q->select('id', 'name', 'slug');
                }
            ])
            ->paginate(
                $per_page,
                ['*'],
                'services',
                $page
            );

        foreach ($services->items() as $service) {
            $service->is_mine = $this->is_mine($service, $entity);
            $this->add_model_images($service, 'service_images', true);
        }

        return $this->success([
            'services' => $services->items(),
            'last_page' => $services->lastPage(),
            'total_item' => $services->total(),
        ]);
    }

    /**
     * Get list of service categories.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function categories()
    {
        $categories = ServiceCategory::where('parent', null)
            ->with(['sub_categories'])
            ->get();

        return $this->success($categories);
    }

    /**
     * Summary of enlist
     * 
     * @param \App\Http\Requests\Service\ServiceEnlistRequest $request
     * @return void
     */
    public function enlist(ServiceEnlistRequest $request)
    {
        $validated = $request->validated();
        $files = $validated['files'] ?? [];

        $merchant = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($merchant)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $service = new Service;
        $service->fill([
            'merchant_id' => $merchant->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'service_category_id' => ServiceCategory::where('slug', $validated['category'])->first()->id,
            'service_days' => $validated['service_days'],
        ]);

        $location = new Location;
        $location->fill($validated['location']);

        $questions = $validated['inquiry_form'];

        try {
            DB::transaction(function () use ($service, $location, $questions, $files) {
                $service->save();

                foreach ($questions as $key => $q_item) {
                    $question = new Question;
                    $question->fill([
                        'type' => $q_item['type'],
                        'question' => $q_item['question'],
                        'is_important' => $q_item['important'],
                        'entity_type' => get_class($service),
                        'entity_id' => $service->id,
                        'order_column' => $key,
                    ]);
                    $question->save();

                    if (in_array($question->type, ['multiple', 'dropdown', 'checkbox'])) {
                        $choices = $q_item['choices'];
                        foreach ($choices as $c_item) {
                            $choice = new QuestionChoice;
                            $choice->fill([
                                'question_id' => $question->id,
                                'value' => $c_item,
                            ]);
                            $choice->save();
                        }
                    }
                }

                foreach ($files as $key => $file) {
                    $media = $this->upload_file_media($service, $file, 'service_images');
                    $media->order_column = $key;
                    $media->save();
                }

                $location->entity_id = $service->id;
                $location->entity_type = get_class($service);
                $location->save();
            });

            $service->is_mine = true;
            $service->rating = 0;
            $service->reviews = 0;
            $service->sold_count = 0;
            $this->add_model_images($service, 'service_images');
            $service->load(['location', 'merchant:id,name,account_number', 'category']);
            return $this->success($service);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }


    /**
     * Summary of update_info
     * @param \App\Http\Requests\Service\ServiceUpdateInfoRequest $request
     * @return void
     */
    public function update_info(ServiceUpdateInfoRequest $request)
    {
        $validated = $request->validated();

        $account_number = $validated['merc_ac'];
        $service_id = $validated['service_id'];

        $name = $validated['name'] ?? null;
        $description = $validated['description'] ?? null;
        $category_slug = $validated['category'] ?? null;
        $service_days = $validated['service_days'] ?? null;
        $location = $validated['location'] ?? null;

        $merchant = $this->get(auth()->user(), $account_number);
        if (empty($merchant)) {
            return $this->error('Invalid merchant account number', 499);
        }

        $service = $merchant->owned_services()->where('id', $service_id)->first();
        if (empty($service)) {
            return $this->error('Invalid service ID', 499);
        }


        if (empty($category_slug) == false) {
            $new_category = ServiceCategory::where('slug', $category_slug)->first();
        }

        $service->fill([
            'name' => empty($name) ? $service->name : $name,
            'description' => empty($description) ? $service->description : $description,
            'service_days' => empty($service_days) ? $service->service_days : $service_days,
        ]);

        DB::beginTransaction();
        try {
            if (empty($location) == false) {
                $service->location->fill($location);
                $service->location->save();
            }

            if (empty($new_category) == false and $service->service_category_id != $new_category->id) {
                $service->service_category_id = $new_category->id;
            }

            $service->save();
            DB::commit();

            $service->load([
                'location',
                'category.parent_category',
            ]);
            return $this->success($service);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of update_inquiry_form
     * @param \App\Http\Requests\Service\UpdateInquiryFormRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update_inquiry_form(UpdateInquiryFormRequest $request)
    {
        $validated = $request->validated();

        $account_number = $validated['merc_ac'];
        $service_id = $validated['service_id'];

        $delete = $validated['delete'] ?? null;
        $update = $validated['update'] ?? null; // Array of existing question
        $add = $validated['add'] ?? null;

        $merchant = $this->get(auth()->user(), $account_number);
        if (empty($merchant)) {
            return $this->error('Invalid merchant account number', 499);
        }

        $service = $merchant->owned_services()->where('id', $service_id)->first();
        if (empty($service)) {
            return $this->error('Invalid service ID', 499);
        }

        DB::beginTransaction();
        try {
            /// DO DELETE
            if (empty($delete) == false) {
                $service->form_questions()->whereIn('id', $delete)->delete();

                /// DO QUESTION INDEX (order_column) RE-ORDERING
                Question::setNewOrder($service->form_questions()->pluck('id')->toArray());
            }

            /// DO UPDATE
            if (empty($update) == false) {
                foreach ($update as $question) {
                    $existing_question = $service->form_questions()->find($question['id']);
                    /// Do nothing if it doesn't exists.
                    if (empty($existing_question)) {
                        continue;
                    }
                    /// Update question information
                    $existing_question->fill([
                        'type' => empty($question['type']) ? $existing_question->type : $question['type'],
                        'question' => empty($question['question']) ? $existing_question->question : $question['question'],
                        'is_important' => is_bool($question['important']) == false ? $existing_question->is_important : $question['important'],
                    ]);
                    $existing_question->save();

                    if ($existing_question->type == 'paragraph') {
                        /// Delete all choices if type is paragraph
                        $existing_question->choices()->delete();
                    } else {
                        /// Delete choices that are not in the choices update array request.
                        $existing_question->choices()->whereNotIn('value', $question['choices'])->delete();
                        foreach ($question['choices'] as $choice) {
                            QuestionChoice::firstOrCreate([
                                'question_id' => $existing_question->id,
                                'value' => $choice,
                            ]);
                        }
                    }
                }
            }

            /// DO ADD
            if (empty($add) == false) {
                $count = $service->form_questions()->count() + 1; /// get count of questions
                foreach ($add as $key => $question) {
                    $new_question = Question::firstOrNew([
                        'entity_id' => $service->id,
                        'entity_type' => get_class($service),
                        'question' => $question['question'],
                        'type' => $question['type'],
                        'is_important' => $question['important']
                    ]);

                    if ($new_question->id) {
                        return $this->error("Duplicate question '" . $new_question->question . "'", 499);
                    }

                    $new_question->order_column = $count + $key;
                    $new_question->save();

                    /// Do choices only if given type is 'paragraph'
                    if (in_array($new_question->type, ['multiple', 'dropdown', 'checkbox'])) {
                        foreach ($question['choices'] as $choice) {
                            QuestionChoice::firstOrCreate([
                                'question_id' => $new_question->id,
                                'value' => $choice,
                            ]);
                        }
                    }

                    $new_question->load('choices');
                }
            }
            DB::commit();
            return $this->success($service->form_questions()->with('choices')->get());
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of details
     * 
     * @param \App\Http\Requests\Service\ServiceDetailsRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function details(ServiceDetailsRequest $request)
    {
        $validated = $request->validated();
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $service = Service::select('id', 'name', 'description', 'service_days', 'service_category_id', 'merchant_id', 'approval_status as status', 'is_active')
            ->withAvg('reviews as rating', 'rating')
            ->withCount('reviews as reviews')
            ->withCount('previous_works as previous_work_count')
            ->with([
                'location',
                'form_questions.choices',
                'category.parent_category',
                'merchant' => function ($q) {
                    $q->select('id', 'name', 'account_number', 'user_id');
                    $q->withAvg('reviews as rating', 'rating')->withCount('reviews as reviews');
                },
                'previous_works',
            ])->find($validated['id']);

        $service->is_mine = $this->is_mine($service, $entity);
        $this->add_model_images($service, 'service_images');
        return $this->success($service);
    }


    /**
     * List categories that are currently connected to the merchant's categories
     * 
     * @param \App\Http\Requests\Service\ServiceActiveCategoriesRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function active_categories(ServiceActiveCategoriesRequest $request)
    {
        $validated = $request->validated();
        $merchant = Merchant::where('account_number', $validated['merc_ac'])->first();

        $categories = ServiceCategory::where('parent', null)
            ->whereHas('sub_categories', function ($q) use ($merchant) {
                $q->whereHas('services', function ($q) use ($merchant) {
                    $q->where([
                        'is_active' => 1,
                        'merchant_id' => $merchant->id,
                        'approval_status' => 'approved',
                    ]);
                });
            })->with([
                    'sub_categories' => function ($q) use ($merchant) {
                        $q->whereHas('services', function ($q) use ($merchant) {
                            $q->where([
                                'is_active' => 1,
                                'merchant_id' => $merchant->id,
                                'approval_status' => 'approved',
                            ]);
                        });
                    }
                ])->get();

        return $this->success($categories);
    }
}
