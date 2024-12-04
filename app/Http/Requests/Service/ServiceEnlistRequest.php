<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class ServiceEnlistRequest extends FormRequest
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
            'name' => 'required|string|max:180',
            'description' => 'required|string|max:600',
            'category' => 'required|exists:service_categories,slug',
            'service_days' => 'required|array:monday,tuesday,wednesday,thursday,friday,saturday,sunday|min:1',
            'service_days.*' => 'nullable|array|min:1',
            'service_days.*.*' => 'required|array:start_time,end_time',
            'service_days.*.*.start_time' => 'required|date_format:H:i',
            'service_days.*.*.end_time' => 'required|date_format:H:i',
            'inquiry_form' => 'required|array|min:1|max:15',
            'inquiry_form.*' => 'required|array',
            'inquiry_form.*.question' => 'required|max:150',
            'inquiry_form.*.type' => 'required|in:paragraph,dropdown,multiple,checkbox',
            'inquiry_form.*.choices' => 'required_unless:inquiry_form.*.type,paragraph|array|min:1',
            'inquiry_form.*.choices.*' => 'required|alpha_num:ascii',
            'inquiry_form.*.important' => 'required|boolean',
            'location' => 'required|array',
            'location.latitude' => 'required|numeric',
            'location.longitude' => 'required|numeric',
            'location.address' => 'required|string',
            'files' => 'required|array|min:1',
            'files.*' => 'file|image|mimes:png,jpg|max:5120',
        ];
    }
}
