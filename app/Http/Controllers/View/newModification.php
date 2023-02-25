<?php

use DateTime;
use Carbon\Carbon;
use App\Model\Employee;
use App\Model\WorkShift;
use App\Model\LeaveApplication;
use App\Model\EmployeeInOutData;
use App\Model\DeviceAttendanceLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\LeaveStatus;
use App\Repositories\LeaveRepository;
use App\Lib\Enumerations\AttendanceStatus;
use App\Repositories\AttendanceRepository;

class sampple extends Controller
{

    protected $leaveRepository;
    protected $attendanceRepository;

    public function __construct(LeaveRepository $leaveRepository, AttendanceRepository $attendanceRepository)
    {
        $this->leaveRepository = $leaveRepository;
        $this->attendanceRepository = $attendanceRepository;
    }

    public function fetchRawLog($table_name = '')
    {
        \set_time_limit(0);

        Log::info("Controller is working fine!");

        $lastLogRow = DB::table('ms_sql')->max('datetime');
        $date = Carbon::now()->subDay(1)->format('Y-m-d');
        $date = date('Y-m-d', strtotime('-5 hours -30 minutes'));
        $carbon_parse = Carbon::parse($date)->format("Ym");

        if ($table_name == '') {
            $table_name = 't_lg' . $carbon_parse;
        }

        if ($lastLogRow) {
            $LogCollections = DB::connection('mysql2')->table($table_name)
                ->select('DEVDT', 'USRID', 'DEVUID', 'SRVDT', 'EVTLGUID')
                ->where($table_name . '.EVT', 4867)
                ->orderBy('SRVDT', 'ASC')
                ->where('SRVDT', '>=', $lastLogRow)
                ->groupBy('DEVDT', 'USRID', 'DEVUID', 'SRVDT', 'EVTLGUID')
                ->get();
        } else {
            $LogCollections = DB::connection('mysql2')->table($table_name)
                ->select('DEVDT', 'USRID', 'DEVUID', 'SRVDT', 'EVTLGUID')
                ->where($table_name . '.EVT', 4867)
                ->orderBy('SRVDT', 'ASC')
                ->groupBy('USRID', 'DEVUID', 'SRVDT', 'EVTLGUID', 'DEVDT')
                ->get();
        }

        foreach ($LogCollections as $key => $log) {

            $type = \null;

            $check_record = DB::table('ms_sql')->where('ID', $log->USRID)->where('evtlguid', $log->EVTLGUID)->where('devdt', $log->DEVDT)->first();

            $last_record = DB::table('ms_sql')->where('ID', $log->USRID)->orderByDesc('primary_id')->first();

            $closeTiming = date('Y-m-d H:i', strtotime($last_record->datetime)) == date('Y-m-d H:i', strtotime($log->datetime));

            if (!$last_record) {
                $type = "IN";
            } elseif ($last_record && $last_record->type == 'OUT' && !$closeTiming) {
                $type = "IN";
            } elseif ($last_record && $last_record->type == 'IN' && !$closeTiming) {
                $type = "OUT";
            }

            if (!$check_record) {

                $data = [
                    'evtlguid' => $log->EVTLGUID,
                    'datetime' => date('Y-m-d H:i:s', $log->DEVDT),
                    'devdt' => $log->DEVDT,
                    'punching_time' => $log->SRVDT,
                    'ID' => $log->USRID,
                    'devuid' => $log->DEVUID,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'type' => $type,
                ];

                DB::table('ms_sql')->insert($data);
            }
        }

        echo "<br>";
        echo "Success : Data Imported Successfully";
        echo "<br>";
    }

    public function attendance($finger_print_id = null, $manualAttendance = false, $manualDate = null)
    {
        \ob_start();
        \set_time_limit(0);
        $time_start = microtime(true);
        $data_format = [];
        $date = date('Y-m-d', strtotime('-1 days'));
        $date2 = Carbon::today()->subDay(1);
        $secondRun = false;
        $startTime = " 06:30:00";
        $endTime = " 08:30:00";
        $dayStart = '';
        $dayEnd = '';

        if ($finger_print_id != null && $manualAttendance != false && $manualDate != null) {
            $secondRun = true;
            $date = $manualDate;
            $employees = Employee::where('finger_id', $finger_print_id)->select('finger_id', 'employee_id')->get();
        } else {
            $employees = Employee::select('finger_id', 'employee_id')->groupby('finger_id')->get();
        }

        $maxReportDate = EmployeeInOutData::max('date');
        $maxLogDatetime = DeviceAttendanceLog::where('device_name', 'not like', '%Manual%')->orwhere('device_name', null)->max('datetime');
        $minLogDatetime = DeviceAttendanceLog::where('device_name', 'not like', '%Manual%')->orwhere('device_name', null)->min('datetime');

        if (!$maxLogDatetime) {
            return false;
        }

        // $utilizedLogDatetime = new DateTime($maxLogDatetime);
        // $utilizedLogDatetime = $utilizedLogDatetime->modify('-1 days');
        // $utilizedLogDatetime = $utilizedLogDatetime->format('Y-m-d');

        // if (!($maxLogDatetime >= date('Y-m-d' . $endTime))) {

        //     info('attendance log is not synced yet...');
        //     return true;

        // }
        //  else {

        // if ($maxReportDate) {
        //     $substrStartDate = str_split(date('d', strtotime($maxReportDate)), 1);

        //     $reportStartDatetime = new DateTime($maxReportDate);
        //     $reportBeginDatetime = $reportStartDatetime->modify('1 days');
        //     $reportStartDate = $reportBeginDatetime->format('Y-m-d');
        //     $reportStartDay = $reportBeginDatetime->format('d');

        //     if ($substrStartDate[0] == 0) {
        //         $dayStart = sprintf("%01d", $reportStartDay);
        //     } else {
        //         $dayStart = sprintf("%02d", $reportStartDay);
        //     }

        // } else {
        //     $substrStartDate = str_split(date('d', strtotime($minLogDatetime)), 1);
        //     $reportStartDate = date('Y-m-d', strtotime($minLogDatetime));
        //     $reportStartDay = date('d', strtotime($minLogDatetime));

        //     if ($substrStartDate[0] == 0) {
        //         $dayStart = sprintf("%01d", $reportStartDay);
        //     } else {
        //         $dayStart = sprintf("%02d", $reportStartDay);
        //     }

        // }

        // $reportEndDate = date('Y-m-d', strtotime($maxLogDatetime));
        // $reportEndDay = date('d', strtotime($maxLogDatetime));
        // $substrEndDate = str_split(date('d', strtotime($maxLogDatetime)), 1);

        // if ($substrEndDate[0] == 0) {
        //     $dayEnd = sprintf("%01d", $reportEndDay);
        // } else {
        //     $dayEnd = sprintf("%02d", $reportEndDay);
        // }

        // // }

        // if (strtotime($reportStartDate) > strtotime($reportEndDate)) {
        //     dd('dates invalid');
        //     return true;
        // }

        // for ($date = $reportStartDate; $date <= $reportEndDate; $date = date('Y-m-d', strtotime('+1 days', strtotime($date)))) {
        //     info('Report Generated for : ' . $date);
        // }

        // for loop
        // for ($date = $reportStartDate; $date <= $reportEndDate; $date = date('Y-m-d', strtotime('+1 days', strtotime($date)))) {

        foreach ($employees as $finger_id) {

            $rework = EmployeeInOutData::whereRaw("date= '" . $date . "' and finger_print_id= '" . $finger_id->finger_id . "'")->first();

            if ($rework || $secondRun == true) {
                $secondRun = true;
            }

            $start_date = DATE('Y-m-d', strtotime($date)) . $startTime;
            $end_date = DATE('Y-m-d', strtotime($date . " +1 day")) . $endTime;

            $data_format = $this->calculate_attendance($start_date, $end_date, $finger_id, $secondRun, $manualAttendance);
            $shift_list = WorkShift::orderBy('start_time', 'ASC')->get();

            //find employee over time
            if ($data_format != [] && isset($data_format['working_time'])) {

                $workingTime = new DateTime($data_format['working_time']);
                $actualTime = new DateTime('08:00:00');

                if ($workingTime > $actualTime) {

                    $over_time = $actualTime->diff($workingTime);

                    $roundMinutes = (int) $over_time->i >= 30 ? '30' : '00';
                    $roundHours = (int) $over_time->h >= 1 ? sprintf("%02d", ($over_time->h)) : '00';

                    if ($over_time->h >= 1) {

                        $data_format['attendance_status'] = AttendanceStatus::$PRESENT;
                        $data_format['over_time'] = $roundHours . ':' . $roundMinutes;

                    } else {

                        $data_format['attendance_status'] = AttendanceStatus::$PRESENT;
                        $data_format['over_time'] = null;

                    }

                } else {

                    $data_format['attendance_status'] = AttendanceStatus::$LESSHOURS;
                    $data_format['over_time'] = null;

                }

                // find employee early or late time and shift name
                if ($data_format != [] && isset($data_format['in_time']) && isset($data_format['out_time'])) {

                    foreach ($shift_list as $key => $value) {

                        $in_time = new DateTime($data_format['in_time']);
                        $login_time = date('H:i:s', \strtotime($data_format['in_time']));
                        $start_time = new DateTime($data_format['date'] . ' ' . $value->start_time);

                        $buffer_start_time = Carbon::createFromFormat('H:i:s', $value->start_time)->subMinutes(29)->format('H:i:s');
                        $buffer_end_time = Carbon::createFromFormat('H:i:s', $value->start_time)->addMinutes(29)->format('H:i:s');

                        $emp_shift = $this->shift_timing_array($login_time, $buffer_start_time, $buffer_end_time);

                        $earlyArray = [];
                        $earlyArray = [];

                        info('---------------------------------------------------------------');
                        info($finger_id->finger_id);
                        info($date);

                        // info($buffer_start_time);
                        // info($login_time);
                        // info($buffer_end_time);
                        // info($emp_shift ? 1 : 0);
                        // info('---------------------------------------------------------------');

                        if ($emp_shift == \true) {

                            if ($in_time >= $start_time) {

                                info($value->shift_name);
                                $interval = $in_time->diff($start_time);
                                $data_format['shift_name'] = $value->shift_name;
                                $data_format['early_by'] = null;
                                $data_format['late_by'] = $interval->format('%H') . ":" . $interval->format('%I');

                            } elseif ($in_time <= $start_time) {

                                info($value->shift_name);
                                $interval = $start_time->diff($in_time);
                                $data_format['shift_name'] = $value->shift_name;
                                $data_format['early_by'] = $interval->format('%H') . ":" . $interval->format('%I');
                                $data_format['late_by'] = null;

                            }

                        } else {

                            $data_format['early_by'] = null;
                            $data_format['late_by'] = null;
                        }
                    }
                }
            }

            //insert employee attendacne data to report table
            if ($data_format != [] && (isset($data_format['working_time']) || isset($data_format['in_time']) || isset($data_format['out_time']))) {

                $workingTime = explode(':', $data_format['working_time']);

                if ($workingTime[0] >= 0) {
                    $if_exists = EmployeeInOutData::where('finger_print_id', $data_format['finger_print_id'])->where('date', $data_format['date'])->first();

                    if (!$if_exists) {
                        EmployeeInOutData::insert($data_format);
                    } else {
                        EmployeeInOutData::where('date', $data_format['date'])->where('finger_print_id', $data_format['finger_print_id'])->update($data_format);
                    }
                }
            } else {

                $if_exists = EmployeeInOutData::where('finger_print_id', $finger_id->finger_id)->where('date', date('Y-m-d', \strtotime($start_date)))->first();

                $data_format = [
                    'date' => date('Y-m-d', \strtotime($start_date)),
                    'finger_print_id' => $finger_id->finger_id,
                    'in_time' => null,
                    'out_time' => null,
                    'working_time' => null,
                    'working_hour' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'created_by' => isset(auth()->user()->user_id) ? auth()->user()->user_id : null,
                    'updated_by' => isset(auth()->user()->user_id) ? auth()->user()->user_id : null,
                    'status' => 1,
                ];

                $tempArray = [];

                $govtHolidays = DB::select(DB::raw('call SP_getHoliday("' . $date . '","' . $date . '")'));

                $leave = LeaveApplication::select('application_from_date', 'application_to_date', 'employee_id', 'leave_type_name')
                    ->join('leave_type', 'leave_type.leave_type_id', 'leave_application.leave_type_id')
                    ->where('status', LeaveStatus::$APPROVE)
                    ->where('application_from_date', '>=', $date)
                    ->where('application_to_date', '<=', $date)
                    ->get();

                $hasLeave = $this->attendanceRepository->ifEmployeeWasLeave($leave, $finger_id->employee_id, $date);
                if ($hasLeave) {
                    $tempArray['attendance_status'] = AttendanceStatus::$LEAVE;
                } else {
                    if ($date > date("Y-m-d")) {
                        $tempArray['attendance_status'] = AttendanceStatus::$FUTURE;
                    } else {
                        $ifHoliday = $this->attendanceRepository->ifHoliday($govtHolidays, $date, $finger_id->employee_id);
                        if ($ifHoliday['weekly_holiday'] == true) {
                            $tempArray['attendance_status'] = AttendanceStatus::$HOLIDAY;
                        } elseif ($ifHoliday['govt_holiday'] == true) {
                            $tempArray['attendance_status'] = AttendanceStatus::$HOLIDAY;
                        } else {
                            $tempArray['attendance_status'] = AttendanceStatus::$ABSENT;
                        }
                    }
                }
                if (!$if_exists) {
                    $data_format['attendance_status'] = $tempArray['attendance_status'];
                    // echo "<br> created <pre>" . print_r($data_format) . "</pre>";
                    EmployeeInOutData::insert($data_format);
                } else {
                    $data_format['attendance_status'] = $tempArray['attendance_status'];
                    // echo "<br> updated <pre>" . print_r($data_format) . "</pre>";
                    $if_exists->update($data_format);
                    $if_exists->save();
                }
            }

            // for loop
            // }

        }

        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);

        echo '<br> <b>Total Execution Time:</b> ' . ($execution_time) . 'Seconds';
        echo '<b>Total Execution Time:</b> ' . ($execution_time * 1000) . 'Milliseconds <br>';
        ob_end_flush();

        if (!$manualAttendance) {
            return true;
        }
    }
}
