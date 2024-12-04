<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillField extends Model
{
    use HasFactory;


    protected $fillable = [
        'bill_id',
        'tag',
        'caption',
        'format',
        'max',
        'value',
    ];

    protected $hidden = [
        'bill_id',
        'created_at',
        'updated_at',
    ];


    public function bill(): BelongsTo {
        return $this->belongsTo(Bill::class);
    }
}
