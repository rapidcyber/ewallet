<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class ServiceListRequest extends FormRequest
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
            'account_number' => 'nullable|exists:merchants,account_number',
            'per_page' => 'nullable|numeric|min:1',
            'page' => 'nullable|numeric|min:1',
            'category' => 'nullable|exists:service_categories,slug',
            'featured' => 'nullable|boolean',
            'service_days' => 'nullable|array|min:1',
            'service_days.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'search_key' => 'nullable|string'
        ];
    }
}
