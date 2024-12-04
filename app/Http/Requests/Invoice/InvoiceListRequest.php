<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceListRequest extends FormRequest
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
            'per_page' => 'nullable|numeric|min:1',
            'page' => 'nullable|numeric|min:1',
            'start' => 'nullable|in:week,month,quarter,year',
            'status' => 'nullable|in:paid,unpaid',
            'inbound' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'merc_ac.exists' => config('constants.messages.invalid_merc_ac'),
        ];
    }
}
