<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Lib\Enumerations\OvertimeStatus;
use App\Lib\Enumerations\UserStatus;
use App\Model\Branch;
use App\Model\Department;
use App\Model\Employee;
use App\Model\EmployeeInOutData;
use App\Repositories\AttendanceRepository;
use App\Repositories\CommonRepository;
use DateTime;
use Illuminate\Http\Request;

class OvertimeApprovalController extends Controller
{

    protected $attendanceRepository;
    protected $commonRepository;

    public function __construct(AttendanceRepository $attendanceRepository, CommonRepository $commonRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->commonRepository = $commonRepository;
    }

    public function overtimeApproval(Request $request)
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

        // dd($month);
        // dd($result);
        // dd($monthToDate);

        return view('admin.payroll.setup.overtimeApproval', [
            'departmentList' => $departmentList, 'employeeInfo' => $employeeInfo, 'employeeList' => $employeeList, 'branchList' => $branchList,
            'results' => $result, 'monthToDate' => $monthToDate, 'month_from' => $month_from, 'month_to' => $month_to, 'monthNameFrom' => $monthNameFrom,
            'monthNameTo' => $monthNameTo, 'department_id' => $request->department_id, 'employee_id' => $request->employee_id, 'branch_id' => $request->branch_id,
            'from_date' => $request->from_date, 'to_date' => $request->to_date, 'monthAndYearFrom' => $monthAndYearFrom, 'monthAndYearTo' => $monthAndYearTo,
            'start_date' => $start_date, 'end_date' => $end_date,
        ]);
    }

    public function overtimeApproval1(Request $request)
    {
        \set_time_limit(0);
        // dd($request->all());
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

        $result = $this->attendanceRepository->findAttendanceMusterReport($start_date, $end_date, $request->employee_id, $request->department_id, $request->branch_id);

        $data = [
            'departmentList' => $departmentList, 'employeeInfo' => $employeeInfo, 'employeeList' => $employeeList, 'branchList' => $branchList,
            'results' => $result, 'monthToDate' => $monthToDate, 'month_from' => $month_from, 'month_to' => $month_to, 'monthNameFrom' => $monthNameFrom,
            'monthNameTo' => $monthNameTo, 'department_id' => $request->department_id, 'employee_id' => $request->employee_id, 'branch_id' => $request->branch_id,
            'from_date' => $request->from_date, 'to_date' => $request->to_date, 'monthAndYearFrom' => $monthAndYearFrom, 'monthAndYearTo' => $monthAndYearTo,
            'start_date' => $start_date, 'end_date' => $end_date,
        ];
        // dd($data);
        return view('admin.payroll.setup.overtimeApproval', $data);
    }

    public function changeOvertimeStatus(Request $request)
    {
        // dd($request->all());

        try {
            if ($request->approve_attendance) {
                $approve_attendance = $request->approve_attendance;
                foreach ($approve_attendance as $key => $employee_attendance_id) {
                    EmployeeInOutData::where('employee_attendance_id', $employee_attendance_id)->update(['over_time_status' => OvertimeStatus::$OT_FOUND_AND_APPROVED]);
                }
            }

            if ($request->reject_overtime) {
                $reject_overtime = $request->reject_overtime;
                foreach ($reject_overtime as $key => $employee_attendance_id) {
                    EmployeeInOutData::where('employee_attendance_id', $employee_attendance_id)->update(['over_time_status' => OvertimeStatus::$OT_DIS_APPROVED]);
                }
            }

            return redirect('overtimeApproval')->with('success', 'Overtime Approval Saved Successfully');

        } catch (\Throwable $th) {
            // dd($th->getMessage());
            return redirect('overtimeApproval')->with('error', 'Something Went Wrong! Try again.');
        }

    }
}
