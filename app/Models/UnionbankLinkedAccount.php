<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UnionbankLinkedAccount extends Model
{

    protected $fillable = [
        'owner_id',
        'owner_type',
        'name',
        'scope',
        'access_token',
        'refresh_token',
    ];

    protected $hidden = [
        'owner_id',
        'owner_type',
        'access_token',
        'refresh_token',
        'created_at',
        'updated_at',
    ];

    use HasFactory;

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
