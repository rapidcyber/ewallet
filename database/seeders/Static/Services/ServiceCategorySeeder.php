<?php

namespace Database\Seeders\Static\Services;

use App\Models\ServiceCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Tenant Management' => [
                'Leasing Management', 
                'Marketing and Screening', 
                'Rent and Legalities'
            ],
            'Financial Management' => [
                'Financial Efficiency with RePay', 
                'Financial Reporting'
            ],
            'Property Maintenance and Inspection' => [
                'Aircon Sales and Services',
                'Appliance Repair',
                'Carpentry',
                'Cleaning Services',
                "Constructions",
                "Furnitures, Fixtures, Upholsterys",
                "Landscape and Gardenings",
                "LPG Deliverys",
                "Plumbings",
                "Roofing / Gutter Services",
                "Smart Home Upgrade Installations",
                "Solar Services",
                "Truck Rentals / Movers",
                "Water Delivery & Purification"
            ],
            'Utility and Operations Management' => [
                'Utility Management', 
                'Sustainability and Community'
            ],
            'Additional Services' => [
                'Concierge Services', 
                'Short-Term Rental Advantage'
            ],
            'Specialized Support' => [
                'Rent and Legalities'
            ],
        ];

        foreach($categories as $main_category => $sub_categories) {
            $parent_category = ServiceCategory::firstOrCreate([
                'name' => $main_category,
                'slug' => str($main_category)->slug('_')
            ]);

            foreach($sub_categories as $sub_category) {
                ServiceCategory::firstOrCreate([
                    'name' => $sub_category,
                    'slug' => str($sub_category)->slug('_'),
                    'parent' => $parent_category->id
                ]);
            }
        }
    }
}
