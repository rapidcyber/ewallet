<?php

namespace App\Http\Requests\ECPay;

use Illuminate\Foundation\Http\FormRequest;

class ECLoadTransactRequest extends FormRequest
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
            'telco_name' => 'required',
            'variant_tag' => 'required',
            'amount' => 'required|numeric',
            'account_number' => 'required|min:8|max:20',
        ];
    }
}
