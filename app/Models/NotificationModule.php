<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'needs_action',
    ];

    protected $casts = [
        'needs_action' => 'boolean',
    ];

    protected $hidden = ['id'];

    public $timestamps = false;

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
