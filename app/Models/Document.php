<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'name',
    ];

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'entity');
    }
}
