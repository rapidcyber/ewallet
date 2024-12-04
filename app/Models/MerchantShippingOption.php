<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MerchantShippingOption extends Pivot
{
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function shippingOption()
    {
        return $this->belongsTo(ShippingOption::class);
    }
}
