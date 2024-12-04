<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class SendQuotationRequest extends FormRequest
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
            'inquiry_id' => 'required|exists:bookings,id',
            'items' => 'required|array',
            'items.*' => 'required|array:name,description,price,quantity',
            'items.*.name' => 'required|max:255',
            'items.*.description' => 'required|max:300',
            'items.*.price' => 'required|numeric|min:1|max:99999999',
            'items.*.quantity' => 'required|numeric:min1',
            'inclusions' => 'nullable|array',
            'inclusions.*' => 'array:name,amount,deduct',
            'inclusions.*.name' => "required|in:vat,discount,shipping_fee",
            'inclusions.*.amount' => "required|numeric|min:1|max:99999999",
            'inclusions.*.deduct' => "required|boolean",
        ];
    }
}
