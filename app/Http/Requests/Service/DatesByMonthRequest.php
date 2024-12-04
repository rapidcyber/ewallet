<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class DatesByMonthRequest extends FormRequest
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
            'service_id' => 'required|exists:services,id',
            'month_year' => 'required|date|date_format:Y-m',
            'status' => 'required|exists:booking_statuses,slug',
        ];
    }
}