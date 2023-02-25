<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubDepartmentRequest extends FormRequest
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

        if (isset($this->subdepartment)) {
            return [
                'sub_department_name' => 'required|regex:/^[A-Za-z0-9 ]+$/|unique:sub_departments,sub_department_name,' . $this->subdepartment, '.sub_department_id',
                'department_id' => 'required',

            ];
        }

        return [
            'sub_department_name' => 'required|regex:/^[A-Za-z0-9 ]+$/',
            'department_id' => 'required',

        ];

    }

    public function messages()
    {
        return [
            'department_id.required' => 'Department field is required',
            'sub_department_name.required' => 'Sub Unit field is required',
            'sub_department_name.regex' => 'Sub Unit format is invalid',
            'sub_department_name.unique' => 'Sub Unit should be unique',
        ];
    }

}
