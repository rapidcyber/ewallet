<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProductOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'buyer_id',
        'buyer_type',
        'amount',
        'quantity',
        'shipping_fee',
        'order_number',
        'warehouse_id',
        'shipping_option_id',
        'payment_option_id',
        'shipping_status_id',
        'shipped_at',
        'tracking_number',
        'delivery_type',
    ];

    protected $hidden = [
        'product_id',
        'buyer_id',
        'buyer_type',
        'shipping_option_id',
        'termination_reason',
        'payment_option_id',
        'shipping_status_id',
        'laravel_through_key',
        'processed_at',
        'updated_at',
    ];

    public function buyer(): MorphTo
    {
        return $this->morphTo();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shipping_status(): BelongsTo
    {
        return $this->belongsTo(ShippingStatus::class, 'shipping_status_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function shipping_option(): BelongsTo
    {
        return $this->belongsTo(ShippingOption::class, 'shipping_option_id');
    }

    public function payment_option(): BelongsTo
    {
        return $this->belongsTo(PaymentOption::class, 'payment_option_id');
    }

    public function documents(): HasOne
    {
        return $this->hasOne(ProductOrderDocument::class, 'product_order_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProductOrderLog::class)->orderBy('created_at', 'desc');
    }

    public function return_orders(): HasMany
    {
        return $this->hasMany(ReturnOrder::class);
    }

    public function location(): MorphOne
    {
        return $this->morphOne(Location::class, 'entity');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'ref_no', 'order_number');
    }

    public function cancellation()
    {
        return $this->hasOne(OrderCancel::class);
    }
}
