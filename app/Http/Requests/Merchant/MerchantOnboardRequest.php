<?php

namespace App\Http\Requests\Merchant;

use Illuminate\Foundation\Http\FormRequest;

class MerchantOnboardRequest extends FormRequest
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
            'name' => 'required|unique:merchants,name',
            'email' => 'required|email:rfc,dns',
            'phone_iso' => 'required|max:2',
            'phone_number' => 'required|numeric',
            'category' => 'required|exists:merchant_categories,slug',
            'website' => 'nullable|url',
            'landline_iso' => 'nullable|max:2',
            'landline_number' => 'nullable|numeric',
            'invoice_prefix' => 'nullable|alpha_num:ascii|size:5|unique:merchants,invoice_prefix',
            'location.latitude' => 'required|numeric',
            'location.longitude' => 'required|numeric',
            'location.address' => 'required|string',
            'dti_sec' => 'required|file|mimes:jpg,png,pdf|max:5120',
            'bir_cor' => 'required|file|mimes:jpg,png,pdf|max:5120',
        ];
    }
}
