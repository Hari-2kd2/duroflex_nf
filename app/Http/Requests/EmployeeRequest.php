<?php

namespace App\Http\Requests;

use App\Model\Employee;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
        if (isset($this->employee)) {
            $result = Employee::where('employee_id', $this->employee)->first();
            return [
                'role_id' => 'required',
                'user_name' => 'required|regex:/^[A-Za-z0-9 ]+$/|unique:user,user_name,' . $result->user_id . ',user_id',
                'first_name' => 'required|regex:/^[A-Za-z0-9 ]+$/',
                'last_name' => 'nullable|regex:/^[A-Za-z0-9 ]+$/',
                'father_name' => 'required|regex:/^[A-Za-z0-9 ]+$/',
                'finger_id' => 'required|regex:/^[A-Za-z0-9 ]+$/|unique:employee,finger_id,' . $this->employee . ',employee_id',
                'department_id' => 'required',
                'designation_id' => 'required',
                'work_shift_id' => 'nullable',
                // 'email' => 'nullable|unique:employee,email,' . $this->employee . ',employee_id',
                // 'official_email' => 'nullable|unique:employee,official_email,' . $this->employee . ',employee_id',
                'phone' => 'required',
                'gender' => 'required',
                'date_of_birth' => 'required',
                'date_of_joining' => 'required',
                'status' => 'required',
                // 'institute.*'       => 'required',
                // 'board_university.*'=> 'required',
                // 'degree.*'          => 'required',
                // 'passing_year.*'    => 'required',
                'designation.*' => 'required',
                // 'organization_name.*' => 'required',
                // 'from_date.*'      => 'required',
                // 'to_date.*'        => 'required',
                // 'responsibility.*' => 'required',
                // 'skill.*'          => 'required',
                'photo' => 'mimes:jpeg,jpg,png|max:200',
                'bank_name' => 'required',
                'bank_branch' => 'required',
                'bank_account_no' => 'required',
                'bank_of_the_city' => 'required',
                'ifsc_no' => 'required',
                'pan_no' => 'required',
                'leave_balance' => 'required',
                'sub_department_id' => 'required',
                'cost_center_id' => 'required',
                'basic_amt' => 'required',
                'da_amt' => 'required',
                'hra_amt' => 'required',
                'daily_wage' => 'required',
                'service_charge' => 'required',

            ];
        }
        return [
            'role_id' => 'required',
            'user_name' => 'required|regex:/^[A-Za-z0-9 ]+$/|unique:user',
            'password' => 'required|confirmed',
            'first_name' => 'required|regex:/^[A-Za-z0-9 ]+$/',
            'last_name' => 'nullable|regex:/^[A-Za-z0-9 ]+$/',
            'father_name' => 'required|regex:/^[A-Za-z0-9 ]+$/',
            'finger_id' => 'required|regex:/^[A-Za-z0-9 ]+$/|unique:employee',
            'department_id' => 'required',
            'designation_id' => 'required',
            'work_shift_id' => 'nullable',
            'email' => 'nullable|unique:employee,email,' . $this->employee . ',employee_id',
            'official_email' => 'nullable|unique:employee,official_email,' . $this->employee . ',employee_id',
            'phone' => 'required',
            'gender' => 'required',
            'date_of_birth' => 'required',
            'date_of_joining' => 'required',
            'status' => 'required',
            // 'institute.*'         => 'required',
            // 'board_university.*'  => 'required',
            // 'degree.*'            => 'required',
            // 'passing_year.*'      => 'required',
            'designation.*' => 'required',
            // 'organization_name.*' => 'required',
            // 'from_date.*'         => 'required',
            // 'to_date.*'           => 'required',
            // 'responsibility.*'    => 'required',
            // 'skill.*'             => 'required',
            'photo' => 'mimes:jpeg,jpg,png|max:200',
            'bank_name' => 'required',
            'bank_branch' => 'required',
            'bank_account_no' => 'required',
            'bank_of_the_city' => 'required',
            'ifsc_no' => 'required',
            'pan_no' => 'required',
            'leave_balance' => 'required',
            'sub_department_id' => 'required',
            'cost_center_id' => 'required',
            'daily_wage' => 'required',
            'basic_amt' => 'required',
            'da_amt' => 'required',
            'hra_amt' => 'required',
            'service_charge' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'role_id.required' => 'The role field is required.',
            'institute*.required' => 'The institute field is required.',
            'board_university*.required' => 'The board university field is required.',
            'degree*.required' => 'The degree field is required.',
            'passing_year*.required' => 'The passing year field is required.',
            'organization_name*.required' => 'The organization name field is required.',
            'from_date*.required' => 'The from date field is required.',
            'to_date*.required' => 'The to date field is required.',
        ];
    }
}
