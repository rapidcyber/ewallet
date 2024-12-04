<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory;

    protected $hidden = [
        'recipient_id',
        'recipient_type',
        'notification_module_id',
        'updated_at',
    ];

    protected $casts = [
        'extras' => 'array',
    ];

    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(NotificationModule::class, 'notification_module_id');
    }
}
