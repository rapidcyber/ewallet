<?php

namespace Database\Seeders\Static\Merchants;

use App\Models\MerchantCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MerchantCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Automotive',
            'Business Support and Supplies',
            'Computer and Electronics',
            'Construction and Contractors',
            'Education',
            'Entertainment',
            'Food and Dining',
            'Health and Medicine',
            'Home and Garden',
            'Legal and Financial',
            'Manufacturing',
            'Merchant',
            'Personal Care and Services',
            'Real State',
            'Fintech',
        ];

        foreach ($categories as $category) {
            MerchantCategory::firstOrCreate([
                'name' => $category,
                'slug' => str($category)->slug('_')
            ]);
        }
    }
}
