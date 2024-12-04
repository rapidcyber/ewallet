<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\MediaCollections\Models\Concerns\IsSorted;

class Question extends Model
{
    use HasFactory, IsSorted;

    protected $hidden = [
        'entity_type',
        'entity_id',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'entity_id',
        'entity_type',
        'question',
        'type',
        'is_important',
        'order_column',
    ];

    protected $casts = [
        'is_important' => 'boolean',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function choices(): HasMany
    {
        return $this->hasMany(QuestionChoice::class);
    }
}
