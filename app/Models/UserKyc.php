<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserKyc extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        /// Scores
        'liveness_score',
        'card_sanity_score',
        'selfie_sanity_score',
        'card_tampering_score',
        'compare_face_score',
        /// Request IDs
        'liveness_req_id',
        'card_sanity_req_id',
        'selfie_sanity_req_id',
        'card_tampering_req_id',
        'compare_face_req_id',

        /// Image IDs
        'selfie_image_id',
        'front_card_image_id',
        'back_card_image_id',

        //
        'card_info',
        'card_info_req_id',

        //
        'request_id',
    ];

    protected $casts = [
        'card_info' => 'array',
    ];
}
