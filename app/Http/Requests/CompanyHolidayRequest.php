<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyHolidayRequest extends FormRequest
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
            'fdate' => 'required',
            'tdate' => 'required',
            'employee_id' => 'required',
            'comment' => 'nullable|max:255',
        ];
    }

    public function messages()
    {
        return [
            'fdate.required' => 'From Date field is required.',
            'tdate.required' => 'To Date field is required.',
            'employee_id.required' => 'Employee ID field is required.',
            'comment.max' => 'Comment field limit should be lessthan 255 digits.',
        ];
    }
}
