<?php

namespace App\Traits;

use App\Models\ChannelProvider;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;

trait WithServiceFee
{
    public function get_service_fee(TransactionProvider $provider, TransactionChannel $channel): float {

        $fee = ChannelProvider::where([
            'channel_id' => $channel->id,
            'provider_id' => $provider->id,
        ])->first();

        if (empty($fee) == false) {
            return $fee->service_fee;
        }

        return 0;
    }
}
