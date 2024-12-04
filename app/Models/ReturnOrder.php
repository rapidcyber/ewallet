<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ReturnOrder extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Max, 250, 250)
            ->optimize()
            ->nonQueued();
    }

    public function reason()
    {
        return $this->belongsTo(ReturnReason::class, 'return_reason_id');
    }

    public function status()
    {
        return $this->belongsTo(ReturnOrderStatus::class, 'return_order_status_id');
    }

    public function product_order()
    {
        return $this->belongsTo(ProductOrder::class);
    }

    public function merchant_owner()
    {
        return $this->hasOneThrough(Merchant::class, Product::class, 'id', 'id', 'product_order_id', 'merchant_id');
    }

    public function dispute()
    {
        return $this->hasOne(ReturnOrderDispute::class);
    }

    public function logs()
    {
        return $this->hasMany(ReturnOrderLog::class)->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    public function rejection()
    {
        return $this->hasOne(ReturnRejection::class);
    }

    public function cancellation()
    {
        return $this->hasOne(ReturnCancel::class);
    }
}
