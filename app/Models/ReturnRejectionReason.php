<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRejectionReason extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function return_rejections()
    {
        return $this->hasMany(ReturnRejection::class, 'reason_id', 'id');
    }
}
