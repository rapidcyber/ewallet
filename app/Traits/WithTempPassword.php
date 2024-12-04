<?php

namespace App\Traits;

use Illuminate\Support\Facades\Hash;

trait WithTempPassword
{
    public function generate_temp_password($length = 12)
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=';
        
        // Ensure the password has at least one character from each pool
        $password = [
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)]
        ];

        // Fill the remaining length with random characters from all pools
        $allCharacters = $lowercase . $uppercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password[] = $allCharacters[random_int(0, strlen($allCharacters) - 1)];
        }

        // Shuffle the password to randomize the character order
        shuffle($password);

        $plain = implode('', $password);
        $hash = Hash::make($plain);

        return [$plain, $hash];
    }
}
