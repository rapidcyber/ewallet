<?php

namespace App\Http\Requests\Bill;

use Illuminate\Foundation\Http\FormRequest;

class ShareBillRequest extends FormRequest
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
            'ref_no' => 'required|exists:bills,ref_no',
            'recipient_number' => 'required|phone:INTERNATIONAL',
            'payable' => 'required|boolean',
            'note' => 'nullable|max:300',
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_number.exists' => 'Recipient number is not register to Repay.',
            'recipient_number.phone' => 'Invalid phone number'
        ];
    }
}
