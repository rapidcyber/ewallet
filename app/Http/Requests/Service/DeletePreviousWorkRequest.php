<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class DeletePreviousWorkRequest extends FormRequest
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
            'work_id' => 'required|exists:previous_works,id',
        ];
    }
}
