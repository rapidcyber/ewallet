<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class LocationListRequest extends FormRequest
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
            'location_id' => 'required_without_all:latitude,longitude,radius|numeric|exists:locations,id',
            'per_page' => 'nullable|numeric|min:1',
            'page' => 'nullable|numeric|min:1',
            'latitude' => 'required_with_all:longitude,radius|numeric',
            'longitude' => 'required_with_all:latitude,radius|numeric',
            'radius' => 'required_with_all:latitude,longitude|numeric'
        ];
    }
}
