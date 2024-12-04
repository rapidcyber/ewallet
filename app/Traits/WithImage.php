<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait WithImage
{
    /**
     * Get media of the provided model
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $collection
     * @param bool $first
     * @return void
     */
    private function add_model_images(Model $model, string $collection = 'default', bool $first = false)
    {
        $images = [];

        $media_ids = $model->getMedia($collection)->pluck('id')->toArray();
        Media::setNewOrder($media_ids);

        if ($first == true) {
            $media = $model->getFirstMedia($collection);
            if (empty($media) == false) {
                $images[] = $this->get_media_details($media);
            }
        } else {
            $medias = $model->getMedia($collection);
            foreach ($medias as $media) {
                $images[] = $this->get_media_details($media);
            }
        }

        if (empty($model->images)) {
            $model->images = $images;
        } else {
            array_push($model->images, $images);
        }
        unset($model->media);
    }

    /**
     * Summary of get_model_image
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $collection
     * @return array|null
     */
    private function get_model_image(Model $model, string $collection)
    {
        $media = $model->getFirstMedia($collection);
        if (empty($media)) {
            return null;
        }
        return $this->get_media_details($media);
    }

    /**
     * Summary of get_media_details
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
     * @return array
     */
    private function get_media_details(Media $media)
    {
        return [
            'id' => $media->id,
            'uuid' => $media->uuid,
            'name' => $media->name,
            'url' => $this->get_media_url($media),
            'index' => $media->order_column,
            'thumbnail' => $this->get_media_url($media, 'thumbnail'),
        ];
    }

    private function get_media_url(Media $media, $conversion = '')
    {
        if ($media->disk == 's3') {
            return $media->getTemporaryUrl(Carbon::now()->addMinutes(5), $conversion);
        }

        return $media->getUrl($conversion);
    }

    /**
     * Summary of get_multiple_images
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $collections
     * @return void
     */
    private function get_multiple_images(Model $model, array $collections)
    {
        $images = [];
        foreach ($collections as $collection) {
            $medias = $model->getMedia($collection);
            foreach ($medias as $media) {
                $images[$collection] = $this->get_media_details($media);
            }
        }

        if (empty($model->images)) {
            $model->images = $images;
        } else {
            array_push($model->images, $images);
        }
        unset($model->media);
    }
}
