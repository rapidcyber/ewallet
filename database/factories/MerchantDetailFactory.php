<?php

namespace Database\Factories;

use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MerchantDetail>
 */
class MerchantDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'merchant_id' => Merchant::factory(),
            'website' => fake()->domainName(),
            'landline_iso' => 'PH',
            'landline_number' => '288' . fake()->unique()->numerify('######'),
            'dti_sec' => fake()->unique()->numerify('########'),
            'description' => fake()->randomElement([null, fake()->text()]),
        ];
    }
}
