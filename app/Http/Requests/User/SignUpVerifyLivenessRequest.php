<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SignUpVerifyLivenessRequest extends FormRequest
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
            'images' => 'required|array',
            'images.*.id' => 'required',
            'videos' => 'required|array',
            'videos.*' => 'required|array',
            'videos.*.metadata' => 'required|string',
            'videos.*.frames' => 'required|array',
            'videos.*.frames.*.index' => 'required|numeric',
            'videos.*.frames.*.label' => 'required|string',
            'videos.*.frames.*.base64' => 'required|string',
            'videos.*.frames.*.metadata' => 'required|string',
            'request_id' => 'required|exists:user_kycs,request_id,user_id,' . auth()->user()->id,
        ];
    }
}
