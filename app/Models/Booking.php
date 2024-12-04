<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class  Booking extends Model implements HasMedia
{
    use HasFactory, HasRelationships, InteractsWithMedia;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'service_id',
        'slots',
        'service_date',
        'message',
        'invoice_id',
        'booking_status_id',
    ];

    protected $hidden = [
        'entity_id',
        'entity_type',
        'updated_at',
        'invoice_id',
        'service_id',
        'booking_status_id',
        'laravel_through_key',
    ];

    protected $casts =  [
        'slots' => 'array',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Max, 250, 250)
            ->optimize()
            ->nonQueued();
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class, 'booking_status_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function location(): MorphOne
    {
        return $this->morphOne(Location::class, 'entity');
    }

    public function transactions()
    {
        return $this->hasManyDeepFromRelations($this->invoice(), (new Invoice())->transactions());
    }

    public function form_answers()
    {
        return $this->hasMany(BookingAnswer::class);
    }
}
