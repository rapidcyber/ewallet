<?php

namespace Database\Seeders;

use App\Models\Biller;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BillerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $biller_count = Biller::count();
        for($count = 1; $count <= 5; $count++) {
            $add_count = $biller_count + $count;
            $paddedNumber = str_pad($add_count, 4, '0', STR_PAD_LEFT);
            Biller::factory()->create([
                'name' => 'Biller ' . $add_count,
                'slug' => 'biller-' . $add_count,
                'short_name' => $add_count,
                'code' => $paddedNumber
            ]);
        }
    }
}
