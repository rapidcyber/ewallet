<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchantCategory extends Model
{
    use HasFactory;

    protected $hidden = ['parent'];

    public $timestamps = false;

    public function merchants(): HasMany
    {
        return $this->hasMany(Merchant::class);
    }

    public function sub_categories(): HasMany
    {
        return $this->hasMany(MerchantCategory::class, 'parent');
    }

    public function parent_category(): BelongsTo
    {
        return $this->belongsTo(MerchantCategory::class, 'parent');
    }
}
