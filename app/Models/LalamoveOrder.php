<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LalamoveOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'lalamove_service_id',
        'order_id',
        'share_link',
        'status',
    ];


    public function driver()
    {
        return $this->belongsTo(LalamoveDriver::class, 'lalamove_driver_id');
    }

    public function service()
    {
        return $this->belongsTo(LalamoveService::class, 'lalamove_service_id');
    }
}
