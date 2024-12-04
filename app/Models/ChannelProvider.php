<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ChannelProvider extends Pivot
{
    protected $table = 'channel_provider';

    public function channel(): BelongsTo
    {
        return $this->belongsTo(TransactionChannel::class, 'channel_id', 'id', 'channel_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(TransactionProvider::class, 'provider_id');
    }
}
