<?php

namespace App\Http\Requests\AllBank;

use Illuminate\Foundation\Http\FormRequest;

class SOARequest extends FormRequest
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
            'acct_type' => 'required|in:opc,p2m',
            'start_date' => 'nullable|date|date_format:m/d/y',
            'end_date' => 'nullable|date|date_format:m/d/y',
            'trans_idcode' => 'nullable|string',
        ];
    }
}
