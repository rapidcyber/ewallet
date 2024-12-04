<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingStatus extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function parent_status()
    {
        return $this->belongsTo(ShippingStatus::class, 'parent');
    }
    
    public function sub_statuses()
    {
        return $this->hasMany(ShippingStatus::class, 'parent');
    }

    public function isToShip()
    {
        return $this->parent_status?->name == 'To Ship';
    }
}
