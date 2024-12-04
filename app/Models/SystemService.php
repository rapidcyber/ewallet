<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemService extends Model
{
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'name', 
        'slug',
        'availability',
    ];
}
