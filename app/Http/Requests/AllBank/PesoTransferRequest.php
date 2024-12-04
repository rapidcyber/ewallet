<?php

namespace App\Http\Requests\AllBank;

use Illuminate\Foundation\Http\FormRequest;

class PesoTransferRequest extends FormRequest
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
            'amount' => 'required|numeric',
            'account_number' => 'required',
            'account_name' => 'required',
            'bank_code' => 'required',
            'bank_name' => 'required',
        ];
    }
}
