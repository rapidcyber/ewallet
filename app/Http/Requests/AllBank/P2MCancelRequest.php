<?php

namespace App\Http\Requests\AllBank;

use Illuminate\Foundation\Http\FormRequest;

class P2MCancelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'merc_ac' => 'nullable|exists:merchants,account_number',
            'token' => 'required|exists:qr_generated_data,merc_token',
        ];
    }
}
