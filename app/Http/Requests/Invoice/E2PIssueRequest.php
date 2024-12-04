<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class E2PIssueRequest extends FormRequest
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
            'phone_iso' => 'required',
            'phone_number' => 'required|numeric',
            'message' => 'nullable|max:300',
            'due_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'minimum_partial' => "nullable|min:1",
            'currency' => 'required|in:PHP',
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

    public function message(): array
    {
        return [
            'currency.in' => 'Unsupported currency.',
        ];
    }
}
