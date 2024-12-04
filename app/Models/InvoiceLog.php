<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'amount',
        'invoice_id',
    ];

    protected $hidden = [
        'id',
        'invoice_id',
        'transaction_id',
        'updated_at',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
