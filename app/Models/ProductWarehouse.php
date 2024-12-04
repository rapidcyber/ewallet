<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductWarehouse extends Pivot
{
    public function products()
    {
        $this->hasMany(Product::class);
    }

    public function warehouses()
    {
        $this->hasMany(Warehouse::class);
    }
}
