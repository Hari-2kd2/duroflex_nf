<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollRequest extends FormRequest
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
            'employee' => 'required',
            'finger_print_id' => 'required',
            'month' => 'required',
            'year' => 'required',
            'no_day_wages' => 'required',
            'total_days' => 'required',
            'per_day_basic_da' => 'required',
            'per_day_hra' => 'required',
            'per_day_wages' => 'required',
            'basic_da_amount' => 'required',
            'basic_amount' => 'required',
            'da_amount' => 'required',
            'hra_amount' => 'required',
            'wages_amount' => 'required',
            'attendance_bonus' => 'required',
            'ot_hours' => 'required',
            'ot_per_hours' => 'required',
            'ot_amount' => 'required',
            'gross_salary' => 'required',
            'employee_pf' => 'required',
            'employee_pf_percentage' => 'required',
            'employee_esic' => 'required',
            'employee_esic_percentage' => 'required',
            'canteen' => 'required',
            'net_salary' => 'required',
            'employer_pf' => 'required',
            'employer_pf_percentage' => 'required',
            'employer_esic' => 'required',
            'employer_esic_percentage' => 'required',
            'service_charge_percentage' => 'required',
            'service_charge' => 'required',
            'bonus_percentage' => 'required',
            'bonus_amount' => 'required',
            'earned_leave_balance' => 'required',
            'earned_leave' => 'required',
            'leave_amount' => 'required',
            'manhours' => 'required',
            // 'manhours_amount' => 'required',
            'manhour_days' => 'required',
            'salary' => 'required',
            // 'el_bonus' => 'required',
            'retained_bonus' => 'required',
            'retained_service_charge' => 'required',
            'retained_attendance_bonus' => 'required',
            'retained_leave_amount' => 'required',
            'lwf' => 'required',
            'employee_total_deduction' => 'required',
            'employer_total_deduction' => 'required',
            'branch' => 'required',
            'costcenter' => 'required',
            'unit' => 'required',
            'department' => 'required',
            'date' => 'required',
            'fdate' => 'required',
            'tdate' => 'required',
            'company_holiday' => 'required',
        ];
    }
}
