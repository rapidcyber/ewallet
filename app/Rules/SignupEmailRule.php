<?php

namespace App\Rules;

use App\Models\OTP;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SignupEmailRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (User::where('email', $value)->exists()) {
            $fail('Email address is already in use');
        }

        $exists = OTP::where(function ($query) use ($value) {
            $query->where('contact', $value)
                ->where('type', 'sign_up')
                ->whereNotNull('verified_at');
        })->exists();
        if ($exists == false) {
            $fail('Email address is not verified');
        }
    }
}
