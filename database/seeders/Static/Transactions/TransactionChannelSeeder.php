<?php

namespace Database\Seeders\Static\Transactions;

use App\Models\TransactionChannel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = [
            [
                'name' => 'Repay',
                'code' => 'RPY'
            ],
            [
                'name' => 'InstaPay',
                'code' => 'ISP'
            ],
            [
                'name' => 'PESONet',
                'code' => 'PNT'
            ],

            /// External Inbound
            /// - for some reason we need this for inbound transactions (I.E. Cash-in)
            ///   reason is we cannot determine which channel the user chose to transact with outside of the system.
            [
                'name' => 'External Inbound',
                'code' => 'EXI'
            ],
            /// External Outbound
            /// - For intrabank outbound transactions (I.E. Cash out from `A Bank account` to another `A Bank account`)
            ///   reason is intrabank doesn't necessarily use other channel for transaction like instapay or pesonet.
            [
                'name' => 'External Outbound',
                'code' => 'EXO',
            ]
        ];

        foreach ($channels as $channel) {
            TransactionChannel::firstOrCreate([
                'name' => $channel['name']
            ], [
                'slug' => str($channel['name'])->slug('_'),
                'code' => $channel['code']
            ]);
        };
    }
}
