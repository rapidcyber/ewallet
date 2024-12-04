<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'ref_no',
        'receipt_email',
        'biller_code',
        'biller_name',
        'remind_date',
        'amount',
        'currency',
        'payment_date',
        'due_date',
        'service_charge',
    ];

    protected $hidden = [
        'entity_id',
        'entity_type',
        'updated_at',
        'deleted_at',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function shared_bills(): HasMany
    {
        return $this->hasMany(ShareBill::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(BillField::class);
    }
}
