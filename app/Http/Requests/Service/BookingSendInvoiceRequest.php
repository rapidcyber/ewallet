<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class BookingSendInvoiceRequest extends FormRequest
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
            'booking_id' => 'required|exists:bookings,id',
            'invoice_id' => 'nullable|exists:invoices,id',

            ///
            'due_date' => 'required_without:invoice_id|date|after:now',
            'items' => 'required_without:invoice_id|array',
            'items.*' => 'required_without:invoice_id|array:name,description,price,quantity',
            'items.*.name' => 'required_without:invoice_id|max:255',
            'items.*.description' => 'required_without:invoice_id|max:300',
            'items.*.price' => 'required_without:invoice_id|numeric|min:1|max:99999999',
            'items.*.quantity' => 'required_without:invoice_id|numeric:min1',
            'minimum_partial' => "nullable|min:1",
            'inclusions' => 'nullable|array',
            'inclusions.*' => 'array:name,amount,deduct',
            'inclusions.*.name' => "required|in:vat,discount,shipping_fee",
            'inclusions.*.amount' => "required|numeric|min:1|max:99999999",
            'inclusions.*.deduct' => "required|boolean",
        ];
    }
}
