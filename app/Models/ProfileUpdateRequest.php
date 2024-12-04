<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfileUpdateRequest extends Model
{
    use SoftDeletes;

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }
}
