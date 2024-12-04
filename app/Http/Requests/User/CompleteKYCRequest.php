<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CompleteKYCRequest extends FormRequest
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
            'card_type' => 'required|string',
            'selfie_image_id' => 'required|string',
            'front_card_image_id' => 'required|string',
            'back_card_image_id' => 'nullable|string',
            'request_id' => 'required|exists:user_kycs,request_id,user_id,' . auth()->user()->id,
        ];
    }
}
