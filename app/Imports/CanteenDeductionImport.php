<?php

namespace App\Imports;

use App\User;
use App\Model\Role;
use App\Model\Branch;
use App\Model\Employee;
use App\Model\WorkShift;
use App\Model\Department;
use App\Model\Designation;
use App\Lib\Enumerations\UserStatus;
use App\Model\EmployeeFoodAndTelephoneDeduction;
use App\Model\Termination;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CanteenDeductionImport  implements ToModel, WithValidation, WithStartRow
{
    use Importable;

    public function rules(): array
    {
        return [
            '*.0' => 'required',
            '*.1' => 'required|exists:employee,finger_id',
            '*.2' => 'required|date_format:Y-m',
            '*.3' => 'required',
            '*.4' => 'required',
            '*.5' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Sr.No is required',
            '1.required' => 'Employee ID field is required',
            '2.required' => 'Month of Deduction field is required',
            '2.date_format' => 'Date format should be Y-m',
            '1.exists' => 'Employee ID is not exists',
            '3.required' => 'No of Breakfast field is required',
            '4.required' => 'No of Lunch field is required',
            '5.required' => 'No of Dinner field is required',
            // '3.date_format' => 'Date format should be Y-m-d',
        ];
    }

    public function model(array $row)
    {

        $month = "0000-00-00";

        // if ($row[2])
        //     $month =  \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('Y-m');

        $month = date('Y-m', strtotime($row[2]));

        //copy('foo/test.php', 'bar/test.php');

        $employee = Employee::where('finger_id', $row[1])->first();

        $ifExists =  EmployeeFoodAndTelephoneDeduction::where('month_of_deduction', $month)->where('finger_print_id', $row[1])->first();
        
        if(!$ifExists){
            $deduction = EmployeeFoodAndTelephoneDeduction::create([
                'month_of_deduction' => $month,
                'finger_print_id' => $employee->finger_id,
                'employee_id' => $employee->employee_id,
                'food_allowance_deduction_rule_id' => 1,
                'telephone_allowance_deduction_rule_id' =>  1,
                'call_consumed_per_month' => 0,
                'breakfast_count' => $row[3],
                'lunch_count' => $row[4],
                'dinner_count' => $row[5],
                'remarks' => 'NA',
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
                'status' => 1,
            ]);
        }else{
            $deduction = EmployeeFoodAndTelephoneDeduction::where('month_of_deduction', $month)->where('finger_print_id', $row[1])->update([
                'month_of_deduction' => $month,
                'finger_print_id' => $employee->finger_id,
                'employee_id' => $employee->employee_id,
                'food_allowance_deduction_rule_id' => 1,
                'telephone_allowance_deduction_rule_id' =>  1,
                'call_consumed_per_month' => 0,
                'breakfast_count' => $row[3],
                'lunch_count' => $row[4],
                'dinner_count' => $row[5],
                'remarks' => 'NA',
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
                'status' => 1,
            ]);
        }
       
    }

    public function startRow(): int
    {
        return 2;
    }
}
