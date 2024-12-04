<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class VerifyIDSanityRequest extends FormRequest
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
            'front_image_id' => 'required|string',
            'back_image_id' => 'nullable|string',
            'request_id' => 'required|exists:profile_update_requests,request_id,user_id,' . auth()->user()->id,
        ];
    }
}
