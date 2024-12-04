<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class ServiceInquireRequest extends FormRequest
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
            'service_id' => 'required|exists:services,id',
            'message' => 'required|max:1000',
            'answers' => 'required|array|min:1',
            'answers.*' => 'required|array:question,answer',
            'answers.*.answer' => 'required|array|min:1',
            'location' => 'required|array:address,latitude,longitude',
            'location.address' => 'required|string',
            'location.latitude' => 'required|numeric',
            'location.longitude' => 'required|numeric',
        ];
    }
}
