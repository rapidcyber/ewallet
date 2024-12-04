<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
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
            'base64' => 'required|string',
            'metadata' => 'required|string',
            'label' => 'required|string',
            'request_id' => 'required|exists:profile_update_requests,request_id,user_id,' . auth()->user()->id,
        ];
    }
}
