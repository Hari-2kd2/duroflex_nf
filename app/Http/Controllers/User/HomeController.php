<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use App\Model\Branch;
use App\Model\CostCenter;
use App\Model\DailyCostToCompany;
use App\Model\Department;
use App\Model\Designation;
use App\Model\Employee;
use App\Model\EmployeeAttendance;
use App\Model\EmployeeAward;
use App\Model\EmployeeEducationQualification;
use App\Model\EmployeeExperience;
use App\Model\EmployeePerformance;
use App\Model\IpSetting;
use App\Model\LeaveApplication;
use App\Model\LeaveType;
use App\Model\MsSql;
use App\Model\Notice;
use App\Model\SubDepartment;
use App\Model\Termination;
use App\Model\Warning;
use App\Model\WorkShift;
use App\Repositories\AttendanceRepository;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{

    protected $employeePerformance, $leaveApplication, $notice, $employeeExperience, $branch, $costCenter, $unit, $designation, $department, $employee, $employeeAward, $attendanceRepository, $warning, $termination;

    public function __construct(
        EmployeePerformance $employeePerformance,
        LeaveApplication $leaveApplication,
        Notice $notice,
        EmployeeExperience $employeeExperience,
        Department $department,
        Designation $designation,
        SubDepartment $unit,
        CostCenter $costCenter,
        Branch $branch,
        Employee $employee,
        EmployeeAward $employeeAward,
        AttendanceRepository $attendanceRepository,
        Warning $warning,
        Termination $termination
    )
    {
        $this->employeePerformance = $employeePerformance;
        $this->leaveApplication = $leaveApplication;
        $this->notice = $notice;
        $this->employeeExperience = $employeeExperience;
        $this->department = $department;
        $this->designation = $designation;
        $this->unit = $unit;
        $this->costCenter = $costCenter;
        $this->branch = $branch;
        $this->employee = $employee;
        $this->employeeAward = $employeeAward;
        $this->attendanceRepository = $attendanceRepository;
        $this->warning = $warning;
        $this->termination = $termination;
    }

    public function index()
    {
        $ip_setting = IpSetting::orderBy('id', 'desc')->first();
        $ip_attendance_status = 0;
        $ip_check_status = 0;
        $login_employee = employeeInfo();
        $count_user_login_today = EmployeeAttendance::where('finger_print_id', '=', $login_employee[0]->finger_id)->whereDate('in_out_time', '=', date('Y-m-d'))->count();
        $last_log_date = MsSql::max('datetime');
        $setting_sync_live = DB::table('sync_to_live')->first();

        if ($ip_setting) {

            // if 0 then attendance will not take
            $ip_attendance_status = $ip_setting->status;

            // if 0 then ip will not checked for attendance

            $ip_check_status = $ip_setting->ip_status;
        }

        if (session('logged_session_data.role_id') != 1) {

            $attendanceData = $this->attendanceRepository->getEmployeeMonthlyAttendance(date("Y-m-01"), date("Y-m-d"), session('logged_session_data.employee_id'));

            $employeePerformance = $this->employeePerformance->select('employee_performance.*', DB::raw('AVG(employee_performance_details.rating) as rating'))
                ->with([
                    'employee' => function ($d) {
                        $d->with('department');
                    }
                ])
                ->join('employee_performance_details', 'employee_performance_details.employee_performance_id', '=', 'employee_performance.employee_performance_id')
                ->where('month', function ($query) {
                    $query->select(DB::raw('MAX(`month`) AS month'))->from('employee_performance');
                })->where('employee_performance.status', 1)->groupBy('employee_id')->get();

            $employeeTotalAward = $this->employeeAward
                ->select(DB::raw('count(*) as totalAward'))
                ->where('employee_id', session('logged_session_data.employee_id'))
                ->whereBetween('month', [date("Y-01"), date("Y-12")])
                ->first();

            $notice = $this->notice->with('createdBy')->orderBy('notice_id', 'DESC')->where('status', 'Published')->get();

            $terminationData = $this->termination->with('terminateBy')->where('terminate_to', session('logged_session_data.employee_id'))->first();

            $hasSupervisorWiseEmployee = $this->employee->select('employee_id')->where('supervisor_id', session('logged_session_data.employee_id'))->get()->toArray();

            if (count($hasSupervisorWiseEmployee) == 0) {
                $leaveApplication = [];
            } else {
                $leaveApplication = $this->leaveApplication->with(['employee', 'leaveType'])
                    ->whereIn('employee_id', array_values($hasSupervisorWiseEmployee))
                    ->where('status', 1)
                    ->orderBy('status', 'asc')
                    ->orderBy('leave_application_id', 'desc')
                    ->get();
            }

            $employeeInfo = $this->employee->with('designation')->where('employee_id', session('logged_session_data.employee_id'))->first();

            $employeeTotalLeave = $this->leaveApplication->select(DB::raw('IFNULL(SUM(number_of_day), 0) as totalNumberOfDays'))
                ->where('employee_id', session('logged_session_data.employee_id'))
                ->where('status', 2)
                ->whereBetween('approve_date', [date("Y-01-01"), date("Y-12-31")])
                ->first();

            $warning = $this->warning->with(['warningBy'])->where('warning_to', session('logged_session_data.employee_id'))->get();

            // date of birth in this month

            $firstDayThisMonth = date('Y-m-d');
            $lastDayThisMonth = date("Y-m-d", strtotime("+1 month", strtotime($firstDayThisMonth)));

            $from_date_explode = explode('-', $firstDayThisMonth);
            $from_day = $from_date_explode[2];
            $from_month = $from_date_explode[1];
            $concatFormDayAndMonth = $from_month . '-' . $from_day;

            $to_date_explode = explode('-', $lastDayThisMonth);
            $to_day = $to_date_explode[2];
            $to_month = $to_date_explode[1];
            $concatToDayAndMonth = $to_month . '-' . $to_day;

            $upcoming_birtday = Employee::orderBy('date_of_birth', 'desc')->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') >= '" . $concatFormDayAndMonth . "' AND DATE_FORMAT(date_of_birth, '%m-%d') <= '" . $concatToDayAndMonth . "' ")->get();

            $data = [
                'attendanceData' => $attendanceData,
                'employeePerformance' => $employeePerformance,
                'employeeTotalAward' => $employeeTotalAward,
                'notice' => $notice,
                'leaveApplication' => $leaveApplication,
                'employeeInfo' => $employeeInfo,
                'employeeTotalLeave' => $employeeTotalLeave,
                'warning' => $warning,
                'terminationData' => $terminationData,
                'upcoming_birtday' => $upcoming_birtday,
                'ip_attendance_status' => $ip_attendance_status,
                'ip_check_status' => $ip_check_status,
                'count_user_login_today' => $count_user_login_today,
                'last_log_date' => $last_log_date,
                'setting_sync_live' => $setting_sync_live,
            ];

            return view('admin.generalUserHome', $data);
        }

        // $hasSupervisorWiseEmployee = $this->employee->select('employee_id')->where('supervisor_id', session('logged_session_data.employee_id'))->get()->toArray();

        $hasSupervisorWiseEmployee = [];

        if (count($hasSupervisorWiseEmployee) == 0) {
            $leaveApplication = [];
        } else {

            // $leaveApplication = $this->leaveApplication->with(['employee', 'leaveType'])
            //     ->whereIn('employee_id', array_values($hasSupervisorWiseEmployee))
            //     ->where('status', 1)
            //     ->orderBy('status', 'asc')
            //     ->orderBy('leave_application_id', 'desc')
            //     ->get();

            $leaveApplication = [];
        }

        $date = date('Y-m-d');
        $last_day = DATE('Y-m-d', strtotime($date . " -1 day"));

        // $attendanceData = DB::select("call `SP_DailyAttendance`('" . $last_day . "')");

        $start_time = WorkShift::orderBy('start_time', 'ASC')->first()->start_time;
        $minTime = date('Y-m-d H:i:s', strtotime('-15 minutes', strtotime($start_time)));

        // $attendanceData = MsSql::where('datetime', '>=', $minTime)->join('employee', 'employee.finger_id', 'ms_sql.ID')
        //     ->orderBy('ms_sql.datetime', 'ASC')->groupBy('ID')->get();

        $attendanceData = MsSql::where('datetime', '>=', $minTime)->join('employee', 'employee.finger_id', 'ms_sql.ID')->join('department', 'department.department_id', '=', 'employee.department_id')
            ->orderBy('ms_sql.datetime', 'ASC')->groupBy('ID')->get();

        $count_user_login_today = count($attendanceData);

        // $dailyData = $this->employee->select('employee_id', 'first_name', 'finger_id')->where('supervisor_id', session('logged_session_data.employee_id'))->get();

        // $dailyAttendanceData = Employee::select("*")->where('employee.supervisor_id', session('logged_session_data.employee_id'))
        //     ->leftJoin('department', 'department.department_id', '=', 'employee.department_id')
        //     ->leftJoin('designation', 'designation.designation_id', '=', 'employee.designation_id')
        //     ->whereNotIn('finger_id', $dailyData)->get();

        $dailyAttendanceData = [];
        $totalEmployee = $this->employee->without('branch', 'department', 'designation', 'costcenter', 'subdepartment')->where('status', UserStatus::$ACTIVE)->count();

        $totalDepartment = $this->department->count();
        $totalDesignation = $this->designation->count();
        $totalUnit = $this->unit->count();
        $totalCostcenter = $this->costCenter->count();
        $totalContractor = $this->branch->count();

        // $employeePerformance = $this->employeePerformance->select('employee_performance.*', DB::raw('AVG(employee_performance_details.rating) as rating'))
        //     ->with(['employee' => function ($d) {
        //         $d->with('department');
        //     }])
        //     ->join('employee_performance_details', 'employee_performance_details.employee_performance_id', '=', 'employee_performance.employee_performance_id')
        //     ->where('month', function ($query) {
        //         $query->select(DB::raw('MAX(`month`) AS month'))->from('employee_performance');
        //     })->where('employee_performance.status', 1)->groupBy('employee_id')->get();

        // $employeeAward = $this->employeeAward->with(['employee' => function ($d) {
        //     $d->with('department');
        // }])->limit(10)->orderBy('employee_award_id', 'DESC')->get();

        $employeeAward = [];
        $employeePerformance = [];
        $dailyData = [];
        $notice = [];

        // $notice = $this->notice->with('createdBy')->orderBy('notice_id', 'DESC')->where('status', 'Published')->get();

        // date of birth in this month
        $firstDayThisMonth = date('Y-m-d');

        $lastDayThisMonth = date("Y-m-d", strtotime("+1 month", strtotime($firstDayThisMonth)));

        $from_date_explode = explode('-', $firstDayThisMonth);
        $from_day = $from_date_explode[2];
        $from_month = $from_date_explode[1];
        $concatFormDayAndMonth = $from_month . '-' . $from_day;

        $to_date_explode = explode('-', $lastDayThisMonth);
        $to_day = $to_date_explode[2];
        $to_month = $to_date_explode[1];
        $concatToDayAndMonth = $to_month . '-' . $to_day;

        $upcoming_birtday = Employee::without('branch', 'department', 'designation', 'costcenter', 'subdepartment')->orderBy('date_of_birth', 'desc')->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') >= '" . $concatFormDayAndMonth . "' AND DATE_FORMAT(date_of_birth, '%m-%d') <= '" . $concatToDayAndMonth . "' ")->get();

        $date = date('Y-m-d', strtotime('-1 days'));
        $ctc = DailyCostToCompany::where('date', $date)->first();

        $data = [
            'attendanceData' => $attendanceData,
            'totalEmployee' => $totalEmployee,
            'totalDepartment' => $totalDepartment,
            'totalDesignation' => $totalDesignation,
            'totalUnit' => $totalUnit,
            'totalCostcenter' => $totalCostcenter,
            'totalContractor' => $totalContractor,
            'totalAttendance' => $count_user_login_today,
            'totalAbsent' => $totalEmployee - $count_user_login_today,
            'employeePerformance' => $employeePerformance,
            'employeeAward' => $employeeAward,
            'notice' => $notice,
            'leaveApplication' => $leaveApplication,
            'upcoming_birtday' => $upcoming_birtday,
            'ip_attendance_status' => $ip_attendance_status,
            'ip_check_status' => $ip_check_status,
            'count_user_login_today' => $count_user_login_today,
            'dailyAttendanceData' => isset($dailyAttendanceData) ? $dailyAttendanceData : 0,
            'dailyData' => $dailyData,
            'last_log_date' => $last_log_date,
            'setting_sync_live' => $setting_sync_live,
            'ctc' => $ctc,

        ];

        return view('admin.adminhome', $data);
    }

    public function profile()
    {
        $employeeInfo = Employee::where('employee.employee_id', session('logged_session_data.employee_id'))->first();
        $employeeExperience = EmployeeExperience::where('employee_id', session('logged_session_data.employee_id'))->get();
        $employeeEducation = EmployeeEducationQualification::where('employee_id', session('logged_session_data.employee_id'))->get();

        return view('admin.user.user.profile', ['employeeInfo' => $employeeInfo, 'employeeExperience' => $employeeExperience, 'employeeEducation' => $employeeEducation]);
    }

    public function mail()
    {

        $user = array(
            'name' => "Learning Laravel",
        );

        Mail::send('emails.mailExample', $user, function ($message) {
            $message->to("kamrultouhidsak@gmail.com");
            $message->subject('E-Mail Example');
        });

        return "Your email has been sent successfully";
    }

    public function attendanceSummaryReport(Request $request)
    {

        $month = date("Y-m");
        $from_date = date("Y-m-01");
        $to_date = date("Y-m-t");

        $monthAndYear = explode('-', $month);
        $month_data = $monthAndYear[1];
        $dateObj = DateTime::createFromFormat('!m', $month_data);
        $monthName = $dateObj->format('F');

        $monthToDate = findMonthToAllDate($month);
        $leaveType = LeaveType::get();
        $result = $this->attendanceRepository->findAttendanceSummaryReport($from_date, $to_date);

        return ['results' => $result, 'monthToDate' => $monthToDate, 'month' => $month, 'leaveTypes' => $leaveType, 'monthName' => $monthName];
    }
}