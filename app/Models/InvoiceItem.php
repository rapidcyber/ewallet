<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $hidden = [
        'invoice_id',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'invoice_id',
        'name',
        'description',
        'price',
        'quantity'
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
