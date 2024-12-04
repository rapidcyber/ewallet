<?php

namespace App\Rules;

use App\Models\OTP;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SignupPhoneRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $phone = str_replace('+', '', $value);

        /// validated if phone does not already exists or in use.
        if (User::where('phone_number', $phone)->exists()) {
            $fail('Phone number is already in use');
        }

        /// validate existence of OTP with where constraints
        $exists = OTP::where(function ($query) use ($phone) {
            $query->where(function ($query) use ($phone) {
                $query->where('contact', $phone)
                    ->orWhere('contact', "+$phone");
            })->where('type', 'sign_up')->whereNotNull('verified_at');
        })->exists();

        if ($exists == false) {
            $fail('Phone number is not verified');
        }
    }
}
