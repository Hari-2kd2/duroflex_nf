<?php

namespace App\Http\Controllers\Attendance;

use App\Exports\ShiftFormatExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShiftDetailRequest;
use App\Imports\EmployeeShiftImport;
use App\Lib\Enumerations\UserStatus;
use App\Model\Employee;
use App\Model\EmployeeShift;
use App\Model\WorkShift;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ShiftDetailsController extends Controller
{

    public function index(Request $request)
    {
        $results = [];
        $shift = [];

        $workShift = WorkShift::all();

        foreach ($workShift as $key => $value) {
            $shift[$value->work_shift_id] = $value->shift_name;
        }

        if ($_POST) {
            $results = EmployeeShift::with('updated_user')->where('month', $request->yearAndMonth)->orderBy('employee_shift_id', 'desc')->get();
        }

        return view('admin.attendance.employeeShift.index', ['results' => $results, 'month' => $request->month, 'yearAndMonth' => $request->yearAndMonth, 'shift' => $shift]);
    }

    public function import(ShiftDetailRequest $request)
    {

        try {
            $file = $request->file('select_file');

            $validation = [
                '*.0' => 'required',
                '*.1' => 'required|exists:employee,finger_id',
                '*.2' => 'required',
            ];

            $message = [
                '0.required' => 'Sr.No is required',
                '1.required' => 'Employee ID field is required',
                '1.exists' => 'Employee ID is not exists',
                '2.required' => 'Month field is required',
            ];

            $shift = [];

            $workShift = WorkShift::all();

            foreach ($workShift as $key => $value) {
                $shift[$value->shift_name] = $value->work_shift_id;
            }

            $month = findMonthToAllDate($request->month);

            foreach ($month as $key => $value) {
                $cell = ($key + 3);
                $validation["*." . $cell . ""] = 'nullable|exists:work_shift,shift_name';
                $message[$cell . ".exists"] = 'WorkShift Name Does not Exists';
            }

            Excel::import(new EmployeeShiftImport($month, $validation, $message, $shift), $file);

            return back()->with('success', 'Employee Shift information saved successfully.');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $import = new EmployeeShiftImport($month, $validation, $message, $shift);
            $import->import($file);

            foreach ($import->failures() as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }

        }

        return back();

    }

    public function employeeShiftTemplate()
    {
        $file_name = 'templates/EmployeeShiftDetails.xlsx';
        $file = Storage::disk('public')->get($file_name);
        return (new Response($file, 200))
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function export(Request $request)
    {

        $employees = Employee::where('status', UserStatus::$ACTIVE)->get();
        // dd($employees);

        $extraData = [];
        $dataSet = [];
        $employeeData = [];
        $shiftDataset = [];
        $shiftName = [];
        $cellRange = [];
        $sundays = [];
        $holidays = [];
        $ph = [];
        $inc = 1;
        $workShiftDataset = [];
        $primaryHead = ['Sl.No', 'Employee ID', 'Month YYYY-MM'];
        $data = findMonthToAllDate($request->month);
        $workShift = WorkShift::all();
        $countDays = count($data);
        $start_date = $request->month . '-01';
        $end_date = date("Y-m-t", strtotime($start_date));

        $publicHolidays = DB::select(DB::raw('call SP_getHoliday("' . $start_date . '","' . $end_date . '")'));

        foreach ($workShift as $key => $value) {
            $shiftDataset[$value->work_shift_id] = $value->shift_name;
            $shiftName[] = $value->shift_name;
        }

        foreach ($data as $key => $value) {

            array_push($primaryHead, (int) $value['day']);
            $ifPublicHoliday = $this->ifPublicHoliday($publicHolidays, $value['date']);

            if ($value['day_name'] == "Sun") {
                $sundays[(int) $value['day']] = true;
                $holidays[(int) $value['day']] = $this->cellRange($countDays)[$key];
            } else {
                $sundays[(int) $value['day']] = false;
            }

            if ($ifPublicHoliday) {
                $ph[(int) $value['day']] = $this->cellRange($countDays)[$key];
            }
        }

        foreach ($employees as $key => $Data) {

            $shiftData = [];

            $employeeData = [
                $inc,
                $Data->finger_id,
                $request->month,
            ];

            foreach ($data as $key => $value) {

                array_push($shiftData, null);
            }

            $dataSet[] = array_merge($employeeData, $shiftData);

            $inc++;
        }

        $heading = [$primaryHead];

        $extraData['heading'] = $heading;
        $extraData['shiftName'] = $shiftName;
        $extraData['holidays'] = $holidays;
        $extraData['ph'] = $ph;
        $extraData['cellRange'] = $this->cellRange($countDays);

        $filename = 'EmployeeShiftInformation-' . DATE('dmYHis') . '.xlsx';

        return Excel::download(new ShiftFormatExport($dataSet, $extraData), $filename);

    }

    public function download(Request $request)
    {
        $employees = Employee::where('status', UserStatus::$ACTIVE)->get();
        // dd($request->all());
        $extraData = [];
        $dataSet = [];
        $employeeData = [];
        $shiftDataset = [];
        $shiftName = [];
        $sundays = [];
        $holidays = [];
        $ph = [];
        $inc = 1;
        $primaryHead = ['Sl.No', 'Employee ID', 'Month YYYY-MM'];
        $data = findMonthToAllDate($request->yearAndMonth);
        $workShift = WorkShift::all();
        $countDays = count($data);
        $start_date = $request->yearAndMonth . '-01';
        $end_date = date("Y-m-t", strtotime($start_date));
        $publicHolidays = DB::select(DB::raw('call SP_getHoliday("' . $start_date . '","' . $end_date . '")'));

        foreach ($workShift as $key => $value) {
            $shiftDataset[$value->work_shift_id] = $value->shift_name;
            $shiftName[] = $value->shift_name;
        }

        foreach ($data as $key => $value) {

            array_push($primaryHead, (int) $value['day']);
            $ifPublicHoliday = $this->ifPublicHoliday($publicHolidays, $value['date']);

            if ($value['day_name'] == "Sun") {
                $sundays[(int) $value['day']] = true;
                $holidays[(int) $value['day']] = $this->cellRange($countDays)[$key];
            } else {
                $sundays[(int) $value['day']] = false;
            }

            if ($ifPublicHoliday) {
                $ph[(int) $value['day']] = $this->cellRange($countDays)[$key];
            }
        }

        foreach ($employees as $key => $Data) {

            $shiftData = [];
            $employeeShift = EmployeeShift::where('finger_print_id', $Data->finger_id)->where('month', $request->yearAndMonth)->first();

            $employeeData = [
                $inc,
                $Data->finger_id,
                $request->yearAndMonth,
            ];

            if ($employeeShift) {
                foreach ($data as $key => $value) {
                    $column = 'd_' . (int) $value['day'];
                    if ($employeeShift->$column) {
                        array_push($shiftData, $shiftDataset[$employeeShift->$column]);
                    } else {
                        array_push($shiftData, null);
                    }
                }
            } else {
                foreach ($data as $key => $value) {
                    array_push($shiftData, null);
                }
            }

            $dataSet[] = array_merge($employeeData, $shiftData);

            $inc++;
        }

        $heading = [$primaryHead];

        $extraData['heading'] = $heading;
        $extraData['shiftName'] = $shiftName;
        $extraData['holidays'] = $holidays;
        $extraData['ph'] = $ph;
        $extraData['cellRange'] = $this->cellRange($countDays);
        $filename = 'EmployeeShiftInformation-' . DATE('dmYHis') . '.xlsx';

        return Excel::download(new ShiftFormatExport($dataSet, $extraData), $filename);

    }

    public function cellRange($countDays)
    {
        $cellRange = ["D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE"];

        if ($countDays == 31) {
            $cellRange = array_merge($cellRange, ["AF", "AG", "AH"]);
        } elseif ($countDays == 30) {
            $cellRange = array_merge($cellRange, ["AF", "AG"]);
        } elseif ($countDays == 29) {
            $cellRange = array_merge($cellRange, ["AF"]);
        }

        return $cellRange;
    }

    public function ifPublicHoliday($govtHolidays, $date)
    {
        $govt_holidays = [];

        foreach ($govtHolidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $govt_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($govt_holidays as $val) {
            // dump('Holiday -'.$val);
            // dump('date -'.$date);
            if ($val == $date) {
                return true;
            }
        }
        return false;
    }

}
