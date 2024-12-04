<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UploadFramesRequest extends FormRequest
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
            'frames' => 'required|array',
            'frames.*.base64' => 'required|string',
            'frames.*.index' => 'required|numeric',
            'frames.*.metadata' => 'required',
            'request_id' => 'required|exists:profile_update_requests,request_id,user_id,' . auth()->user()->id,
        ];
    }
}
