<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LalamoveBalance extends Model
{
    protected $fillable = [
        'currency',
        'amount'
    ];
}
