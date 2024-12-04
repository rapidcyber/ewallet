<?php

namespace App\Http\Requests\Security;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'code' => 'required|numeric|digits:6',

            /**
             * Password Format
             * 
             * - at least 1 uppercase character
             * - at least 1 numeric character
             * - minimum 8 character
             */
            'password' => 'required|regex:/^(?=.*?[A-Z])(?=.*?[0-9]).{8,}$/',
        ];
    }
}
