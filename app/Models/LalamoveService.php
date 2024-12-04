<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LalamoveService extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'scheduled_at',
        'expires_at',
        'price',
        'product_id',
        'quantity',
        'warehouse_id',
        'buyer_id',
        'buyer_type',
        'buyer_stop_id',
        'seller_stop_id'
    ];

    public function buyer(): MorphTo
    {
        return $this->morphTo();
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function lalamove_orders()
    {
        return $this->hasMany(LalamoveOrder::class);
    }

    public function buyer_location()
    {
        return $this->morphOne(Location::class, 'entity');
    }
}
