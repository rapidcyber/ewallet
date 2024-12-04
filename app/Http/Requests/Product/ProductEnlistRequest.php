<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductEnlistRequest extends FormRequest
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
            'name' => 'required|max:120',
            'description' => 'required|max:2000',
            'currency' => 'nullable|in:PHP',
            'price' => 'required|numeric',
            'condition' => 'required|exists:product_conditions,slug',
            'category' => 'required|exists:product_categories,slug',
            'on_demand' => 'required|boolean',
            'warehouses' => 'required|array',
            'warehouses.*' => 'required|min:1|array:id,stock',
            'warehouses.*.id' => 'required|numeric',
            'warehouses.*.stock' => 'required|numeric|min:1',
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:png,jpg|max:5120',

            /// details
            'weight' => 'required|numeric',
            'height' => 'required|numeric',
            'length' => 'required|numeric',
            'width' => 'required|numeric',
            'mass_unit' => 'required|in:mg,kg,t',
            'length_unit' => 'required|in:mm,cm,m,in,ft,yd',
        ];
    }
}
