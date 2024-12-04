<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductInfoRequest extends FormRequest
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
            'merc_ac' => 'required|exists:merchants,account_number',
            'sku' => 'required|exists:products,sku',
            'name' => 'nullable|max:120',
            'description' => 'required|max:2000',
            'price' => 'nullable|numeric',
            'condition' => 'nullable|exists:product_conditions,slug',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'mass_unit' => 'nullable|in:mg,kg,t',
            'length_unit' => 'nullable|in:mm,cm,m,in,ft,yd',
        ];
    }
}
