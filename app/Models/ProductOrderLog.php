<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_order_id',
        'shipping_status_id',
        'title',
        'description'
    ];

    public function product_order()
    {
        return $this->belongsTo(ProductOrder::class, 'product_order_id');
    }

    public function shipping_status()
    {
        return $this->belongsTo(ShippingStatus::class, 'shipping_status_id');
    }
}
