<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnOrderStatus extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function return_orders()
    {
        return $this->hasMany(ReturnOrder::class);
    }

    public function parent_status()
    {
        return $this->belongsTo(ReturnOrderStatus::class, 'parent');
    }

    public function children()
    {
        return $this->hasMany(ReturnOrderStatus::class, 'parent');
    }

    public function isCancellable()
    {
        return in_array($this->name, [
            'Return Initiated',
            'Rejected',
            'Pending Resolution',
            'Dispute In Progress',
            'Pending Response',
        ]);
    }

    public function isRejected()
    {
        if ($this->name === 'Rejected' || $this->parent_status?->name === 'Rejected') {
            return true;
        }

        return false;
    }
}
