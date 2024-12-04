<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnCancelReason extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function return_cancels()
    {
        return $this->hasMany(ReturnCancel::class);
    }
}
