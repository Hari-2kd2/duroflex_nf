<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MonthlyWorkingDayRequest extends FormRequest
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
        if (isset($this->working_days)) {
            return [
                'year'  => 'required|unique:monthly_workingdays,year,' . $this->working_days . ',working_id',

            ];
        }
        return [
            'year' => 'required',
        ];
    }
}
