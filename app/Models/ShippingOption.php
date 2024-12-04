<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'shipping_days',
        'price',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function merchants()
    {
        return $this->belongsToMany(Merchant::class, 'merchant_shipping_option', 'shipping_option_id', 'merchant_id');
    }
}
