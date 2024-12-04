<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInquiryFormRequest extends FormRequest
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

            /// Update
            'update' => 'nullable|array|min:1',
            'update.*' => 'required|array',
            'update.*.id' => 'required|exists:questions,id',
            'update.*.question' => 'nullable|max:300',
            'update.*.type' => 'nullable|in:paragraph,dropdown,multiple,checkbox',
            'update.*.important' => 'nullable|boolean',
            'update.*.choices' => 'required_unless:update.*.type,paragraph|array|min:2',
            'update.*.choices.*' => 'required|string',

            /// ADD
            'add' => 'nullable|array|min:1',
            'add.*' => 'required|array',
            'add.*.question' => 'required|max:300',
            'add.*.type' => 'required|in:paragraph,dropdown,multiple,checkbox',
            'add.*.important' => 'required|boolean',
            'add.*.choices' => 'required_unless:add.*.type,paragraph|array|min:2',
            'add.*.choices.*' => 'required|string',
        ];
    }
}
