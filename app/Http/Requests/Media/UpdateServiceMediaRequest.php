<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceMediaRequest extends FormRequest
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
            'service_id' => 'required|exists:services,id',
            'delete' => 'nullable|array|min:1',
            'delete.*' => 'required|numeric',
            'upload' => 'nullable|array|min:1',
            'upload.*' => 'file|mimes:png,jpg|max:5120',
        ];
    }
}
