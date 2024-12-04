<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnOrderLog extends Model
{
    use HasFactory;

    public function return_order()
    {
        return $this->belongsTo(ReturnOrder::class);
    }

    public function status()
    {
        return $this->belongsTo(ReturnOrderStatus::class, 'return_order_status_id');
    }
}
