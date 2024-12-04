<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $hidden = [
        'product_category_id',
        'product_condition_id',
        'created_at',
        'updated_at',
        'merchant_id',
    ];

    protected $fillable = [
        'sku',
        'merchant_id',
        'product_category_id',
        'name',
        'description',
        'variations',
        'currency',
        'price',
        'sale_amount',
        'sold_count',
        'product_condition_id',
        'on_demand',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'variations' => 'array',
        'on_demand' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Max, 250, 250)
            ->optimize()
            ->nonQueued();
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function condition(): BelongsTo
    {
        return $this->belongsTo(ProductCondition::class, 'product_condition_id');
    }

    public function productDetail()
    {
        return $this->hasOne(ProductDetail::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(EntityReview::class, 'entity');
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class)->withPivot('stocks')->withTimestamps();
    }

    public function locations(): HasManyThrough {

        return $this->hasManyThrough(Location::class, ProductWarehouse::class, 'product_id', 'entity_id', 'id', 'warehouse_id')
            ->where('entity_type', Warehouse::class);
    }

    public function orders()
    {
        return $this->hasMany(ProductOrder::class);
    }

    public function getFullUrlMediaAttribute()
    {
        return $this->getMedia()->map(function ($mediaObject) {
            $mediaObject->full_url = $mediaObject->getFullUrl();

            return $mediaObject;
        });
    }

    public function first_image()
    {
        return $this->morphOne(Media::class, 'model')
            ->where('collection_name', 'product_images')
            ->orderBy('order_column');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->where('approval_status', 'approved');
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
