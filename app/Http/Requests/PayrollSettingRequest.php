<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'attendance_bonus' => 'required|numeric',
            'ot_per_hour' => 'required|numeric',
            'employee_esic' => 'required|numeric|between:0,99.99',
            'employee_pf' => 'required|numeric|between:0,99.99',
            'employer_esic' => 'required|numeric|between:0,99.99',
            'employer_pf' => 'required|numeric|between:0,99.99',
            'service_charge' => 'required|numeric|between:0,99.99',
            'bonus' => 'required|numeric|between:0,99.99',
            // 'el_amount' => 'required|numeric|between:0,99.99',
            'el_day_limit' => 'required|numeric',
            'lwf' => 'required|numeric',
            'year_closing' => 'required',
            'other_allowance' => 'required|numeric',
            'other_deduction' => 'required|numeric',
        ];
    }
}
