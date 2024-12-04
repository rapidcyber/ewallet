<?php

namespace Database\Seeders;

use App\Models\EntityReview;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class EntityReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        foreach ($products as $product) {
            EntityReview::factory(10)->create([
                'entity_id' => $product->id,
                'entity_type' => Product::class,
                'reviewer_id' => User::inRandomOrder()->first()->id,
                'reviewer_type' => User::class,
            ]);
        }

        $merchants = Merchant::all();
        foreach ($merchants as $merchant) {
            EntityReview::factory(10)->create([
                'entity_id' => $merchant->id,
                'entity_type' => Merchant::class,
                'reviewer_id' => User::inRandomOrder()->first()->id,
                'reviewer_type' => User::class,
            ]);
        }

        $services = Service::all();
        foreach ($services as $service) {
            EntityReview::factory(10)->create([
                'entity_id' => $service->id,
                'entity_type' => Service::class,
                'reviewer_id' => User::inRandomOrder()->first()->id,
                'reviewer_type' => User::class,
            ]);
        }
    }
}
