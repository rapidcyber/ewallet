<?php

namespace Database\Factories;

use App\Models\Biller;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class BillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $entity_type = fake()->randomElement(['merchant', 'user']);
        $date = fake()->dateTimeBetween('-1 month', 'now');
        $biller = Biller::inRandomOrder()->first();

        return [
            'entity_id' => $entity_type == 'merchant' ? Merchant::factory() : User::factory(),
            'entity_type' => $entity_type == 'merchant' ? Merchant::class : User::class,
            'ref_no' => 'BL' . $date->format('ymd') . fake()->unique()->numerify('########'),
            'receipt_email' => fake()->randomElement([null, fake()->safeEmail()]),
            'biller_code' => $biller->code,
            'biller_name' => $biller->name,
            'infos' => [],
            'amount' => fake()->numberBetween(100, 10000),
            'created_at' => $date,
            'updated_at' => $date
        ];
    }
}
