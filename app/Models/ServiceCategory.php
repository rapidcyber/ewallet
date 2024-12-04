<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use HasFactory;

    public $timestamps = false;

    // protected $hidden = ['parent'];
    protected $fillable = [
        'parent',
        'name',
        'slug'
    ];

    public function parent_category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'parent');
    }

    public function sub_categories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class, 'parent');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
