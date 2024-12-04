<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkedRealholmesAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'control',
        'refresh',
        'token',
    ];

    protected $hidden = [
        'entity_id',
        'entity_type',
        'refresh',
        'control',
        // 'token',
        'created_at',
        'updated_at',
    ];
}
