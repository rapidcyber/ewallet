<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionChannel extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'name'];

    protected $hidden = ['id', 'slug', 'created_at', 'updated_at'];


    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(TransactionProvider::class, 'channel_provider', 'channel_id', 'provider_id')->withPivot('service_fee')->withTimestamps();
    }

    public function transactionProviders(): BelongsToMany
    {
        return $this->belongsToMany(TransactionProvider::class, 'channel_provider', 'channel_id', 'provider_id')->withPivot('service_fee')->withTimestamps();
    }
}
