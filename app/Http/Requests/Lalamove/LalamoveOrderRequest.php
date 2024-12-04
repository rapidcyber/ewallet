<?php

namespace App\Http\Requests\Lalamove;

use Illuminate\Foundation\Http\FormRequest;

class LalamoveOrderRequest extends FormRequest
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
            'shipping_option' => 'required|exists:shipping_options,slug',
            'payment_option' => 'required|exists:payment_options,slug',
            'quotation_id' => 'required|exists:lalamove_services,quotation_id',
            'merc_ac' => 'nullable|exists:merchants,account_number',
        ];
    }
}
