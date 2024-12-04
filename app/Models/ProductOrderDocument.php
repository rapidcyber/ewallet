<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOrderDocument extends Model
{
    use HasFactory;

    public function product_order(): BelongsTo
    {
        return $this->belongsTo(ProductOrder::class, 'product_order_id');
    }
}
