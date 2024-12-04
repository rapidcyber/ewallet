<?php

namespace App\Http\Requests\Lalamove;

use Illuminate\Foundation\Http\FormRequest;

class LalamoveWebhookRequest extends FormRequest
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
            'apiKey' => 'required|string',
            'timestamp' => 'required|numeric',
            'signature' => 'required|string',
            'eventId' => 'required|string',
            'eventType' => 'required|string',
            'data' => 'required|array',
        ];
    }
}
