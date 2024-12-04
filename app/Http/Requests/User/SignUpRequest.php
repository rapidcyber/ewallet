<?php

namespace App\Http\Requests\User;

use App\Rules\PasswordRule;
use App\Rules\SignupEmailRule;
use App\Rules\SignupPhoneRule;
use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $phone = $this->input('phone_number');
        if (str_starts_with($phone, '+') == false) {
            $phone = "+$phone";
        }

        $this->merge([
            'phone_number' => $phone
        ]);

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
            'phone_number' => [
                'required',
                'phone:mobile,INTERNATIONAL',
                new SignupPhoneRule
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                new SignupEmailRule,
            ],
            'firstname' => 'required|string|max:50',
            'surname' => 'required|string|max:50',
            'middlename' => 'nullable|string',
            'ext' => 'nullable|string',
            'password' => ['required', new PasswordRule],
            'pin' => 'required|numeric|digits:4',
        ];
    }
}
