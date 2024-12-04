<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'extras' => 'array'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function recipient()
    {
        return $this->morphTo();
    }

    public function provider()
    {
        return $this->belongsTo(TransactionProvider::class, 'transaction_provider_id');
    }

    public function channel()
    {
        return $this->belongsTo(TransactionChannel::class, 'transaction_channel_id');
    }

    public function type()
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    public function processor()
    {
        return $this->belongsTo(Employee::class, 'processed_by');
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
