<?php

namespace App\Models;

use App\Traits\WithImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PreviousWork extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, WithImage;

    protected $fillable = [
        'service_id',
        'title',
        'description'
    ];

    protected $hidden = [
        'media',
        'created_at',
        'updated_at',
        'service_id',
    ];

    protected $appends = [
        'image',
        'thumbnail',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Max, 250, 250)
            ->optimize()
            ->nonQueued();
    }

    public function getImageAttribute()
    {
        return $this->get_media_url($this->getFirstMedia('previous_work_images'));
    }
    public function getThumbnailAttribute()
    {
        return $this->get_media_url($this->getFirstMedia('previous_work_images'), 'thumbnail');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'entity');
    }
}
