<?php

namespace App\Http\Requests\ECPay;

use Illuminate\Foundation\Http\FormRequest;

class BillCreateRequest extends FormRequest
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
            'biller_code' => 'required',
            'biller_name' => 'required',
            'infos' => 'required|array',
            'infos.First Field' => 'required|array:tag,caption,format,max,value',
            'infos.Second Field' => 'required|array:tag,caption,format,max,value',
            'infos.*.caption' => 'required',
            'infos.*.format' => 'required',
            'infos.*.max' => 'required',
            'infos.*.value' => 'required',
            'currency' => 'nullable',
            'amount' => 'required|numeric',
            'due_date' => 'required|date|after_or_equal:now',
            'receipt_email' => 'nullable|email:rfc,dns',
            'remind_date' => 'nullable|numeric|between:1,25',
            'service_charge' => 'nullable|numeric',
        ];
    }
}
