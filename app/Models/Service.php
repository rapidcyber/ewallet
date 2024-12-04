<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Service extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $hidden = [
        'merchant_id',
        'service_category_id',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'name',
        'merchant_id',
        'description',
        'service_category_id',
        'service_days',
    ];

    protected static $inquiryStatus;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (is_null(self::$inquiryStatus)) {
            self::$inquiryStatus = BookingStatus::whereIn('slug', ['inquiry', 'quoted'])->pluck('id')->toArray();
        }
    }

    protected $casts = [
        'service_days' => 'array',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Max, 250, 250)
            ->optimize()
            ->nonQueued();
    }

    public function first_image()
    {
        return $this->morphOne(Media::class, 'model')
            ->where('collection_name', 'service_images')
            ->orderBy('order_column');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function previous_works(): HasMany
    {
        return $this->hasMany(PreviousWork::class);
    }

    public function scopeActive(Builder $query)
    {
        $query->where('is_active', true)->where('approval_status', 'approved');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'service_id')->whereNotIn('booking_status_id', self::$inquiryStatus);
    }

    public function inquiries()
    {
        return $this->hasMany(Booking::class, 'service_id')->whereIn('booking_status_id', self::$inquiryStatus);
    }

    public function location(): MorphOne
    {
        return $this->morphOne(Location::class, 'entity');
    }

    public function invoices_through_services()
    {
        return $this->hasManyThrough(Invoice::class, Booking::class);
    }

    public function form_questions()
    {
        return $this->morphMany(Question::class, 'entity')->orderBy('order_column');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(EntityReview::class, 'entity');
    }

    public function rating()
    {
        $reviewStats = $this->reviews()->selectRaw('SUM(rating) as total_rating, COUNT(*) as review_count')->first();

        if ($reviewStats->review_count > 0) {
            return $reviewStats->total_rating / $reviewStats->review_count;
        }

        return 0;

    }
}
