<?php

namespace Database\Factories;

use App\Models\EmployeeRole;
use App\Models\Merchant;
use App\Models\SalaryType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $salary_type = SalaryType::inRandomOrder()->first();

        if ($salary_type->name == 'Per Day') {
            $salary = fake()->numberBetween(5, 10) * 100;
        } else {
            $salary = fake()->numberBetween(5, 10) * 3000;
        }

        return [
            'merchant_id' => Merchant::factory(),
            'user_id' => User::factory(),
            'employee_role_id' => EmployeeRole::whereNot('slug', str('Owner')->slug())->inRandomOrder()->first()->id,
            'occupation' => fake()->jobTitle(),
            'salary' => $salary,
            'salary_type_id' => $salary_type->id,
        ];
    }
}
