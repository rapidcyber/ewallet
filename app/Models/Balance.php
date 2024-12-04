<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Balance extends Model
{
    use HasFactory;

    protected $hidden = [
        'id',
        'entity_id',
        'entity_type',
        'transaction_id',
        'credits',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'id',
        'entity_id',
        'entity_type',
        'transaction_id',
        'currency',
        'amount',
        'credits',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
