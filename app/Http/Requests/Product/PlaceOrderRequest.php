<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
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
            'sku' => 'required|exists:products,sku',
            'shipping_option' => 'required|exists:shipping_options,slug',
            'payment_option' => 'required|exists:payment_options,slug',
            'quantity' => 'required|numeric|min:1',
            'location' => 'required|array',
            'location.address' => 'required|string',
            'location.latitude' => 'required|numeric',
            'location.longitude' => 'required|numeric',
        ];
    }
}
