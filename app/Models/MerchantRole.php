<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MerchantRole extends Pivot
{
    protected $table = 'merchant_role';

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
