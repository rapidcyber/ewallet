<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShareBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'entity_id',
        'entity_type',
        'is_payable',
        'note',
    ];

    protected $hidden = [
        'bill_id',
        'entity_id',
        'entity_type',
        'created_at',
        'updated_at',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
