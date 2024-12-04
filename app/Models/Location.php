<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Location extends Model
{
    use HasFactory;

    protected $hidden = [
        'id',
        'entity_id',
        'entity_type',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'entity_id',
        'entity_type',
        'address',
        'latitude',
        'longitude',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
