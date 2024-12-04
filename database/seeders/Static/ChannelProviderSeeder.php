<?php

namespace Database\Seeders\Static;

use App\Models\ChannelProvider;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use Illuminate\Database\Seeder;

class ChannelProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cp = [
            [
                'channel_id' => TransactionChannel::where('slug', 'instapay')->first()->id,
                'provider_id' => TransactionProvider::where('slug', 'allbank')->first()->id,
                'service_fee' => 15.00,
            ],
            [
                'channel_id' => TransactionChannel::where('slug', 'pesonet')->first()->id,
                'provider_id' => TransactionProvider::where('slug', 'allbank')->first()->id,
                'service_fee' => 15.00,
            ],
            [
                'channel_id' => TransactionChannel::where('slug', 'external_inbound')->first()->id,
                'provider_id' => TransactionProvider::where('slug', 'allbank')->first()->id,
                'service_fee' => 10.00,
            ],
        ];

        foreach ($cp as $pc) {
            ChannelProvider::firstOrCreate($pc);
        }
    }
}