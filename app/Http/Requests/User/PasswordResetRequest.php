<?php

namespace App\Http\Requests\User;

use App\Rules\PasswordRule;
use Illuminate\Foundation\Http\FormRequest;

class PasswordResetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'verification_id' => 'required|exists:password_reset_codes,verification_id',
            'code' => 'required',
            'new_password' => new PasswordRule,
        ];
    }
}
