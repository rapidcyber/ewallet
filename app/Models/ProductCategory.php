<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'parent'];

    protected $hidden = ['parent'];

    public $timestamps = false;

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // public function parent(): BelongsTo
    // {
    //     return $this->belongsTo(ProductCategory::class, 'parent');
    // }

    public function sub_categories(): HasMany

    {
        return $this->hasMany(ProductCategory::class, 'parent');
    }

    public function parent_category()
    {
        return $this->belongsTo(ProductCategory::class, 'parent', 'id');
    }
}
