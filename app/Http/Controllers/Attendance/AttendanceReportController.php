<?php

namespace App\Http\Controllers\Attendance;

use App\Exports\AttendanceMusterReportExport;
use App\Exports\ExcelExportFromView;
use App\Exports\MonthlyAttendanceReportExport;
use App\Exports\MusterAttendanceReportExport;
use App\Exports\SummaryAttendanceReportExport;
use App\Http\Controllers\Controller;
// use Barryvdh\DomPDF\Facade as PDF;
use App\Lib\Enumerations\UserStatus;
use App\Model\Branch;
use App\Model\Department;
use App\Model\Employee;
use App\Model\LeaveType;
use App\Model\PrintHeadSetting;
use App\Repositories\AttendanceRepository;
use App\Repositories\CommonRepository;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{

    protected $attendanceRepository;
    protected $commonRepository;

    public function __construct(AttendanceRepository $attendanceRepository, CommonRepository $commonRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->commonRepository = $commonRepository;
    }

    public function dailyAttendance(Request $request)
    {

        \set_time_limit(0);

        $departmentList = Department::get();
        $branchList = Branch::get();
        $results = [];
        if ($_POST) {
            $results = $this->attendanceRepository->getEmployeeDailyAttendance(dateConvertFormtoDB($request->date), $request->department_id, $request->branch_id, $request->attendance_status);
        }
        // dd(dateConvertFormtoDB($request->date));
        return view('admin.attendance.report.dailyAttendance', ['results' => $results, 'branchList' => $branchList, 'departmentList' => $departmentList, 'date' => $request->date, 'branch_id' => $request->branch_id, 'department_id' => $request->department_id, 'attendance_status' => $request->attendance_status]);
    }

    public function monthlyAttendance(Request $request)
    {
        \set_time_limit(0);

        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->get();
        // dd($interval->h, $interval->i, $totTime);
        $results = [];
        if ($_POST) {
            $results = $this->attendanceRepository->getEmployeeMonthlyAttendance(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), $request->employee_id);
        }
        // dd($results);
        return view('admin.attendance.report.monthlyAttendance', ['results' => $results, 'employeeList' => $employeeList, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'employee_id' => $request->employee_id]);
    }

    public function myAttendanceReport(Request $request)
    {
        \set_time_limit(0);

        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->where('employee_id', session('logged_session_data.employee_id'))->get();
        $results = [];
        if ($_POST) {
            $results = $this->attendanceRepository->getEmployeeMonthlyAttendance(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), session('logged_session_data.employee_id'));
        } else {
            $results = $this->attendanceRepository->getEmployeeMonthlyAttendance(date('Y-m-01'), date("Y-m-t", strtotime(date('Y-m-01'))), session('logged_session_data.employee_id'));
        }

        return view('admin.attendance.report.mySummaryReport', ['results' => $results, 'employeeList' => $employeeList, 'from_date' => $request->from_date, 'to_date' => $request->to_date, 'employee_id' => $request->employee_id]);
    }

    public function attendanceMusterReport(Request $request)
    {
        \set_time_limit(0);

        if ($request->from_date && $request->to_date) {
            // dd($request->all());
            $month_from = date('Y-m', strtotime($request->from_date));
            $month_to = date('Y-m', strtotime($request->to_date));
            $start_date = dateConvertFormtoDB($request->from_date);
            $end_date = dateConvertFormtoDB($request->to_date);
        } else {
            $month_from = date('Y-m');
            $month_to = date('Y-m');
            $start_date = $month_from . '-01';
            $end_date = date("Y-m-t", strtotime($start_date));
        }

        $departmentList = Department::get();
        $employeeList = Employee::with('department', 'branch', 'designation')->where('status', UserStatus::$ACTIVE)->get();
        $branchList = Branch::get();

        $monthAndYearFrom = explode('-', $month_from);
        $monthAndYearTo = explode('-', $month_to);

        $month_data_from = $monthAndYearFrom[1];
        $month_data_to = $monthAndYearTo[1];
        $dateObjFrom = DateTime::createFromFormat('!m', $month_data_from);
        $dateObjTo = DateTime::createFromFormat('!m', $month_data_to);
        $monthNameFrom = $dateObjFrom->format('F');
        $monthNameTo = $dateObjTo->format('F');

        $employeeInfo = Employee::with('department', 'branch', 'designation')->where('status', UserStatus::$ACTIVE)->where('employee_id', $request->employee_id)->first();

        $monthToDate = findMonthFromToDate($start_date, $end_date);

        if ($request->from_date && $request->to_date) {
            $result = $this->attendanceRepository->findAttendanceMusterReport($start_date, $end_date, $request->employee_id, $request->department_id, $request->branch_id);
        } else {
            $result = [];
        }

        return view('admin.attendance.report.musterReport', [
            'departmentList' => $departmentList, 'employeeInfo' => $employeeInfo, 'employeeList' => $employeeList, 'branchList' => $branchList,
            'results' => $result, 'monthToDate' => $monthToDate, 'month_from' => $month_from, 'month_to' => $month_to, 'monthNameFrom' => $monthNameFrom,
            'monthNameTo' => $monthNameTo, 'department_id' => $request->department_id, 'employee_id' => $request->employee_id, 'branch_id' => $request->branch_id,
            'from_date' => $request->from_date, 'to_date' => $request->to_date, 'monthAndYearFrom' => $monthAndYearFrom, 'monthAndYearTo' => $monthAndYearTo,
            'start_date' => $start_date, 'end_date' => $end_date,
        ]);
    }

    public function attendanceSummaryReport(Request $request)
    {
        \set_time_limit(0);
        \ini_set('memory_limit', '1024M');

        if ($request->from_date && $request->to_date) {
            $from_date = $request->from_date;
            $to_date = $request->to_date;
        } else {
            $from_date = date("01/m/Y");
            $to_date = date("t/m/Y");
        }
        $result = [];
        $branch_id = $request->branch_id;
        $branchList = Branch::get();

        $month = date('Y-m', strtotime(DateConvertFormToDB($to_date)));
        $monthAndYear = explode('-', $month);
        $month_data = $monthAndYear[1];
        $dateObj = DateTime::createFromFormat('!m', $month_data);
        $monthName = $dateObj->format('F');

        $monthToDate = findMonthFromToDate(DateConvertFormToDB($from_date), DateConvertFormToDB($to_date));
        $leaveType = LeaveType::get();

        if ($_POST) {
            $result = $this->attendanceRepository->findAttendanceSummaryReport(DateConvertFormToDB($from_date), DateConvertFormToDB($to_date), $branch_id);
        }
        // dd($request->all());
        return view('admin.attendance.report.summaryReport', ['results' => $result, 'branchList' => $branchList, 'monthToDate' => $monthToDate, 'month' => $month, 'branch_id' => $branch_id, 'from_date' => $from_date, 'to_date' => $to_date, 'leaveTypes' => $leaveType, 'monthName' => $monthName]);
    }

    public function monthlyExcel(Request $request)
    {
        \set_time_limit(0);

        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->get();
        $employeeInfo = Employee::with('department')->where('employee_id', $request->employee_id)->first();
        $printHead = PrintHeadSetting::first();
        $results = [];

        if ($request->from_date && $request->to_date && $request->employee_id) {
            $results = $this->attendanceRepository->getEmployeeMonthlyAttendance(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date), $request->employee_id);
        }

        $excel = new MonthlyAttendanceReportExport('admin.attendance.report.monthlyAttendancePagination', [
            'printHead' => $printHead, 'employeeInfo' => $employeeInfo, 'results' => $results, 'employeeList' => $employeeList,
            'from_date' => $request->from_date, 'to_date' => $request->to_date, 'employee_id' => $request->employee_id,
            'employee_name' => $employeeInfo->first_name . ' ' . $employeeInfo->last_name,
            'department_name' => $employeeInfo->department->department_name,
        ]);

        $excelFile = Excel::download($excel, 'MonthlyReport-' . date('Ym', strtotime($request->from_date)) . date('Hi') . '.xlsx');

        return $excelFile;
    }
    public function summaryExcel(Request $request)
    {
        \set_time_limit(0);
        // dd($request->all());

        $from_date = dateConvertFormtoDB($request->from_date);
        $to_date = dateConvertFormtoDB($request->to_date);
        $branch_id = $request->branch_id;
        $month = date('Y-m', strtotime($to_date));
        $monthToDate = findMonthFromToDate($from_date, $to_date);
        $leaveType = LeaveType::get();
        $result = $this->attendanceRepository->findAttendanceSummaryReport($from_date, $to_date, $branch_id);
        $employeeInfo = Employee::with('department')->where('employee_id', $request->employee_id)->where('status', UserStatus::$ACTIVE)->first();
        $monthAndYear = explode('-', $month);
        $month_data = $monthAndYear[1];
        $dateObj = DateTime::createFromFormat('!m', $month_data);
        $monthName = $dateObj->format('F');

        $data = [
            'results' => $result,
            'month' => $month,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'monthToDate' => $monthToDate,
            'leaveTypes' => $leaveType,
            'monthName' => $monthName,
        ];

        $excel = new SummaryAttendanceReportExport('admin.attendance.report.summaryReportPagination', $data);

        $excelFile = Excel::download($excel, 'MusterReport-' . date('Ym', strtotime($month)) . date('Hi') . '.xlsx');

        return $excelFile;
    }

    public function musterExcel(Request $request)
    {
        \set_time_limit(0);

        if ($request->from_date && $request->to_date) {
            $month_from = date('Y-m', strtotime($request->from_date));
            $month_to = date('Y-m', strtotime($request->to_date));
            $start_date = dateConvertFormtoDB($request->from_date);
            $end_date = dateConvertFormtoDB($request->to_date);
        } else {
            $month_from = date('Y-m');
            $month_to = date('Y-m');
            $start_date = $month_from . '-01';
            $end_date = date("Y-m-t", strtotime($start_date));
        }

        $departmentList = Department::get();
        $employeeList = Employee::with('department', 'branch', 'designation')->where('status', UserStatus::$ACTIVE)->get();
        $branchList = Branch::get();

        $monthAndYearFrom = explode('-', $month_from);
        $monthAndYearTo = explode('-', $month_to);

        $month_data_from = $monthAndYearFrom[1];
        $month_data_to = $monthAndYearTo[1];
        $dateObjFrom = DateTime::createFromFormat('!m', $month_data_from);
        $dateObjTo = DateTime::createFromFormat('!m', $month_data_to);
        $monthNameFrom = $dateObjFrom->format('F');
        $monthNameTo = $dateObjTo->format('F');

        $employeeInfo = Employee::with('department', 'branch', 'designation')->where('status', UserStatus::$ACTIVE)->where('employee_id', $request->employee_id)->first();

        $monthToDate = findMonthFromToDate($start_date, $end_date);

        $result = $this->attendanceRepository->findAttendanceMusterReport($start_date, $end_date, $request->employee_id, $request->department_id, $request->branch_id);

        $data = [
            'departmentList' => $departmentList,
            'employeeInfo' => $employeeInfo,
            'employeeList' => $employeeList,
            'branchList' => $branchList,
            'results' => $result,
            'month_from' => $month_from,
            'month_to' => $month_to,
            'monthNameFrom' => $monthNameFrom,
            'monthNameTo' => $monthNameTo,
            'department_id' => $request->department_id,
            'employee_id' => $request->employee_id,
            'branch_id' => $request->branch_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'monthAndYearFrom' => $monthAndYearFrom,
            'monthAndYearTo' => $monthAndYearTo,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'monthToDate' => $monthToDate,
        ];

        $excel = new MusterAttendanceReportExport('admin.attendance.report.musterReportPagination', $data);

        $excelFile = Excel::download($excel, 'MusterReport-' . date('Ym', strtotime($month_from)) . date('Hi') . '.xlsx');

        return $excelFile;
    }

    public function dailyExcel(Request $request)
    {
        \set_time_limit(0);
        \ini_set('memory_limit', '512M');

        $departmentList = Department::where('department_id', $request->department_id)->first();
        $branchList = Branch::where('branch_id', $request->branch_id)->first();
        $results = $this->attendanceRepository->getEmployeeDailyAttendance($request->date, $request->department_id, $request->branch_id, $request->attendance_status);

        $data = [
            'results' => $results,
            'date' => $request->date,
            'branch_id' => isset($branchList->branch_id) ? $branchList->branch_id : '',
            'branch_name' => isset($branchList->branch_name) ? $branchList->branch_name : '',
            'department_id' => isset($departmentList->department_id) ? $departmentList->department_id : '',
            'department_name' => isset($departmentList->department_name) ? $departmentList->department_name : '',

        ];

        $excel = new ExcelExportFromView('admin.attendance.report.dailyReportPagination', $data);

        $excelFile = Excel::download($excel, 'DailyReport-' . date('Ymd', strtotime(dateConvertFormtoDB($request->date))) . date('Hi') . '.xlsx');

        return $excelFile;
    }

    public function attendanceRecord(Request $request)
    {
        \set_time_limit(0);

        $results = [];

        if ($_POST) {

            $fdate = Carbon::createFromFormat('d/m/Y', $request->fdate)->format('Y-m-d 00:00:01');
            $tdate = Carbon::createFromFormat('d/m/Y', $request->tdate)->format('Y-m-d 23:59:59');

            if ($request->fdate && $request->tdate) {
                $qry = 'ms_sql.datetime >= "' . $fdate . '" and ms_sql.datetime <= "' . $tdate . '"';
            }

            $results = DB::table('ms_sql')->leftjoin('employee', 'employee.finger_id', '=', 'ms_sql.ID')->whereRaw($qry)
                ->select('ms_sql.datetime', 'ms_sql.ID', 'ms_sql.updated_at', DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'))->get();

        }
        return \view('admin.attendance.report.attendanceRecord', ['results' => $results, 'device_name' => $request->device_name, 'fdate' => $request->fdate, 'tdate' => $request->tdate, 'employee_id ' => $request->employee_id]);
    }

    public function musterExcelExportFromCollection(Request $request)
    {
        \set_time_limit(0);
        \ini_set('memory_limit', '512M');

        if ($request->from_date && $request->to_date) {
            $month_from = date('Y-m', strtotime($request->from_date));
            $month_to = date('Y-m', strtotime($request->to_date));
            $start_date = dateConvertFormtoDB($request->from_date);
            $end_date = dateConvertFormtoDB($request->to_date);
        } else {
            $month_from = date('Y-m');
            $month_to = date('Y-m');
            $start_date = $month_from . '-01';
            $end_date = date("Y-m-t", strtotime($start_date));
        }

        $departmentList = Department::get();
        $employeeList = Employee::with('department', 'branch', 'designation')->where('status', UserStatus::$ACTIVE)->get();
        $branchList = Branch::get();

        $monthAndYearFrom = explode('-', $month_from);
        $monthAndYearTo = explode('-', $month_to);

        $month_data_from = $monthAndYearFrom[1];
        $month_data_to = $monthAndYearTo[1];
        $dateObjFrom = DateTime::createFromFormat('!m', $month_data_from);
        $dateObjTo = DateTime::createFromFormat('!m', $month_data_to);
        $monthNameFrom = $dateObjFrom->format('F');
        $monthNameTo = $dateObjTo->format('F');

        $employeeInfo = Employee::with('department', 'branch', 'designation')->where('status', UserStatus::$ACTIVE)->where('employee_id', $request->employee_id)->first();

        $monthToDate = findMonthFromToDate($start_date, $end_date);
        //dd($monthToDate);
        $dataset = $this->attendanceRepository->findAttendanceMusterReportExcelDump($start_date, $end_date, $request->employee_id, $request->department_id, $request->branch_id);
        // dd($result);

        $inner_head = ['Sl.No', 'CONTRACTOR', 'EMPLOYEE ID', 'EMPLOYEE NAME', 'DEPARTMENT', 'IN/OUT/SHIFT'];
        foreach ($monthToDate as $Day) {
            $inner_head[] = $Day['day'];
        }

        $heading = [
            [
                'Attendance Summary Report',
            ],
            $inner_head,
        ];

        $extraData = ['heading' => $heading];
        return Excel::download(new AttendanceMusterReportExport($dataset, $extraData), 'summaryReport' . date('Ymd', strtotime($request->date)) . date('His') . '.xlsx');
    }

    public function musterReportExcelFormat()
    {
        $extraData = [];
        $monthToDate = findMonthFromToDate('2023-01-01', '2023-01-10');
        $inner_head = ['Sr.No.', 'Contractor', 'Emp. ID', 'Student Name', 'Name', 'Department', 'In/Out/Shift'];
        foreach ($monthToDate as $Day) {
            $inner_head[] = $Day['day'];
        }

        $heading = [
            [
                'Attendance Summary Report',
            ],
            $inner_head,
        ];
        $extraData = ['heading' => $heading];

        $dataset = $this->attendanceRepository->findAttendanceMusterReport('2023-01-01', '2023-01-10', 'ADM1001', 2, 1);

        return Excel::download(new AttendanceMusterReportExport($dataset, $extraData), 'summaryReport' . date('Ymd', strtotime('2023-01-01')) . date('His') . '.xlsx');
    }
}
