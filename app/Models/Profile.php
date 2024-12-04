<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'surname',
        'suffix',
        'mother_maiden_name',
        'nationality',
        'sex',
        'birth_place',
        'birth_date',
        'landline_iso',
        'landline_number',
        'photo_src',
        'status'
    ];

    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
