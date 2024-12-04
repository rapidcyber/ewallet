<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Traits\WithNumberGeneration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inquiry>
 */
class InquiryFactory extends Factory
{
    use WithNumberGeneration;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => fake()->sentence(),
            'message' => fake()->text(255),
            'status' => fake()->randomElement([0, 1]),
            'deleted_at' => fake()->boolean(60) ? fake()->dateTimeBetween($date, 'now') : null,
            'created_at' => $date,
            'updated_at' => $date
        ];
    }
}
