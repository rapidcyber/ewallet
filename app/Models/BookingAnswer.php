<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'question',
        'answer',
        'type',
        'is_important',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'question' => 'string',
        'answer' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
