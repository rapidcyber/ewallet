<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCancelReason extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function order_cancels()
    {
        return $this->hasMany(OrderCancel::class);
    }
}
