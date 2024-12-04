<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QrGeneratedData extends Model
{
    use HasFactory;


    protected $fillable = [
        'client_id',
        'client_type',
        'ref_no',
        'merc_token',
        'type',
        'internal',
        'code',
    ];

    public function client(): MorphTo
    {
        return $this->morphTo();
    }
}
