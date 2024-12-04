<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UpdateProductMediaRequest;
use App\Http\Requests\Media\UpdateServiceMediaRequest;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    use WithEntity, WithHttpResponses, WithImageUploading, WithImage;

    /**
     * Summary of delete_product_media
     * 
     * @param \App\Http\Requests\Media\UpdateProductMediaRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update_product_media(UpdateProductMediaRequest $request)
    {
        $validated = $request->validated();

        $sku = $validated['sku'];
        $account_number = $validated['merc_ac'];

        $ids_to_delete = $validated['delete'] ?? [];
        $files_to_save = $validated['upload'] ?? [];

        $merchant = $this->get(auth()->user(), $account_number);
        if (empty($merchant)) {
            return $this->error('Invalid merchant account number', 499);
        }

        $product = $merchant->owned_products()->where('sku', $sku)->first();
        if (empty($product)) {
            return $this->error('Invalid product', 499);
        }

        DB::beginTransaction();
        try {
            if (empty($ids_to_delete) == false) {
                $medias = $product->getMedia('product_images')->whereIn('id', $ids_to_delete);
                foreach ($medias as $media) {
                    $media->delete();
                }
            }

            /// Re-order indexes
            $media_ids = $product->getMedia('product_images')->pluck('id')->toArray();
            Media::setNewOrder($media_ids);

            $new_media = [];
            foreach ($files_to_save as $file) {
                $media = $this->upload_file_media($product, $file, 'product_images');
                $media->file_name = bin2hex(random_bytes(4)) . '.' . $file->getClientOriginalExtension();
                $media->save();
                array_push($new_media, $this->get_media_details($media));
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }

        return $this->success($new_media);
    }

    /**
     * 
     * 
     * @param \App\Http\Requests\Media\UpdateServiceMediaRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function update_service_media(UpdateServiceMediaRequest $request)
    {
        $validated = $request->validated();

        $account_number = $validated['merc_ac'];
        $service_id = $validated['service_id'];

        $ids_to_delete = $validated['delete'] ?? null;
        $files_to_save = $validated['upload'] ?? null;

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
            if (empty($ids_to_delete) == false) {
                $medias = $service->getMedia('service_images')->whereIn('id', $ids_to_delete);
                foreach ($medias as $media) {
                    $media->delete();
                }
            }

            /// Re-order indexes
            $media_ids = $service->getMedia('service_images')->pluck('id')->toArray();
            Media::setNewOrder($media_ids);

            $new_media = [];
            foreach ($files_to_save as $file) {
                $media = $this->upload_file_media($service, $file, 'service_images');
                $media->file_name = bin2hex(random_bytes(4)) . '.' . $file->getClientOriginalExtension();
                $media->save();
                array_push($new_media, $this->get_media_details($media));
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            $this->exception($ex);
        }

        return $this->success($new_media);
    }
}
