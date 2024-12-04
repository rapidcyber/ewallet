<?php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\MerchantCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Merchant>
 */
class MerchantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phone_prefix = "639";
        $name = fake()->unique()->company();

        $date = fake()->dateTimeBetween('-6 months', 'now');

        do {
            $prefix = substr(Str::upper(Str::replace(' ', '', Str::limit($name, 5))), 0, 5);
    
            $exists = Merchant::where('invoice_prefix', $prefix)->exists();
        } while ($exists);

        return [
            'app_id' => fake()->unique()->uuid(),
            'user_id' => User::inRandomOrder()->first()->id,
            'merchant_category_id' => MerchantCategory::inRandomOrder()->first()->id,
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'phone_iso' => 'PH',
            'phone_number' => $phone_prefix . fake()->unique()->numerify('#########'),
            'invoice_prefix' => $prefix,
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
