<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'sender_id',
        'sender_type',
        'recipient_id',
        'recipient_type',
        'transaction_provider_id',
        'transaction_channel_id',
        'transaction_type_id',
        'transaction_status_id',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'sender_id',
        'sender_type',
        'recipient_id',
        'recipient_type',
        'txn_no',
        'ref_no',
        'transaction_provider_id',
        'transaction_channel_id',
        'transaction_type_id',
        'transaction_status_id',
        'service_fee',
        'currency',
        'amount',
        'extras',
    ];

    protected $casts = [
        'extras' => 'array'
    ];

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(TransactionChannel::class, 'transaction_channel_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(TransactionProvider::class, 'transaction_provider_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TransactionStatus::class, 'transaction_status_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    public function balance(): HasOne
    {
        return $this->hasOne(Balance::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(TransactionDispute::class);
    }
}
