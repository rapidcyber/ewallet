<?php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $approval_status = fake()->randomElement(['review', 'approved', 'rejected', 'suspended']);
        $date = fake()->dateTimeBetween('-5 month', 'now');

        do {
            $product_name = 'Product ' . $this->faker->word();
        } while (Product::where('name', $product_name)->exists());

        return [
            'merchant_id' => Merchant::factory(),
            'sku' => $this->generate_product_sku(),
            'product_category_id' => ProductCategory::inRandomOrder()->whereNot('parent', null)->first()->id,
            'name' => $product_name,
            'description' => fake()->paragraph(4),
            'price' => fake()->numberBetween(100, 3000),
            'product_condition_id' => ProductCondition::inRandomOrder()->first()->id,
            'on_demand' => fake()->boolean(),
            'stock_count' => fake()->numberBetween(10, 1000),
            'approval_status' => $approval_status,
            'is_active' => $approval_status == 'approved' ? fake()->boolean() : false,
            'is_featured' => $approval_status == 'approved' ? fake()->boolean() : false,
        ];
    }

    private function generate_product_sku()
    {
        $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphaLen = strlen($alpha);
        $randomAlpha = '';
        for ($i = 0; $i < 4; $i++) {
            $randomAlpha = $randomAlpha . $alpha[rand(0, $alphaLen - 1)];
        }

        $numeric = '0123456789';
        $numericLen = strlen($numeric);
        $randomNumeric = '';
        for ($i = 0; $i < 8; $i++) {
            $randomNumeric = $randomNumeric . $numeric[rand(0, $numericLen - 1)];
        }

        $sku = $randomAlpha . $randomNumeric;
        $check_sku = Product::where('sku', $sku)->exists();

        if ($check_sku) {
            return $this->generate_product_sku();
        }

        return $sku;
    }
}
