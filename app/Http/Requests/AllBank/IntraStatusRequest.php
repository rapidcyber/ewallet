<?php

namespace App\Http\Requests\AllBank;

use Illuminate\Foundation\Http\FormRequest;

class IntraStatusRequest extends FormRequest
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
            // 'ref_no' => 'required|exists:transactions,ref_no'
            'ref_no' => 'required'
        ];
    }
}