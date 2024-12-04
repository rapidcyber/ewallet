<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EntityReview extends Model
{
    use HasFactory;

    protected $hidden = [
        'entity_id',
        'entity_type',
        'reviewer_id',
        'reviewer_type',
        'updated_at'
    ];

    protected $fillable = [
        'comment',
        'rating',
        'entity_id',
        'entity_type',
        'reviewer_id',
        'reviewer_type',
    ];

    protected $appends = [
        'reviewer_name'
    ];

    public function getReviewerNameAttribute()
    {
        return $this->reviewer()->first()->name;
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): MorphTo
    {
        return $this->morphTo();
    }
}
