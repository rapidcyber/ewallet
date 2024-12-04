<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Invoice extends Model
{
    use HasFactory;

    protected $hidden = [
        'sender_id',
        'sender_type',
        'recipient_id',
        'recipient_type',
        'updated_at',
    ];

    protected $fillable = [
        'sender_id',
        'sender_type',
        'merchant_id',
        'recipient_id',
        'recipient_type',
        'invoice_no',
        'currency',
        'message',
        'due_date',
        'status',
        'minimum_partial',
        'type',
    ];

    public function sender(): MorphTo
    {
        return $this->morphTo();
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    public function inclusions(): HasMany
    {
        return $this->hasMany(InvoiceInclusion::class);
    }

    public function surcharges()
    {
        return $this->inclusions()->where('deduct', 0);
    }

    public function deductions()
    {
        return $this->inclusions()->where('deduct', 1);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'ref_no', 'invoice_no');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(InvoiceLog::class);
    }

    public function getTotalPriceAttribute()
    {
        $total_invoice_item = $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $total_invoice_inclusion = $this->inclusions->sum(function ($inclusion) {
            return $inclusion->deduct === 0 ? $inclusion->amount : -$inclusion->amount;
        });

        return $total_invoice_item + $total_invoice_inclusion;
    }
}
