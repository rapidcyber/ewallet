<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class BookingUpdateStatusRequest extends FormRequest
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
            'booking_id' => 'required|exists:bookings,id',
            'response' => 'required|in:accept,decline',
        ];
    }
}
