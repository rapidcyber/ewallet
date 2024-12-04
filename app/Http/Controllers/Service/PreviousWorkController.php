<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\AddPreviousWorkRequest;
use App\Http\Requests\Service\DeletePreviousWorkRequest;
use App\Http\Requests\Service\GetPreviousWorkRequest;
use App\Http\Requests\Service\PreviousWorkDetailsRequest;
use App\Http\Requests\Service\UpdatePreviousWorkRequest;
use App\Models\PreviousWork;
use App\Models\Service;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PreviousWorkController extends Controller
{

    use WithEntity, WithHttpResponses, WithImageUploading, WithImage;

    /**
     * Summary of add_previous_work
     * @param \App\Http\Requests\Service\AddPreviousWorkRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function add_previous_work(AddPreviousWorkRequest $request)
    {
        $validated = $request->validated();

        $account_number = $validated['merc_ac'];
        $service_id = $validated['service_id'];
        $files = $validated['files'] ?? [];

        $merchant = $this->get(auth()->user(), $account_number);
        if (empty($merchant)) {
            return $this->error('Invalid merchant account number', 499);
        }

        $service = $merchant->owned_services()->where('id', $service_id)->first();
        if (empty($service)) {
            return $this->error('Invalid service ID', 499);
        }

        if ($service->previous_works()->count() >= 5) {
            return $this->error('Maximum previous work limit reached', 499);
        }

        DB::beginTransaction();
        try {
            $prev_work = new PreviousWork;
            $prev_work->fill([
                'service_id' => $service->id,
                'title' => $validated['title'] ?? '',
                'description' => $validated['description'] ?? '',
            ]);
            $prev_work->save();

            foreach ($files as $key => $file) {
                $media = $this->upload_file_media($prev_work, $file, 'previous_work_images');
                $media->order_column = $key;
                $media->save();
            }

            DB::commit();
            return $this->success($prev_work);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of update_previous_work
     * @param \App\Http\Requests\Service\UpdatePreviousWorkRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update_previous_work(UpdatePreviousWorkRequest $request)
    {
        $validated = $request->validated();

        $account_number = $validated['merc_ac'];
        $service_id = $validated['service_id'];
        $work_id = $validated['work_id'];

        $title = $validated['title'] ?? '';
        $description = $validated['description'] ?? '';

        $delete_media_id = $validated['delete'] ?? [];
        $add_media = $validated['files'] ?? [];

        $merchant = $this->get(auth()->user(), $account_number);
        if (empty($merchant)) {
            return $this->error('Invalid merchant account number', 499);
        }

        $service = $merchant->owned_services()->where('id', $service_id)->first();
        if (empty($service)) {
            return $this->error('Invalid service ID', 499);
        }

        $work = $service->previous_works()->where('id', $work_id)->first();
        if (empty($work)) {
            return $this->error('Invalid work ID', 499);
        }

        if (empty($add_media) == false) {
            $count = $work->getMedia('previous_work_images')->whereNotIn('id', $delete_media_id)->count();
            if ($count >= 5) {
                return $this->error('Maximum previous work images reached', 499);
            }
        }

        DB::beginTransaction();
        try {
            $work->fill([
                'title' => empty($title) ? $work->title : $title,
                'description' => empty($description) ? $work->description : $description,
            ]);
            $work->save();

            $medias = $work->getMedia('previous_work_images')->whereIn('id', $delete_media_id);
            foreach ($medias as $media) {
                $media->delete();
            }

            $media_ids = $work->getMedia('previous_work_images')->pluck('id')->toArray();
            Media::setNewOrder($media_ids);

            foreach ($add_media as $file) {
                $media = $this->upload_file_media($work, $file, 'previous_work_images');
                $media->save();
            }

            DB::commit();
            $work->load('media');
            $this->add_model_images($work, 'previous_work_images');
            return $this->success($work);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of delete_previous_work
     * @param \App\Http\Requests\Service\DeletePreviousWorkRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function delete_previous_work(DeletePreviousWorkRequest $request)
    {
        $validated = $request->validated();

        $account_number = $validated['merc_ac'];
        $service_id = $validated['service_id'];
        $work_id = $validated['work_id'];

        $merchant = $this->get(auth()->user(), $account_number);
        if (empty($merchant)) {
            return $this->error('Invalid merchant account number', 499);
        }

        $service = $merchant->owned_services()->where('id', $service_id)->first();
        if (empty($service)) {
            return $this->error('Invalid service ID', 499);
        }

        $work = $service->previous_works()->where('id', $work_id)->first();
        if (empty($work)) {
            /// if empty just return success.
            return $this->success();
        }

        DB::beginTransaction();
        try {
            $work->delete();
            DB::commit();
            return $this->success();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of get_previous_works
     * @param \App\Http\Requests\Service\GetPreviousWorkRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function get_previous_works(GetPreviousWorkRequest $request)
    {
        $validated = $request->validated();
        $service_id = $validated['service_id'];

        $service = Service::find($service_id);
        $prev_works = $service->previous_works()->get();
        return $this->success($prev_works);
    }

    /**
     * Summary of previous_work_details
     * @param \App\Http\Requests\Service\PreviousWorkDetailsRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function previous_work_details(PreviousWorkDetailsRequest $request)
    {
        $validated = $request->validated();
        $service_id = $validated['service_id'];
        $work_id = $validated['work_id'];

        $service = Service::find($service_id);
        $prev_work = $service->previous_works()->where('id', $work_id)->first();
        if (empty($prev_work)) {
            return $this->error('Invalid previous work id.', 499);
        }

        $prev_work->setAppends([]);
        $this->add_model_images($prev_work, 'previous_work_images');
        return $this->success($prev_work);
    }

}
