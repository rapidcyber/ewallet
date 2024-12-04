<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUpdateInfoRequest extends FormRequest
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

        // $name = $validated['name'];
        // $description = $validated['description'];
        // $category_slug = $validated['category'];
        // $service_days = $validated['service_days'];
        // $location = $validated['location'];
        return [
            'merc_ac' => 'required|exists:merchants,account_number',
            'service_id' => 'required|exists:services,id',
            'name' => 'nullable|string|max:180',
            'description' => 'nullable|string|max:600',
            'category' => 'nullable|exists:service_categories,slug',
            'service_days' => 'nullable|array:monday,tuesday,wednesday,thursday,friday,saturday,sunday|min:1',
            'service_days.*' => 'nullable|array|min:1',
            'service_days.*.*.start' => 'required|date_format:H:i',
            'service_days.*.*.end' => 'required|date_format:H:i',
            'location' => 'nullable|array',
            'location.latitude' => 'required_with:location|numeric',
            'location.longitude' => 'required_with:location|numeric',
            'location.address' => 'required_with:location|string',
        ];
    }
}
