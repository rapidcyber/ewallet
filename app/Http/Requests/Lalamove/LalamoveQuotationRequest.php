<?php

namespace App\Http\Requests\Lalamove;

use Illuminate\Foundation\Http\FormRequest;

class LalamoveQuotationRequest extends FormRequest
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
            'sku' => 'required|exists:products,sku',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'required|string',
            'quantity' => 'required|numeric|min:1',
            'merc_ac' => 'sometimes|exists:merchants,account_number'
        ];
    }
}
