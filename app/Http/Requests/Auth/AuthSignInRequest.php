<?php

namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;

class AuthSignInRequest extends FormRequest
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
            'phone_number' => 'required|phone:mobile,INTERNATIONAL',
            'password' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.phone' => 'Invalid phone number',
        ];
    }
}
