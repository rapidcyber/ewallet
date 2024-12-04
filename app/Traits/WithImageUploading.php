<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait WithImageUploading
{
    /**
     * Upload file to corresponding file system based on app environment.
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $collection
     * @return void
     */
    public function upload_file_media(Model $model, UploadedFile $file, string $collection = 'default'): Media
    {
        $filename = $file->getBasename() . '.' . $file->extension();

        try {
            $path = app()->environment(['local', 'staging']) ? 'public' : 's3';

            $media = $model->addMedia($file)->usingFileName($filename)->toMediaCollection($collection, $path);
        } catch (\Throwable $th) {
            dd($th);
        }

        return $media;
    }

    public function copy_file_media(HasMedia $model, Media $media, string $collection = 'default'): void
    {
        try {
            $path = app()->environment(['local', 'staging']) ? 'public' : 's3';

            $media->copy($model, $collection, $path);
        } catch (\Throwable $th) {
            dd($th);
        }
    }


    /**
     * Showing the file size of temporary uploaded files
     * in human readable format.
     * @param mixed $bytes
     * @return string
     */
    public function format_file_size($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}
