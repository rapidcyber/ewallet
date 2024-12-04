<?php

namespace Database\Seeders\Static\Merchant;

use App\Models\SalaryType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SalaryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salary_types = [
            'Per Day',
            'Per Cutoff'
        ];

        foreach ($salary_types as $salary_type) {
            SalaryType::firstOrCreate([
                'name' => $salary_type
            ], [
                'slug' => str($salary_type)->slug('_')
            ]);
        }
    }
}
