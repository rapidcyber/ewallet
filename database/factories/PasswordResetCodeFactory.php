<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PasswordResetCode>
 */
class PasswordResetCodeFactory extends Factory
{
    protected $model = \App\Models\PasswordResetCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phone_prefix = '639';
        $code = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
        $verification_id = bin2hex(date_format(now(), 'md').mt_rand(100000, 999999));
        $expires_at = now()->addMinutes(5);

        return [
            'contact' => $phone_prefix.fake()->unique()->numerify('#########'),
            'code' => $code,
            'verification_id' => $verification_id,
            'expires_at' => $expires_at,
        ];
    }
}
