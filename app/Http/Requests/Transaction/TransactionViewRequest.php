<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class TransactionViewRequest extends FormRequest
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
            'txn_no' => 'required|exists:transactions,txn_no',
            'merc_ac' => 'nullable|exists:merchants,account_number'
        ];
    }

    public function messages(): array
    {
        return [
            'merc_ac.exists' => config('constants.messages.invalid_merc_ac'),
            'txn_no.exists' => config('constants.messages.invalid_txn_ref'),
        ];
    }
}
