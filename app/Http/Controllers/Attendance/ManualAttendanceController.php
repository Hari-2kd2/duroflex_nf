<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use App\Model\Employee;
use App\Model\EmployeeAttendance;
use App\Model\IpSetting;
use App\Model\ManualAttendance;
use App\Model\WhiteListedIp;
use App\Repositories\AttendanceRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request as RequestIP;

class ManualAttendanceController extends Controller
{
    protected $generateReportController;
    protected $attendanceRepository;

    public function __construct(GenerateReportController $generateReportController, AttendanceRepository $attendanceRepository)
    {
        $this->generateReportController = $generateReportController;
        $this->attendanceRepository = $attendanceRepository;
    }

    public function manualAttendance(Request $request)
    {
        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->orderBy('finger_id', 'ASC')->get();
        return view('admin.attendance.manualAttendance.index', ['employeeList' => $employeeList]);
    }

    public function filterData(Request $request)
    {
        $data = dateConvertFormtoDB($request->get('date'));
        $employee = $request->get('employee_id');
        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->get();

        if (session('logged_session_data.role_id') == 1 || session('logged_session_data.role_id') == 2) {
            $employeeList = Employee::where('status', UserStatus::$ACTIVE)->get();
        }

        $attendanceData = Employee::select('employee.finger_id', 'employee.employee_id',
            DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) as fullName'),
            DB::raw('(SELECT DATE_FORMAT(MIN(view_employee_in_out_data.in_time), \'%Y-%m-%d %H:%i:%s\')  FROM view_employee_in_out_data WHERE view_employee_in_out_data.date = "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id ) AS inTime'),
            DB::raw('(SELECT DATE_FORMAT(MAX(view_employee_in_out_data.out_time), \'%Y-%m-%d %H:%i:%s\') FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id ) AS outTime'),
            DB::raw('(SELECT view_employee_in_out_data.created_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS createdBy'),
            DB::raw('(SELECT view_employee_in_out_data.updated_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS updatedBy'),
            DB::raw('(SELECT view_employee_in_out_data.created_at FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS createdAt'),
            DB::raw('(SELECT view_employee_in_out_data.updated_at FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS updatedAt'),
            DB::raw('(SELECT view_employee_in_out_data.shift_name FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS shiftName'),
            DB::raw('(SELECT view_employee_in_out_data.working_time FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS workingTime'),
            DB::raw('(SELECT view_employee_in_out_data.over_time FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS overTime'),
            DB::raw('(SELECT view_employee_in_out_data.early_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS earlyBy'),
            DB::raw('(SELECT view_employee_in_out_data.late_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS lateBy'))
            ->where('employee.employee_id', $employee)
            ->where('employee.status', 1)
            ->get();

        return view('admin.attendance.manualAttendance.index', ['employeeList' => $employeeList, 'attendanceData' => $attendanceData]);
    }

    public function store(Request $request)
    {
        try {

            $data = dateConvertFormtoDB($request->get('date'));
            $employee = $request->get('employee_id');
            $start = date("Y-m-d H:i:s", strtotime($data . ' 06:30:00'));
            $end = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($data . ' 09:00:00')));
            $recompute = false;
            $manual = true;

            $emp = Employee::where('employee_id', $employee)->select('finger_id')->first();

            $result = json_decode(DB::table('manual_attendance')
                    ->where('ID', $emp->finger_id)
                    ->select('manual_attendance.primary_id')
                    ->whereRaw('manual_attendance.datetime >= "' . $start . '" AND manual_attendance.datetime <=  "' . $end . '"')
                    ->get()->toJson(), true);

            $delete = DB::table('manual_attendance')->whereIn('primary_id', array_values($result))->delete();

            foreach ($request->finger_print_id as $key => $finger_print_id) {

                if (isset($request->inTime[$key]) && isset($request->outTime[$key])) {

                    $InTime = date("Y-m-d H:i:s", strtotime($request->inTime[$key]));

                    $InData = [
                        'ID' => $finger_print_id,
                        'type' => 'IN',
                        'status' => 0,
                        'device_name' => 'Manual',
                        'devuid' => 'Manual',
                        'datetime' => date("Y-m-d H:i:s", strtotime($request->inTime[$key])),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'updated_by' => auth()->user()->user_id,
                        'created_by' => auth()->user()->user_id,
                    ];

                    ManualAttendance::insert($InData);

                    $OutTime = date("Y-m-d H:i:s", strtotime($request->outTime[$key]));

                    $outData = [
                        'ID' => $finger_print_id,
                        'type' => 'OUT',
                        'status' => 0,
                        'device_name' => 'Manual',
                        'devuid' => 'Manual',
                        'datetime' => date("Y-m-d H:i:s", strtotime($request->outTime[$key])),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'updated_by' => auth()->user()->user_id,
                        'created_by' => auth()->user()->user_id,
                    ];

                    ManualAttendance::insert($outData);

                    $this->generateReportController->generateManualAttendanceReport($finger_print_id, dateConvertFormtoDB($request->date), $InTime, $OutTime, $manual, $recompute);

                    return redirect('manualAttendance')->with('success', 'Manual attendance saved successfully. ');
                }
            }
        } catch (\Exception $e) {
            $bug = $e->getMessage();
            return redirect('manualAttendance')->with('error', 'Something Error Found !, Please try again. ' . $bug);
        }

    }

    // ip attendance
    public function ipAttendance(Request $request)
    {

        try {

            $finger_id = $request->finger_id;
            $ip_check_status = $request->ip_check_status;
            $user_ip = RequestIP::ip();

            if ($ip_check_status == 0) {
                $att = new EmployeeAttendance;
                $att->finger_print_id = $finger_id;
                $att->in_out_time = date("Y-m-d H:i:s");
                $att->save();

                return redirect()->back()->with('success', 'Attendance updated.');
            } else {
                $check_white_listed = WhiteListedIp::where('white_listed_ip', '=', $user_ip)->count();

                if ($check_white_listed > 0) {

                    $att = new EmployeeAttendance;
                    $att->finger_print_id = $finger_id;
                    $att->in_out_time = date("Y-m-d H:i:s");
                    $att->save();

                    return redirect()->back()->with('success', 'Attendance updated.');
                } else {
                    return redirect()->back()->with('error', 'Invalid Ip Address.');
                }
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    // get to attendance ip setting page

    public function setupDashboardAttendance()
    {
        $ip_setting = IpSetting::orderBy('updated_at', 'desc')->first();
        $white_listed_ip = WhiteListedIp::all();

        return view('admin.attendance.setting.dashboard_attendance', [
            'ip_setting' => $ip_setting,
            'white_listed_ip' => $white_listed_ip,
        ]);
    }

    // post new attendance

    public function postDashboardAttendance(Request $request)
    {

        try {

            DB::beginTransaction();

            $setting = IpSetting::orderBy('id', 'desc')->first();

            $setting->status = $request->status;
            $setting->ip_status = $request->ip_status;
            $setting->update();

            if ($request->ip) {

                WhiteListedIp::orderBy('id', 'desc')->delete();
                foreach ($request->ip as $value) {

                    if ($value != '') {

                        $white_listed_ip = new WhiteListedIp;

                        $white_listed_ip->white_listed_ip = $value;

                        $white_listed_ip->save();
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Employee Attendance Setting Updated');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
