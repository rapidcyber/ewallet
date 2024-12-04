<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'message' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['paid', 'partial', 'unpaid']),
            'type' => $this->faker->randomElement(['payable', 'quotation']),
        ];
    }
}
