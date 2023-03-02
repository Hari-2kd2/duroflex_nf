<?php

namespace App\Imports;

use App\User;
use App\Model\Role;
use App\Model\Branch;
use App\Model\Employee;
use App\Model\WorkShift;
use App\Model\Department;
use App\Model\Designation;
use App\Model\Termination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Lib\Enumerations\UserStatus;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TerminationImport implements ToModel, WithValidation, WithStartRow
{
    use Importable;

    public function rules(): array
    {
        return [
            '*.0' => 'required',
            '*.1' => 'required|exists:employee,finger_id|unique:termination,finger_print_id',
            '*.2' => 'required|string|max:255',
            // '*.3' => 'required|date_format:Y-m-d',
            '*.3' => 'required|string|max:255',
            '*.4' => 'required|numeric',
            '*.5' => 'required|string|max:255',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Sr.No is required',
            '1.required' => 'Employee ID field is required',
            '2.required' => 'Subject field is required',
            '1.exists' => 'Employee ID is not exists',
            '1.unique' => 'Provide non terminated Employee Id, this data already present in termination list!',
            '3.required' => 'Description field is required',
            '4.required' => 'Termination Date field is required',
            '5.required' => 'Termination Type field is required',
            // '3.date_format' => 'Date format should be Y-m-d',
        ];
    }

    public function model(array $row)
    {
        DB::BeginTransaction();

        $employee = Employee::where('finger_id', $row[1])->first();

        $exist = Termination::where('terminate_to', $employee->employee_id)->first();
        // dd($row);

        if (!$exist) {

            $date = "0000-00-00";

            if ($row[4])
                try {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[4])->format('Y-m-d');
                } catch (\Throwable $th) {
                    $date = date('Y-m-d', strtotime($row[4]));
                }
            //copy('foo/test.php', 'bar/test.php');

            $terminationData = Termination::create([
                'finger_print_id' => $row[1],
                'terminate_to' => $employee->employee_id,
                'terminate_by' => auth()->user()->user_id,
                'termination_type' => $row[5],
                'subject' => $row[2],
                'notice_date' => date('Y-m-d'),
                'termination_date' => $date,
                'description' => $row[3],
                'status' => UserStatus::$NOTICE,
            ]);
        } else {
            $employee = null;
            $exist = null;
            $date = null;
        }

        DB::commit();
    }

    public function startRow(): int
    {
        return 2;
    }
}