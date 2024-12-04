<?php

namespace Database\Seeders;

use App\Models\CashInFacility;
use Illuminate\Database\Seeder;

class CashInFacilitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $facilities = [
            [
                'slug' => 'union_bank',
                'name' => 'UnionBank',
                'active' => 0,
            ],
            [
                'slug' => 'bpi',
                'name' => 'BPI',
                'active' => 0,
            ],
        ];

        foreach ($facilities as $facility) {
            $exists = CashInFacility::where('slug', $facility['slug'])->exists();

            if ($exists) {
                continue;
            }

            $s = new CashInFacility;
            $s->fill($facility);
            $s->save();
        }
    }
}
