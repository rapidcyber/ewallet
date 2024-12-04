<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class AddPreviousWorkRequest extends FormRequest
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
            'title' => 'required|string|max:180',
            'description' => 'required|string|max:600',
            'files' => 'required|array|min:1|max:5',
            'files.*' => 'file|mimes:png,jpg|max:5120',
        ];
    }
}
