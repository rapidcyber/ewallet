<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class MerchantReviewListRequest extends FormRequest
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
            'account_number' => 'required|exists:merchants,account_number',
            'per_page' => 'nullable|numeric|min:1',
            'page' => 'nullable|numeric|min:1',
        ];
    }
}
