<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllbankWebhookData extends Model
{
    use HasFactory;

    protected $casts =[
        'data' => 'array'
    ];

    protected $fillable = [
        'type',
        'data',
        'env',
        'signature',
    ];
}
