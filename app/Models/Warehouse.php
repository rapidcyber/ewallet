<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Warehouse extends Model
{
    use HasFactory;

    protected $hidden = [
        'merchant_id',
        'created_at',
        'updated_at',
        'pivot',
    ];
    protected $fillable = [
        'merchant_id',
        'name',
        'phone_number',
        'email_address'
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function location(): MorphOne
    {
        return $this->morphOne(Location::class, 'entity');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('stocks')->withTimestamps();
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(WarehouseAvailability::class);
    }
}
