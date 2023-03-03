<?php

namespace App\Http\Controllers\Attendance;

use App\Console\Commands\CalculateAttendance;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\AttendanceStatus;
use App\Lib\Enumerations\GeneralStatus;
use App\Lib\Enumerations\MandayStatus;
use App\Lib\Enumerations\PayrollConstant;
use App\Lib\Enumerations\UserStatus;
use App\Model\Employee;
use App\Model\EmployeeInOutData;
use App\Model\EmployeeShift;
use App\Model\WorkShift;
use App\Repositories\AttendanceRepository;
use App\Repositories\LeaveRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerateReportController extends Controller
{

    protected $leaveRepository;
    protected $attendanceRepository;

    public function __construct(LeaveRepository $leaveRepository, AttendanceRepository $attendanceRepository)
    {
        $this->leaveRepository = $leaveRepository;
        $this->attendanceRepository = $attendanceRepository;
    }

    public function generateManualAttendanceReport($finger_print_id, $date, $in_time = '', $out_time = '', $manual, $recompute)
    {
        \ob_start();
        \set_time_limit(0);
        info('Generate Manual Attendance Report.....................');
        $employee = Employee::status(UserStatus::$ACTIVE)->where('finger_id', $finger_print_id)->select('finger_id', 'employee_id')->first();
        ob_end_flush();

        return $this->calculate_attendance($employee->finger_id, $employee->employee_id, $date, $in_time, $out_time, $manual, $recompute);

    }

    public function regenerateAttendanceReport(Request $request)
    {

        try {

            \ob_start();
            \set_time_limit(0);
            ini_set('memory_limit', '3072M');
            $time_start = microtime(true);

            info('Calculate Attendance Report.....................');

            $datePeriod = CarbonPeriod::create(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date));

            Employee::select('finger_id', 'employee_id')->status(UserStatus::$ACTIVE)->chunk(5, function ($employeeData) use ($datePeriod) {
                foreach ($employeeData as $key => $employee) {
                    foreach ($datePeriod as $date) {
                        $date = $date->format('Y-m-d');
                        $this->calculate_attendance($employee->finger_id, $employee->employee_id, dateConvertFormtoDB($date), '', '', false, true);
                    }
                }
            });

            $bug = 0;

            $time_end = microtime(true);
            $execution_time_in_seconds = ($time_end - $time_start) . ' Seconds';

            info('Execution_time_in_seconds : ' . $execution_time_in_seconds);
            ob_end_flush();
            return redirect()->back()->with('success', 'Reports calculated Successfully');

        } catch (\Throwable $th) {
            $bug = $th->getMessage();
            info($bug);
            ob_end_flush();
            return redirect()->back()->with('error', 'Something went wrong, Please try again!');
        }

    }

    public function generateAttendanceReport($date)
    {
        \ob_start();
        \set_time_limit(0);
        info('Generate Attendance Report Scheduler.....................');

        $employeeData = Employee::status(UserStatus::$ACTIVE)->select('finger_id', 'employee_id')->get();

        foreach ($employeeData as $key => $employee) {
            $this->calculate_attendance($employee->finger_id, $employee->employee_id, $date, '', '', false, true);
        }

        ob_end_flush();
    }

    public function store($data_format, $employee_id, $manualAttendance, $recompute)
    {
        //insert employee attendance data to report table
        $if_exists = EmployeeInOutData::where('finger_print_id', $data_format['finger_print_id'])->where('date', $data_format['date'])->first();
        $if_manual_override_exists = EmployeeInOutData::where('finger_print_id', $data_format['finger_print_id'])->where('date', $data_format['date'])->where('device_name', 'Manual')->first();

        if (($recompute && !$if_manual_override_exists) || ($recompute == false && $manualAttendance)) {
            if ($data_format != []) {
                if (!$if_exists) {
                    EmployeeInOutData::insert($data_format);
                    return true;
                } else {
                    unset($data_format['created_by']);
                    unset($data_format['created_at']);
                    $if_exists->update($data_format);
                    $if_exists->save();
                    return true;
                }

            } else {

                $tempArray = [];

                $govtHolidays = DB::select(DB::raw('call SP_getHoliday("' . $data_format['date'] . '","' . $data_format['date'] . '")'));
                $companyHolidayDetails = DB::select(DB::raw('call SP_getCompanyHoliday("' . $data_format['date'] . '","' . $data_format['date'] . '","' . $employee_id . '")'));

                if ($data_format['date'] > date("Y-m-d")) {

                    $tempArray['attendance_status'] = AttendanceStatus::$FUTURE;
                    $tempArray['mandays'] = MandayStatus::$INVALID;

                } else {

                    $ifHoliday = $this->ifHoliday($govtHolidays, $data_format['date']);
                    $ifCompanyHoliday = $this->ifCompanyHoliday($companyHolidayDetails, $data_format['date']);

                    if ($ifHoliday) {
                        $tempArray['attendance_status'] = AttendanceStatus::$HOLIDAY;
                        $tempArray['mandays'] = MandayStatus::$PAID_HOLIDAY;
                    } elseif ($ifCompanyHoliday) {
                        $tempArray['attendance_status'] = AttendanceStatus::$HOLIDAY;
                        $tempArray['mandays'] = MandayStatus::$COMPANY_HOLIDAY;
                    } else {
                        $tempArray['attendance_status'] = AttendanceStatus::$ABSENT;
                        $tempArray['mandays'] = MandayStatus::$ABSENT;
                    }

                }

                if (!$if_exists) {
                    $data_format['attendance_status'] = $tempArray['attendance_status'];
                    EmployeeInOutData::insert($data_format);
                } else {
                    $data_format['attendance_status'] = $tempArray['attendance_status'];
                    $if_exists->update($data_format);
                    $if_exists->save();
                }
            }

        } else {
            info('Manual override skipped when calculating reports for an employee - ' . $data_format['finger_print_id'] . ' on ' . $data_format['date'] . '...........');
        }
    }

    public function calculate_attendance($finger_id, $employee_id, $date, $in_time = '', $out_time = '', $manualAttendance = false, $recompute = false)
    {

        $month = date('Y-m', strtotime($date));
        $dataSet = [];

        $day = 'd_' . (int) date('d', strtotime($date));

        $shift = EmployeeShift::where('finger_print_id', $finger_id)->where('month', $month)->first();

        if ($manualAttendance) {

            $dataSet = $this->manualAttendanceReport($in_time, $out_time, $date, $finger_id);

        } else {

            if ($shift && $shift->$day != null) {

                $dataSet = $this->shiftBasedReport($shift, $date, $month, $day, $finger_id);

            } else {

                $hasReport = EmployeeInOutData::where('finger_print_id', $finger_id)->whereDate('date', $date)->first();

                $start_time = WorkShift::orderBy('start_time', 'ASC')->first()->start_time;
                $minTime = date('Y-m-d H:i:s', strtotime('-15 minutes', strtotime($start_time)));

                $start_date = DATE('Y-m-d', strtotime($date)) . " " . date('H:i:s', strtotime('-15 minutes', strtotime($minTime)));
                $end_date = DATE('Y-m-d', strtotime($date . " +1 day")) . " 00:00:00";

                $fingerID = (object) ['finger_id' => $finger_id];

                $dataSet = $this->autoGenReport($start_date, $end_date, $fingerID, $hasReport ? true : false);
            }
        }

        return $this->store($dataSet, $employee_id, $manualAttendance, $recompute);
    }

    public function autoGenReport($date_from, $date_to, $finger_id, $reRun)
    {
        // info('Report auto Generation function.....................');

        \set_time_limit(0);
        $results = [];
        $dataSet = [];
        $attendance_data = [];

        if ($reRun) {
            $results = DB::table('ms_sql')
                ->whereRaw("datetime >= '" . $date_from . "' AND datetime <= '" . $date_to . "'")
                ->where('ID', $finger_id->finger_id)
                ->orderby('datetime', 'ASC')
                ->get();

        } else {
            $results = DB::table('ms_sql')
                ->whereRaw("datetime >= '" . $date_from . "' AND datetime <= '" . $date_to . "'")
                ->where('ID', $finger_id->finger_id)
                ->where('status', 0)
                ->orderby('datetime', 'ASC')
                ->get();
        }

        if (count($results) == 0) {

            $attendance_data['date'] = date('Y-m-d', strtotime($date_from));
            $attendance_data['finger_print_id'] = $finger_id->finger_id;
            $attendance_data['in_time'] = null;
            $attendance_data['out_time'] = null;
            $attendance_data['working_time'] = null;
            $attendance_data['working_hour'] = null;
            $attendance_data['device_name'] = null;
            $attendance_data['status'] = 1;
            $attendance_data['attendance_status'] = AttendanceStatus::$ABSENT;
            $attendance_data['mandays'] = MandayStatus::$ABSENT;
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            $attendance_data['updated_at'] = date('Y-m-d H:i:s');
            $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['in_out_time'] = null;

            $dataSet = $attendance_data;

        } elseif (count($results) == 1) {

            $attendance_data['date'] = date('Y-m-d', strtotime($date_from));
            $attendance_data['finger_print_id'] = $finger_id->finger_id;
            $attendance_data['in_time'] = date('Y-m-d H:i:s', strtotime($results[0]->datetime));
            $attendance_data['out_time'] = null;
            $attendance_data['working_time'] = null;
            $attendance_data['working_hour'] = null;
            $attendance_data['device_name'] = $results[0]->device_name;
            $attendance_data['status'] = 1;
            $attendance_data['attendance_status'] = AttendanceStatus::$ONETIMEINPUNCH;
            $attendance_data['mandays'] = MandayStatus::$INVALID;
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            $attendance_data['updated_at'] = date('Y-m-d H:i:s');
            $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['in_out_time'] = date('d/m/y H:i', strtotime($results[0]->datetime)) . ":" . ('IN');

            $dataSet = $attendance_data;

        } elseif (count($results) >= 2) {

            $attendance_data['date'] = date('Y-m-d', strtotime($date_from));
            $attendance_data['finger_print_id'] = $finger_id->finger_id;
            $attendance_data['in_time'] = date('Y-m-d H:i:s', strtotime($results[0]->datetime));
            $attendance_data['out_time'] = date('Y-m-d H:i:s', strtotime($results[count($results) - 1]->datetime));
            $attendance_data['working_time'] = $this->workingtime($results[0]->datetime, $results[count($results) - 1]->datetime);
            $attendance_data['working_hour'] = $this->workingtime($results[0]->datetime, $results[count($results) - 1]->datetime);
            $attendance_data['device_name'] = $results[0]->device_name;
            $attendance_data['status'] = 1;
            $explode = explode(':', $attendance_data['working_time']);
            $attendance_data['attendance_status'] = null;
            $attendance_data['mandays'] = null;
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            $attendance_data['updated_at'] = date('Y-m-d H:i:s');
            $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            // $attendance_data['in_out_time'] = date('d/m/y H:i', strtotime($results[0]->datetime)) . ":" . ('IN,') . ' ' . date('d/m/y H:i', strtotime($results[count($results) - 1]->datetime)) . ":" . ('OUT');
            $attendance_data['in_out_time'] = $this->in_out_time($results);

            $dataSet = $this->overtimeLateEarlyCalc($attendance_data);
        }

        return $dataSet;
    }

    public function manualAttendanceReport($fdatetime, $tdatetime, $date, $finger_id)
    {
        $attendance_data = [];
        $dataSet = [];
        $working_time = $this->workingtime($fdatetime, $tdatetime);
        $hour = explode(':', $working_time);

        $rawData = [
            'date' => date('Y-m-d', strtotime($date)),
            'finger_print_id' => $finger_id,
            'in_time' => date('Y-m-d H:i:s', strtotime($fdatetime)),
            'out_time' => date('Y-m-d H:i:s', strtotime($tdatetime)),
            'shift_name' => null,
            'work_shift_id' => null,
            'working_time' => $working_time,
            'working_hour' => null,
            'device_name' => 'Manual',
            'over_time' => $this->over_time($working_time, PayrollConstant::$NA_OVERTIME_HOUR),
            'attendance_status' => $hour[0] >= PayrollConstant::$NA_WORKING_HOUR ? AttendanceStatus::$PRESENT : AttendanceStatus::$LESSHOURS,
            'mandays' => MandayStatus::$INVALID,
            'in_out_time' => date('d/m/y H:i', strtotime($fdatetime)) . ":" . ('IN,') . ' ' . date('d/m/y H:i', strtotime($tdatetime)) . ":" . ('OUT'),
        ];

        $attendance_data = $this->reportDataFormat($rawData);

        $dataSet = $this->overtimeLateEarlyCalc($attendance_data);

        return $dataSet;
    }

    public function shiftBasedReport($shift, $date, $month, $day, $finger_id)
    {
        // info('Shift Based Report function.....................');

        $attendance_data = [];
        $dataSet = [];

        $dailyShiftData = WorkShift::where('work_shift_id', $shift->$day)->first();

        $shiftStartTime = $date . ' ' . $dailyShiftData->start_time;
        $shiftEndTime = $date . ' ' . $dailyShiftData->end_time;

        if ($dailyShiftData->start_time > $dailyShiftData->end_time) {
            $nature = 'Night';
            $fdatetime = date('Y-m-d H:i:s', strtotime('-1 hours', strtotime($shiftStartTime)));
            $tdatetime = date('Y-m-d H:i:s', strtotime('+1 days +4 hours', strtotime($shiftEndTime)));
        } else {
            $nature = 'Day';
            $fdatetime = date('Y-m-d H:i:s', strtotime('-1 hours', strtotime($shiftStartTime)));
            $tdatetime = date('Y-m-d H:i:s', strtotime('+4 hours', strtotime($shiftEndTime)));
        }

        $results = DB::table('ms_sql')->whereRaw("datetime >= '" . $fdatetime . "' AND datetime <= '" . $tdatetime . "'")
            ->where('ID', $finger_id)->get();

        if (count($results) == 1) {
            $inTime = DB::table('ms_sql')->whereRaw("datetime >= '" . $fdatetime . "' AND datetime <= '" . $tdatetime . "'")
                ->where('ID', $finger_id)->min('datetime');
        } else {
            $inTime = DB::table('ms_sql')->whereRaw("datetime >= '" . $fdatetime . "' AND datetime <= '" . $tdatetime . "'")
                ->where('ID', $finger_id)->min('datetime');
            $outTime = DB::table('ms_sql')->whereRaw("datetime >= '" . $fdatetime . "' AND datetime <= '" . $tdatetime . "'")
                ->where('ID', $finger_id)->max('datetime');
        }

        if ($inTime != null && isset($outTime)) {

            $working_time = $this->workingtime($inTime, $outTime);
            $over_time = $this->over_time($this->workingtime($inTime, $outTime), $this->workingtime($dailyShiftData->start_time, $dailyShiftData->end_time));
            $hour = explode(':', $working_time);

            $rawData = [
                'date' => date('Y-m-d', strtotime($date)),
                'finger_print_id' => $finger_id,
                'in_time' => date('Y-m-d H:i:s', strtotime($inTime)),
                'out_time' => date('Y-m-d H:i:s', strtotime($outTime)),
                'shift_name' => shiftList()[$shift->$day],
                'work_shift_id' => $shift->$day,
                'working_time' => $working_time,
                'working_hour' => null,
                'device_name' => null,
                'over_time' => $this->over_time($working_time, '08:00:00'),
                'attendance_status' => $hour[0] >= 8 ? AttendanceStatus::$PRESENT : AttendanceStatus::$LESSHOURS,
                'in_out_time' => date('d/m/y H:i', strtotime($inTime)) . ":" . 'IN' . ', ' . date('d/m/y H:i', strtotime($outTime)) . ":" . 'OUT',
            ];

            $attendance_data = $this->reportDataFormat($rawData);
            // $dataSet = $this->overtimeLateEarlyCalc($attendance_data, $shift->$day);
            $dataSet = $this->overtimeLateEarlyCalc($attendance_data);

        } elseif ($inTime != null) {

            $rawData = [
                'date' => date('Y-m-d', strtotime($date)),
                'finger_print_id' => $finger_id,
                'in_time' => date('Y-m-d H:i:s', strtotime($inTime)),
                'out_time' => null,
                'shift_name' => shiftList()[$shift->$day],
                'work_shift_id' => $shift->$day,
                'working_time' => null,
                'working_hour' => null,
                'device_name' => null,
                'over_time' => null,
                'attendance_status' => AttendanceStatus::$ONETIMEINPUNCH,
                'in_out_time' => date('d/m/y H:i', strtotime($inTime)) . ":" . 'IN',
            ];

            $dataSet = $this->reportDataFormat($rawData);

        } else {

            $rawData = [
                'date' => date('Y-m-d', strtotime($date)),
                'finger_print_id' => $finger_id,
                'in_time' => null,
                'out_time' => null,
                'shift_name' => shiftList()[$shift->$day],
                'work_shift_id' => $shift->$day,
                'working_time' => null,
                'working_hour' => null,
                'device_name' => null,
                'over_time' => null,
                'attendance_status' => AttendanceStatus::$ABSENT,
                'in_out_time' => null,
            ];

            $dataSet = $this->reportDataFormat($rawData);

        }

        return $dataSet;
    }

    public function reportDataFormat($data)
    {
        $attendance_data = [];
        $dataSet = [];

        $attendance_data['date'] = $data['date'];
        $attendance_data['finger_print_id'] = $data['finger_print_id'];
        $attendance_data['in_time'] = $data['in_time'];
        $attendance_data['out_time'] = $data['out_time'];
        $attendance_data['shift_name'] = $data['shift_name'];
        $attendance_data['work_shift_id'] = $data['work_shift_id'];
        $attendance_data['working_time'] = $data['working_time'];
        $attendance_data['working_hour'] = $data['working_hour'];
        $attendance_data['device_name'] = $data['device_name'];
        $attendance_data['over_time'] = $data['over_time'];
        $attendance_data['in_out_time'] = $data['in_out_time'];
        $attendance_data['attendance_status'] = $data['attendance_status'];
        $attendance_data['early_by'] = isset($data['early_by']) ? $data['early_by'] : null;
        $attendance_data['late_by'] = isset($data['late_by']) ? $data['late_by'] : null;
        $attendance_data['mandays'] = isset($data['mandays']) ? $data['mandays'] : null;
        $attendance_data['status'] = GeneralStatus::$OKEY;
        $attendance_data['created_at'] = date('Y-m-d H:i:s');
        $attendance_data['updated_at'] = date('Y-m-d H:i:s');
        $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
        $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;

        $dataSet = $attendance_data;

        return $dataSet;
    }

    public function overtimeLateEarlyCalc($data_format)
    {

        $dataSet = [];
        $tempArray = [];

        if ($data_format != [] && isset($data_format['working_time']) && $data_format['working_time'] != null) {

            // find employee early or late time and shift name
            if (isset($data_format['work_shift_id']) && $data_format['work_shift_id'] != null) {

                $shift_list = WorkShift::where('work_shift_id', $data_format['work_shift_id'])->first();

                $login_time = date('H:i:s', \strtotime($data_format['in_time']));
                $in_datetime = new DateTime($data_format['in_time']);
                $start_datetime = new DateTime($data_format['date'] . ' ' . $shift_list->start_time);
                $buffer_start_time = Carbon::createFromFormat('H:i:s', $shift_list->start_time)->subMinutes(15)->format('H:i:s');
                $buffer_end_time = Carbon::createFromFormat('H:i:s', $shift_list->start_time)->addMinutes(15)->format('H:i:s');

                $emp_shift = $this->shift_timing_array($login_time, $buffer_start_time, $buffer_end_time);

                if ($emp_shift == \true) {

                    if ($in_datetime >= $start_datetime) {

                        $interval = $in_datetime->diff($start_datetime);
                        $tempArray['work_shift_id'] = $shift_list->work_shift_id;
                        $tempArray['shift_name'] = $shift_list->shift_name;
                        $tempArray['start_time'] = $shift_list->start_time;
                        $tempArray['end_time'] = $shift_list->end_time;
                        $tempArray['early_by'] = null;
                        $tempArray['late_by'] = $interval->format('%H') . ":" . $interval->format('%I');

                    } elseif ($in_datetime <= $start_datetime) {

                        $interval = $start_datetime->diff($in_datetime);
                        $tempArray['work_shift_id'] = $shift_list->work_shift_id;
                        $tempArray['shift_name'] = $shift_list->shift_name;
                        $tempArray['start_time'] = $shift_list->start_time;
                        $tempArray['end_time'] = $shift_list->end_time;
                        $tempArray['early_by'] = $interval->format('%H') . ":" . $interval->format('%I');
                        $tempArray['late_by'] = null;

                    }
                }

            } else {

                $shift_list = WorkShift::orderBy('start_time', 'ASC')->get();

                if (isset($data_format['in_time']) && $data_format['in_time'] != null && isset($data_format['out_time']) && $data_format['out_time'] != null) {

                    foreach ($shift_list as $key => $value) {
                        $in_time = new DateTime($data_format['in_time']);
                        $login_time = date('H:i:s', \strtotime($data_format['in_time']));
                        $start_time = new DateTime($data_format['date'] . ' ' . $value->start_time);

                        $buffer_start_time = Carbon::createFromFormat('H:i:s', $value->start_time)->subMinutes(15)->format('H:i:s');
                        $buffer_end_time = Carbon::createFromFormat('H:i:s', $value->start_time)->addMinutes(15)->format('H:i:s');

                        $emp_shift = $this->shift_timing_array($login_time, $buffer_start_time, $buffer_end_time);

                        if ($emp_shift == \true) {

                            if ($in_time >= $start_time) {

                                $interval = $in_time->diff($start_time);
                                $tempArray['finger_print_id'] = $data_format['finger_print_id'];
                                $tempArray['work_shift_id'] = $value->work_shift_id;
                                $tempArray['shift_name'] = $value->shift_name;
                                $tempArray['start_time'] = $value->start_time;
                                $tempArray['end_time'] = $value->end_time;
                                $tempArray['late_by'] = $interval->format('%H') . ":" . $interval->format('%I') . ":" . $interval->format('%S');
                                $tempArray['early_by'] = null;

                            } elseif ($in_time <= $start_time) {
                                $interval = $start_time->diff($in_time);
                                $tempArray['finger_print_id'] = $data_format['finger_print_id'];
                                $tempArray['work_shift_id'] = $value->work_shift_id;
                                $tempArray['shift_name'] = $value->shift_name;
                                $tempArray['start_time'] = $value->start_time;
                                $tempArray['end_time'] = $value->end_time;
                                $tempArray['early_by'] = $interval->format('%H') . ":" . $interval->format('%I') . ":" . $interval->format('%S');
                                $tempArray['late_by'] = null;
                            }

                            break;

                        } else {
                            $tempArray['finger_print_id'] = $data_format['finger_print_id'];
                            $tempArray['work_shift_id'] = null;
                            $tempArray['shift_name'] = null;
                            $tempArray['start_time'] = null;
                            $tempArray['end_time'] = null;
                            $tempArray['early_by'] = null;
                            $tempArray['late_by'] = null;
                        }

                    }

                }

            }

            // find employee over time
            if (isset($tempArray['work_shift_id']) && $tempArray['work_shift_id'] != null) {

                $shiftStartTime = new DateTime(date('H:i:s', strtotime($tempArray['start_time'])));
                $shiftEndTime = new DateTime(date('H:i:s', strtotime($tempArray['end_time'])));
                $halfDayTime = new DateTime(PayrollConstant::$HALF_DAY);
                $shiftEndTimeForAtt = new DateTime(date('H:i:s', strtotime('-5 minutes', strtotime($tempArray['end_time']))));

                if ($shiftStartTime < $shiftEndTime) {
                    $employeeOutTime = new DateTime(date('H:i:s', strtotime($data_format['out_time'])));
                } else {
                    $endDate = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($data_format['date'] . ' ' . $tempArray['end_time'])));
                    $shiftEndTime = new DateTime(date('Y-m-d H:i:s', strtotime($endDate)));
                    $employeeOutTime = new DateTime($data_format['out_time']);
                }

                if ($employeeOutTime >= $shiftEndTimeForAtt) {
                    $tempArray['attendance_status'] = AttendanceStatus::$PRESENT;
                    $tempArray['mandays'] = MandayStatus::$FULL_DAY;
                } else if ($employeeOutTime >= $halfDayTime) {
                    $tempArray['attendance_status'] = AttendanceStatus::$LESSHOURS;
                    $tempArray['mandays'] = MandayStatus::$HALF_DAY;
                } else {
                    $tempArray['attendance_status'] = AttendanceStatus::$LESSHOURS;
                    $tempArray['mandays'] = MandayStatus::$LOSS_OF_PAY;
                }

                if ($shiftEndTime < $employeeOutTime) {

                    $over_time = $shiftEndTime->diff($employeeOutTime);

                    $roundMinutes = (int) $over_time->i >= 30 ? 30 : '00';
                    $roundHours = (int) $over_time->h >= 1 ? sprintf("%02d", ($over_time->h)) : '00';

                    if ($over_time->h >= 1) {
                        $tempArray['over_time'] = $roundHours . ':' . $roundMinutes;
                    }

                } else {
                    $tempArray['over_time'] = null;
                }

            } else if (!isset($tempArray['work_shift_id']) || $tempArray['work_shift_id'] == null) {

                $workingTime = new DateTime($data_format['working_time']);
                $naShiftDuration = new DateTime(PayrollConstant::$NA_OVERTIME_HOUR);
                $halfDayTime = new DateTime(PayrollConstant::$HALF_DAY);

                if ($workingTime >= $naShiftDuration) {
                    $tempArray['attendance_status'] = AttendanceStatus::$PRESENT;
                    $tempArray['mandays'] = MandayStatus::$FULL_DAY;
                } else if ($workingTime >= $halfDayTime) {
                    $tempArray['attendance_status'] = AttendanceStatus::$LESSHOURS;
                    $tempArray['mandays'] = MandayStatus::$HALF_DAY;
                } else {
                    $tempArray['attendance_status'] = AttendanceStatus::$LESSHOURS;
                    $tempArray['mandays'] = MandayStatus::$LOSS_OF_PAY;
                }

                if ($workingTime > $naShiftDuration) {

                    $over_time = $naShiftDuration->diff($workingTime);

                    $roundMinutes = (int) $over_time->i >= 30 ? '30' : '00';
                    $roundHours = (int) $over_time->h >= 1 ? sprintf("%02d", ($over_time->h)) : '00';

                    if ($over_time->h >= 1) {
                        $tempArray['over_time'] = $roundHours . ':' . $roundMinutes;
                    } else {
                        $tempArray['over_time'] = null;
                    }

                } else {
                    $tempArray['over_time'] = null;
                }
            }

            $dataSet = array_merge($data_format, $tempArray);
            unset($dataSet['start_time']);unset($dataSet['end_time']);

            return $dataSet;
        }
    }

    public function over_time($working_time, $shift_time)
    {
        $workingTime = new DateTime($working_time);
        $actualTime = new DateTime($shift_time);
        $overTime = null;

        if ($workingTime > $actualTime) {
            $over_time = $actualTime->diff($workingTime);
            $roundMinutes = (int) $over_time->i >= 30 ? '30' : '00';
            $roundHours = (int) $over_time->h >= 1 ? sprintf("%02d", ($over_time->h)) : '00';

            if ($over_time->h >= 1) {
                $overTime = $roundHours . ':' . $roundMinutes;
            }
        }

        return $overTime;
    }

    public function in_out_time($array)
    {
        $result = [];
        $count = count($array);

        foreach ($array as $key => $value) {
            if ($key == 0) {
                $result[] = date('d/m/y H:i', strtotime($value->datetime)) . ':' . 'IN';
            } elseif ($key == ($count - 1)) {
                $result[] = date('d/m/y H:i', strtotime($value->datetime)) . ':' . 'OUT';
            } else {
                $result[] = date('d/m/y H:i', strtotime($value->datetime)) . ':' . 'BTW';
            }
        }

        $str = json_encode($result);
        $str = str_replace('[', '', $str);
        $str = str_replace(']', '', $str);
        $str = str_replace('"', '', $str);
        $str = str_replace("\/", '/', $str);

        return $str;
    }

    public function calculate_hours_mins($datetime1, $datetime2)
    {
        $interval = $datetime1->diff($datetime2);
        return $interval->format('%h') . ":" . $interval->format('%i') . ":" . $interval->format('%s');
    }

    public function calculate_total_working_hours($at)
    {
        $total_seconds = 0;
        for ($i = 0; $i < count($at); $i++) {
            $seconds = 0;
            $timestr = $at[$i]['subtotalhours'];

            $parts = explode(':', $timestr);

            $seconds = ($parts[0] * 60 * 60) + ($parts[1] * 60) + $parts[2];
            $total_seconds += $seconds;
        }
        return gmdate("H:i:s", $total_seconds);
    }

    public function find_closest_time($dates, $first_in)
    {

        function closest($dates, $findate)
        {
            $newDates = array();

            foreach ($dates as $date) {
                $newDates[] = strtotime($date);
            }

            sort($newDates);

            foreach ($newDates as $a) {
                if ($a >= strtotime($findate)) {
                    return $a;
                }
            }
            return end($newDates);
        }

        $values = closest($dates, date('Y-m-d H:i:s', \strtotime($first_in)));
    }

    public function shift_timing_array($in_time, $start_shift, $end_shift)
    {
        $shift_status = $in_time <= $end_shift && $in_time >= $start_shift;
        return $shift_status;
    }

    public function workingtime($from, $to)
    {
        $date1 = new DateTime($to);
        $date2 = $date1->diff(new DateTime($from));
        $hours = ($date2->days * 24);
        $hours = $hours + $date2->h;

        return $hours . ":" . sprintf('%02d', $date2->i) . ":" . sprintf('%02d', $date2->s);
    }

    public function calculateAttendance()
    {
        return view('admin.attendance.calculateAttendance.index');
    }

    public function ifCompanyHoliday($compHolidays, $date)
    {

        $comp_holidays = [];
        foreach ($compHolidays as $holidays) {
            $start_date = $holidays->fdate;
            $end_date = $holidays->tdate;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $comp_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($comp_holidays as $val) {
            if ($val == $date) {
                return true;
            }
        }

        return false;
    }

    public function ifHoliday($govtHolidays, $date)
    {
        $ph = [];

        foreach ($govtHolidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $ph[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($ph as $val) {
            if ($val == $date) {
                return true;
            }
        }
        return false;
    }
}
