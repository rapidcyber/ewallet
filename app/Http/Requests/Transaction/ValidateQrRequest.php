<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class ValidateQrRequest extends FormRequest
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
            'merc_ac' => 'nullable|exists:merchants,account_number',
            'ref_no' => 'required|exists:qr_generated_data,ref_no',
        ];
    }

    public function messages(): array {
        return [
            'ref_no.exists' => 'Invalid QR Code',
        ];
    }
}
