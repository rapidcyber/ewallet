<?php

namespace App\Http\Requests\AllBank;

use Illuminate\Foundation\Http\FormRequest;

class InstaTransferRequest extends FormRequest
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
            'verification_id' => 'required|exists:o_t_p_s,verification_id',
            'code' => 'required|numeric|digits:6',
            'amount' => 'required|numeric|max:50000',
            'account_number' => 'required',
            'account_name' => 'required',
            'bank_code' => 'required',
            'bank_name' => 'required',
        ];
    }
}
