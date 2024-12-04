<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsRecords extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient',
        'context',
        'env',
        'message_id',
        'status',
    ];
}
