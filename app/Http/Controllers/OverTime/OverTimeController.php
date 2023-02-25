<?php

namespace App\Http\Controllers\OverTime;

use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use App\Model\Employee;
use App\Model\LeaveType;
use App\Model\OvertimeRule;
use App\Model\PrintHeadSetting;
use App\Repositories\OverTimeRepository;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OverTimeController extends Controller
{

    protected $_overTimeRepository;

    public function __construct(OverTimeRepository $overTimeRepository)
    {
        $this->overTimeRepository = $overTimeRepository;
    }

    public function dailyOverTime(Request $request)
    {
        $results = [];
        if ($_POST) {
            $results = $this->overTimeRepository->getEmployeeDailyOverTime($request->date);
        }

        return view('admin.overtime.dailyOverTime', ['results' => $results, 'formData' => $request->date]);
    }

    public function monthlyOverTime(Request $request)
    {
        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->get();
        $results      = [];
        if ($_POST) {
            $results = $this->overTimeRepository->getEmployeeMonthlyOverTime(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), $request->employee_id);
        }
        return view('admin.overtime.monthlyOverTime', ['results' => $results, 'employeeList' => $employeeList, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'employee_id' => $request->employee_id]);
    }

    public function myOverTimeReport(Request $request)
    {
        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->where('employee_id', session('logged_session_data.employee_id'))->get();
        $results      = [];
        if ($_POST) {
            $results = $this->overTimeRepository->getEmployeeMonthlyOverTime(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), session('logged_session_data.employee_id'));
        } else {
            $results = $this->overTimeRepository->getEmployeeMonthlyOverTime(date('Y-m-01'), date("Y-m-t", strtotime(date('Y-m-01'))), session('logged_session_data.employee_id'));
        }

        return view('admin.overtime.myOverTimeReport', ['results' => $results, 'employeeList' => $employeeList, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'employee_id' => $request->employee_id]);
    }

    public function downloadDailyOverTime($date)
    {
        $printHead = PrintHeadSetting::first();

        $results = $this->overTimeRepository->getEmployeeDailyOverTime($date);

        $data = [
            'results'   => $results,
            'date'      => $date,
            'printHead' => $printHead,
        ];
        $pdf = PDF::loadView('admin.overtime.pdf.dailyOverTimePdf', $data);
        $pdf->setPaper('A4', 'landscape');
        $pageName = "daily-overtime.pdf";
        return $pdf->download($pageName);
    }

    public function downloadMonthlyOverTime(Request $request)
    {

        $employeeInfo = Employee::with('department')->where('employee_id', $request->employee_id)->first();
        $printHead    = PrintHeadSetting::first();
        $results      = $this->overTimeRepository->getEmployeeMonthlyOverTime(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), $request->employee_id);

        $data = [
            'results'         => $results,
            'form_date'       => dateConvertFormtoDB($request->from_date),
            'to_date'         => dateConvertFormtoDB($request->to_date),
            'printHead'       => $printHead,
            'employee_name'   => $employeeInfo->first_name . ' ' . $employeeInfo->last_name,
            'department_name' => $employeeInfo->department->department_name,
        ];

        $pdf = PDF::loadView('admin.overtime.pdf.monthlyOverTimePdf', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download("monthly-overtime.pdf");
    }

    public function downloadMyOverTime(Request $request)
    {
        $employeeInfo = Employee::with('department')->where('employee_id', $request->employee_id)->first();
        $printHead    = PrintHeadSetting::first();
        $results      = $this->overTimeRepository->getEmployeeMonthlyOverTime(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), $request->employee_id);
        $data         = [
            'results'         => $results,
            'form_date'       => dateConvertFormtoDB($request->from_date),
            'to_date'         => dateConvertFormtoDB($request->to_date),
            'printHead'       => $printHead,
            'employee_name'   => $employeeInfo->first_name . ' ' . $employeeInfo->last_name,
            'department_name' => $employeeInfo->department->department_name,
        ];

        $pdf = PDF::loadView('admin.overtime.pdf.myOverTimeReportPdf', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download("my-overtime.pdf");
    }

    public function overtimeSummaryReport(Request $request)
    {
        if ($request->month) {
            $month = $request->month;
        } else {
            $month = date("Y-m");
        }

        $monthAndYear = explode('-', $month);
        $month_data   = $monthAndYear[1];
        $dateObj      = DateTime::createFromFormat('!m', $month_data);
        $monthName    = $dateObj->format('F');
        $monthToDate  = findMonthToAllDate($month);
        $result       = $this->overTimeRepository->findOvertimeSummaryReport($month);

        return view('admin.overtime.overtimeSummaryReport', ['results' => $result, 'monthToDate' => $monthToDate, 'month' => $month, 'monthName' => $monthName]);
    }

    public function downloadOverTimeSummaryReport($month)
    {
        $printHead    = PrintHeadSetting::first();
        $monthToDate  = findMonthToAllDate($month);
        $leaveType    = LeaveType::get();
        $result       = $this->overTimeRepository->findOvertimeSummaryReport($month);
        $monthAndYear = explode('-', $month);
        $month_data   = $monthAndYear[1];
        $dateObj      = DateTime::createFromFormat('!m', $month_data);
        $monthName    = $dateObj->format('F');

        $data = [
            'results'     => $result,
            'month'       => $month,
            'printHead'   => $printHead,
            'monthToDate' => $monthToDate,
            'leaveTypes'  => $leaveType,
            'monthName'   => $monthName,
        ];
        $pdf = PDF::loadView('admin.overtime.pdf.OverTimeSummaryReportPdf', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download("overtime-summaryReport.pdf");
    }

    public function otSummaryReport(Request $request)
    {
        $check_ot_in  = DB::table('employee_attendence')->where('finger_id', $request->finger_id)->orderBy('in_out_time', 'ASC')->select('in_out_time')->first();
        $check_ot_out = DB::table('employee_attendence')->where('finger_id', $request->finger_id)->orderBy('in_out_time', 'DESC')->select('in_out_time')->first();
        $diff         = $check_ot_out - $check_ot_in;
        return $diff;
    }

    public function samp()
    {

        $results      = [];
        $employeeList = Employee::where('status', 1)->get();
        // $results[$employeeList->user_id][] = $employeeList;
        // $employeeList = Employee::where('status', 1)->get();
        return $employeeList;
        // $employees = DB::select("call `SP_getEmployeeInfo`('" . $employee_id . "')");
        // $employees  = Employee::where("date_of_joining", "<=", Carbon::now()->subMonths(24))->where('status', 1)->get();
        // $employees = Employee::join('designation', 'designation.designation_id', '=', 'employee.designation_id')
        //     ->join('department', 'department.department_id', '=', 'employee.department_id')
        //     ->where('status', 1)->orderBy('date_of_joining', 'asc')->get();

        $employees = Employee::select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'designation_name', 'department_name', 'date_of_joining', 'date_of_leaving', 'finger_id', 'employee_id', 'branch_name')
            ->join('designation', 'designation.designation_id', 'employee.designation_id')
            ->join('department', 'department.department_id', '=', 'employee.department_id')
            ->join('branch', 'branch.branch_id', '=', 'employee.branch_id')
            ->where('status', UserStatus::$ACTIVE)->where("date_of_joining", "<=", Carbon::now()->subMonths(24))->orderBy('date_of_joining', 'asc')->get();
        $dataFormat = [];
        $tempArray  = [];
        if (count($employees) > 0) {
            foreach ($employees as $employee) {
                $tempArray['date_of_joining']  = $employee->date_of_joining;
                $tempArray['date_of_leaving']  = $employee->date_of_leaving;
                $tempArray['employee_id']      = $employee->employee_id;
                $tempArray['designation_name'] = $employee->designation_name;
                $tempArray['fullName']         = $employee->fullName;
                $tempArray['phone']            = $employee->phone;
                $tempArray['finger_id']        = $employee->finger_id;
                $tempArray['department_name']  = $employee->department_name;
                $tempArray['branch_name']      = $employee->branch_name;

                // $date_of_joining = new DateTime($employee->date_of_joining);
                // $today_date      = Carbon::now();

                $dataFormat[$employee->employee_id][] = $tempArray;
            }
        } else {
            $tempArray['status'] = 'No Data Found';
            $dataFormat[]        = $tempArray['status'];
        }
        return $dataFormat;

        // foreach ($employees as $employee) {
        //     foreach ($data as $key => $value) {
        //         $tempArray['employee_id'] = $employee->employee_id;
        //         $tempArray['finger_id'] = $employee->finger_id;
        //         $tempArray['designation_name'] = $employee->designation_name;
        //         $tempArray['date'] = $value['date'];
        //         $tempArray['day'] = $value['day'];
        //         $tempArray['day_name'] = $value['day_name'];
        //         $expected_time = $this->getEmployeeDailyOverTime($value['date']);
        //         foreach ($expected_time as $t) {
        //             $tempArray['working_hour'] = $t->workingHour;
        //         }
        //         // return $id;
        //         $attendance = DB::table('view_employee_in_out_data')->select('working_time')->where('date', [$value['date']])->where('finger_print_id', [$employee->finger_id])->first();
        //         if ($attendance != null && $employee->finger_id == $t->finger_print_id) {
        //             $tempArray['status'] = 'true';
        //             $tempArray['working_time'] = $attendance->working_time;
        //         } else {
        //             $tempArray['status'] = false;
        //             $tempArray['working_time'] = '00.00';
        //         }
        //         $dataFormat[$employee->fullName][] = $tempArray;
        //     }

        // }

        // return $data[0]['day'];
        // return $employees;

    }

    public function samp1()
    {
        $month     = '2022-03';
        $data      = findMonthToAllDate($month);
        $employees = Employee::select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'designation_name', 'finger_id', 'employee_id')
            ->join('designation', 'designation.designation_id', 'employee.designation_id')
            ->where('status', UserStatus::$ACTIVE)->get();

        $start_date = $month . '-01';
        $end_date   = date("Y-m-t", strtotime($start_date));

        $dataFormat = [];
        $tempArray  = [];
        foreach ($employees as $employee) {
            foreach ($data as $key => $value) {
                $tempArray['employee_id']      = $employee->employee_id;
                $tempArray['finger_id']        = $employee->finger_id;
                $tempArray['designation_name'] = $employee->designation_name;
                $tempArray['date']             = $value['date'];
                $tempArray['day']              = $value['day'];
                $tempArray['day_name']         = $value['day_name'];
                $attendance                    = DB::table('view_employee_in_out_data')->select('working_time')->where('date', [$value['date']])->where('finger_print_id', [$employee->finger_id])->first();

                if (($attendance) != [] && $attendance->working_time > '08:30:00') {
                    $total_ot = DB::table('view_employee_in_out_data')->select('working_time')->where('finger_print_id', [$employee->finger_id])->whereBetween('date', [$start_date, $end_date])->pluck('working_time');
                    foreach ($total_ot as $ot) {
                        $overtime = Carbon::createFromFormat('H.i', '00.00')->addHours(intval($ot));
                        $tempArray['total_ot'] += $overtime;
                    }
                    $tempArray['status']       = 'true';
                    $tempArray['working_time'] = $attendance->working_time;

                } else {
                    $tempArray['status']       = false;
                    $tempArray['working_time'] = '00.00';
                }
                $dataFormat[$employee->fullName][] = $tempArray;
            }
        }
        return $dataFormat;

    }
    public function getEmployeeDailyOverTime($date = false)
    {
        if ($date) {
            $data = dateConvertFormtoDB($date);
        } else {
            $data = date("Y-m-d");
        }
        $queryResults = DB::select("call `SP_DailyOverTime`('" . $data . "')");
        $results      = [];
        foreach ($queryResults as $value) {
            $results[$value->department_name] = $value;
        }
        return $results;
    }

    public function overtimeRuleConfigure()
    {
        $data = OvertimeRule::first();
        return view('admin.overtime.setup.overtimeRuleConfigure', ['data' => $data]);
    }

    public function updateOvertimeRuleConfigure(Request $request)
    {
        $input = $request->all();
        $data  = OvertimeRule::findOrFail($request->overtime_rule_id);

        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = $e->errorInfo[1];
        }

        if ($bug == 0) {
            return "success";
        } else {
            return "error";
        }
    }
    

}
