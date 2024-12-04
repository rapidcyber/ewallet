<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnReason extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function return_orders()
    {
        return $this->hasMany(ReturnOrder::class);
    }
}
