<?php

namespace Database\Seeders\Static\Products;

use App\Models\ProductCondition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conditions = [
            'Brand New',
            'Used'
        ];

        foreach($conditions as $condition) {
            ProductCondition::firstOrCreate([
                'name' => $condition
            ], [
                'slug' => str($condition)->slug('_')
            ]);
        }
    }
}
