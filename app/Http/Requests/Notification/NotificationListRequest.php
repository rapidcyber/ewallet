<?php

namespace App\Http\Requests\Notification;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;

class NotificationListRequest extends FormRequest
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
            'per_page' => 'nullable|numeric|min:1',
            'page' => 'nullable|numeric|min:1',
            'merc_ac' => 'nullable|exists:merchants,account_number',
        ];
    }

    public function messages(): array
    {
        return [
            'merc_ac.exists' => config('constants.messages.invalid_merc_ac'),
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw (new ValidationException($validator))
            ->status(499);
    }
}
