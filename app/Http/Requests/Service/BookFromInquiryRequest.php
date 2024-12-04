<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class BookFromInquiryRequest extends FormRequest
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
            'inquiry_id' => 'required|exists:bookings,id',
            'service_date' => 'required|date|date_format:Y-m-d|after:today',
            'time_slots' => 'required|array',
            'time_slots.*' => 'required|array:start_time,end_time',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => 'required|date_format:H:i',
        ];
    }
}
