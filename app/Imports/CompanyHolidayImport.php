<?php

namespace App\Imports;

use App\Model\CompanyHoliday;
use App\Model\Employee;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CompanyHolidayImport implements ToModel, WithValidation, WithStartRow
{
    use Importable;

    public function rules(): array
    {
        return [
            '*.0' => 'required',
            '*.1' => 'required|exists:employee,finger_id',
            '*.2' => 'required',
            '*.3' => 'required',
            // '*.2' => 'required|date_format:d-m-Y',
            '*.4' => 'required|string|max:255',

        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Sr.No is required',
            '1.required' => 'Employee ID field is required',
            '1.exists' => 'Employee ID is not exists',
            '2.required' => 'From Date field is required',
            '3.required' => 'To Date field is required',
            // '2.date_format' => 'Date format should be d-m-Y',
            '4.required' => 'Comment field is required',
        ];
    }

    public function model(array $row)
    {

        $fdate = "0000-00-00";
        $tdate = "0000-00-00";

        if ($row[2]) {
            try {
                $fdate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('Y-m-d');
            } catch (\Throwable $th) {
                $fdate = date('Y-m-d', strtotime($row[2]));
            }
        }

        if ($row[3]) {
            try {
                $tdate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[3])->format('Y-m-d');
            } catch (\Throwable $th) {
                $tdate = date('Y-m-d', strtotime($row[3]));
            }
        }
        //copy('foo/test.php', 'bar/test.php');

        $employee = Employee::where('finger_id', $row[1])->first();
        $holiday = CompanyHoliday::where('employee_id', $employee->employee_id)->whereRaw('fdate = "' . $fdate . '" and tdate = "' . $tdate . '"')->first();
        if (!$holiday) {
            CompanyHoliday::create([
                'employee_id' => $employee->employee_id,
                'fdate' => $fdate,
                'tdate' => $tdate,
                'comment' => $row[4],
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);
        } else {
            $holiday->update([
                'fdate' => $fdate,
                'tdate' => $tdate,
                'comment' => $row[4],
                'updated_by' => auth()->user()->user_id,
            ]);
        }

    }

    public function startRow(): int
    {
        return 2;
    }

}