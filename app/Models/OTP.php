<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    use HasFactory;

    protected $fillable = ['contact', 'code', 'verification_id', 'expires_at', 'verified_at', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class, 'contact', 'email')
            ->orWhere('contact', 'phone_number');
    }
}
