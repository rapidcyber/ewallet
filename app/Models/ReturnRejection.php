<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ReturnRejection extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Max, 250, 250)
            ->optimize()
            ->nonQueued();
    }

    public function return_order()
    {
        return $this->belongsTo(ReturnOrder::class);
    }

    public function reason()
    {
        return $this->belongsTo(ReturnRejectionReason::class, 'return_rejection_reason_id');
    }
}
