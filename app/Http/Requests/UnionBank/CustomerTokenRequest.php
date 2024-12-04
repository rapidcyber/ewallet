<?php

namespace App\Http\Requests\UnionBank;

use Illuminate\Foundation\Http\FormRequest;

class CustomerTokenRequest extends FormRequest
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
            'code' => 'required',
            'name' => 'nullable|alpha_num:ascii|max:50',
        ];
    }
}
