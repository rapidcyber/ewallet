<?php

namespace App\Http\Requests\Test;

use Illuminate\Foundation\Http\FormRequest;

/// @TODO - Remove when TS integration is implemented
class TestUserSignUpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (config('app.debug') == false) {
            return false;
        }
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
            'username' => 'required|unique:users,username',
            'email' => 'required|email:rfc:dns|unique:users,email',
            'password' => 'required',
            'phone_iso' => 'required',
            'phone_number' => 'required',
            'pin' => 'required|digits:4',
            'firstname' => 'required',
            'surname' => 'required',
        ];
    }
}
