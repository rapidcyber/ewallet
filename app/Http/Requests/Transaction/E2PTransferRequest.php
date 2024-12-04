<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class E2PTransferRequest extends FormRequest
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
            'amount' => 'required|numeric|min:1|max:20000',
            'phone_number' => 'required|exists:users,phone_number',
            'merc_ac' => 'nullable|exists:merchants,account_number',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Minimum transferrable is 1.',
            'phone_number.exists' => 'This phone number is not registered.',
            'merc_ac.exists' => config('constants.messages.invalid_merc_ac'),
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw (new ValidationException($validator))
            ->status(499);
    }
}
