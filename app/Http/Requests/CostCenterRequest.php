<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CostCenterRequest extends FormRequest
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
        if (isset($this->costcenter)) {
            return [
                'sub_department_id' => 'required',
                'cost_center_number' => 'required|regex:/^[A-Za-z0-9 ]+$/',
                // 'cost_center_number' => 'required|unique:cost_centers,cost_center_number',
            ];
        }
        return [
            'sub_department_id' => 'required',
            'cost_center_number' => 'required|regex:/^[A-Za-z0-9 ]+$/',
            // 'cost_center_number' => 'required|unique:cost_centers,cost_center_number',
        ];
    }

    public function messages()
    {
        return [
            'sub_department_id.required' => 'Sub unit field is required',
            'cost_center_number.required' => 'Cost center field is required',
            // 'cost_center_number.unique' => 'Cost center name Should be unique ',
        ];
    }
}
