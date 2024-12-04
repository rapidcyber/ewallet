<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TransactionProvider extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'slug'];

    protected $hidden = ['id', 'slug', 'created_at', 'updated_at'];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function transactions_made(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'sender');
    }

    public function transactions_outflow(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'recipient');
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(TransactionChannel::class, 'channel_provider', 'provider_id', 'channel_id')->withPivot('service_fee')->withTimestamps();
    }

    public function transactionChannels(): BelongsToMany
    {
        return $this->belongsToMany(TransactionChannel::class, 'channel_provider', 'provider_id', 'channel_id')->withPivot('service_fee')->withTimestamps();
    }
}
