<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AuthAttempt extends Model
{
    use HasFactory;


    protected $fillable = ['user_id'];

    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }
}
