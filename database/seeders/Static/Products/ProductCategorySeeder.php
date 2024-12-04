<?php

namespace Database\Seeders\Static\Products;

use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Baby and Kids Supply' => [
                'Baby clothes and accessories',
                'Baby supplies',
                'Kids wear and accessories',
                'Toys and games',
            ],
            'Car Accessories and Portable Transports' => [
                'Bicycles and bike accessories',
                'Car parts and accessories',
                'Decals and stickers',
                'Mini cars',
                'Portable transports (skateboards, skooters, etc.)',
            ],
            'Clothes and Accessories' => [
                'Bags',
                'Footwear',
                'Jewelries / accessories',
                'Men\'s',
                'Women\'s',
            ],
            'Consumable Products' => [
                'Beauty products',
                'Grocery',
                'Health products',
                'Pet food',
            ],
            'Electronics' => [
                'Computers & gadgets',
                'Mobile phones',
                'Wearables',
            ],
            'Hobbies and Education' => [
                'Antiques and Collectibles',
                'Arts and Crafts',
                'Books, Magazines, and other printed materials',
                'Musical Instruments',
                'School and Office Suplies',
            ],
            'Home Improvements' => [
                'Appliances',
                'Furniture',
                'Garden',
                'Kitchen',
                'Tools',
            ],
        ];

        foreach ($categories as $primaryCategory => $subCategories) {
            $primaryCategorySlug = str($primaryCategory)->slug('_');
            if (!ProductCategory::where('slug', $primaryCategory)->exists()) {
                $parentCategory = ProductCategory::firstOrCreate([
                    'name' => $primaryCategory,
                    'slug' => $primaryCategorySlug,
                ]);

                foreach ($subCategories as $subCat) {
                    $sSlug = str($subCat)->slug('_');
                    if (!ProductCategory::where('slug', $sSlug)->where('parent', $parentCategory->id)->exists()) {
                        ProductCategory::firstOrCreate([
                            'name' => $subCat,
                            'slug' => $sSlug,
                            'parent' => $parentCategory->id,
                        ]);
                    }
                }
            }
        }
    }
}
