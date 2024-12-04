<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class AuthVerifyOTPRequest extends FormRequest
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
            'verification_id' => 'required|numeric|exists:o_t_p_s,verification_id',
            'code' => 'required|numeric|digits:6'
        ];
    }

    public function messages()
    {
        return [
            'verification_id.exists' => 'Invalid OTP',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw (new ValidationException($validator))
            ->status(499);
    }
}
