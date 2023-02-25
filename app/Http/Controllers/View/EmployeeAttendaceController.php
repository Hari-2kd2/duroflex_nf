<?php

namespace App\Http\Controllers\View;

use DateTime;
use Carbon\Carbon;
use App\Model\MsSql;
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

class EmployeeAttendaceController extends Controller
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

        foreach ($employees as $key1 => $finger_id) {

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

            // dd($data_format);
        }

        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);

        // echo '<br> <b>Total Execution Time:</b> ' . ($execution_time) . 'Seconds';
        // echo '<b>Total Execution Time:</b> ' . ($execution_time * 1000) . 'Milliseconds <br>';
        ob_end_flush();

        // if ($finger_print_id != null && $manualAttendance != false && $manualDate != null) {
        //     return redirect('manualAttendance')->with('success', 'Attendance successfully saved.');
        // }

        if (!$manualAttendance) {
            return true;
        }
    }

    public function calculate_attendance($date_from, $date_to, $finger_id, $reRun, $manualAttendance)
    {
        $k = 0;
        $a = 0;
        $first_row = 0;
        $at = [];
        $bt = [];
        $first_row_2 = 0;
        $at_id = [];
        $bt_id = [];
        $in_out_time_at = [];
        $in_out_time_bt = [];
        $attendance_data = [];
        $device_name_at = [];
        $device_name_bt = [];

        $primary_id = [];
        $primary_id_2 = [];
        $primary_id_3 = [];
        $in_out_time_record = [];
        $in_out_time_record_2 = [];
        $in_out_time_record_3 = [];

        \set_time_limit(0);

        if ($manualAttendance) {

            $results = DB::table('manual_attendance')
                ->whereRaw("datetime >= '" . $date_from . "' AND datetime <= '" . $date_to . "'")
                ->where('ID', $finger_id->finger_id)->where('device_name', 'Manual')
                ->orderby('datetime', 'ASC')
                ->get();

            Log::info("results for Manual attendance: " . $finger_id->finger_id . ' Date : ' . $date_from);
            $attendance_data['date'] = date('Y-m-d', strtotime($results[0]->datetime));
            $attendance_data['in_time'] = date('Y-m-d H:i:s', strtotime($results[0]->datetime));
            $attendance_data['finger_print_id'] = $finger_id->finger_id;
            $attendance_data['out_time'] = date('Y-m-d H:i:s', strtotime($results[1]->datetime));
            $attendance_data['working_time'] = $this->workingtime($results[0]->datetime, $results[1]->datetime);
            $attendance_data['working_hour'] = $this->workingtime($results[0]->datetime, $results[1]->datetime);
            $attendance_data['device_name'] = $results[0]->device_name;
            $attendance_data['status'] = 1;
            $explode = explode(':', $attendance_data['working_time']);
            $attendance_data['attendance_status'] = $explode[0] >= 8 ? AttendanceStatus::$PRESENT : AttendanceStatus::$LESSHOURS;
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            $attendance_data['updated_at'] = date('Y-m-d H:i:s');
            $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['in_out_time'] = date('H:i', strtotime($results[0]->datetime)) . ":" . $results[0]->type . ' ' . date('H:i', strtotime($results[1]->datetime)) . ":" . $results[1]->type;
        }

        // dd($results);

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

        if (count($results) == 1 && $results[0]->type == 'IN') {
            Log::info("Only one results for : " . $finger_id->finger_id . ' Date : ' . $date_from);
            $attendance_data['date'] = date('Y-m-d', strtotime($results[0]->datetime));
            $attendance_data['in_time'] = date('Y-m-d H:i:s', strtotime($results[0]->datetime));
            $attendance_data['finger_print_id'] = $finger_id->finger_id;
            $attendance_data['out_time'] = \null;
            $attendance_data['working_time'] = \null;
            $attendance_data['working_hour'] = \null;
            $attendance_data['device_name'] = \null;
            $attendance_data['status'] = 2;
            $attendance_data['attendance_status'] = AttendanceStatus::$ONETIMEINPUNCH;
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            $attendance_data['updated_at'] = date('Y-m-d H:i:s');
            $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['in_out_time'] = date('H:i', strtotime($results[0]->datetime)) . ":" . $results[0]->type;

            return $attendance_data;

        } elseif (count($results) == 1 && $results[0]->type == 'OUT') {

            Log::info("Only one results for : " . $finger_id->finger_id . ' Date : ' . $date_from);
            $attendance_data['date'] = date('Y-m-d', strtotime($results[0]->datetime));
            $attendance_data['out_time'] = date('Y-m-d H:i:s', strtotime($results[0]->datetime));
            $attendance_data['finger_print_id'] = $finger_id->finger_id;
            $attendance_data['in_time'] = \null;
            $attendance_data['working_time'] = \null;
            $attendance_data['working_hour'] = \null;
            $attendance_data['device_name'] = \null;
            $attendance_data['status'] = 2;
            $attendance_data['attendance_status'] = AttendanceStatus::$ONETIMEOUTPUNCH;
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            $attendance_data['updated_at'] = date('Y-m-d H:i:s');
            $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['in_out_time'] = date('H:i', strtotime($results[0]->datetime)) . ":" . $results[0]->type;

            return $attendance_data;

        } elseif (count($results) > 1) {

            $count_check = 0;
            $count_check_2 = 0;
            $count_check_3 = 0;

            for ($i = 0; $i < count($results); $i++) {
                if ($results[$i]->type == 'IN') {
                    $count_check++;
                    array_push($primary_id, $results[$i]->primary_id);
                    array_push($in_out_time_record, (date('H:i', strtotime($results[$i]->datetime)) . ':' . $results[$i]->type));
                }
            }

            for ($i = 0; $i < count($results); $i++) {
                if ($results[$i]->type == 'OUT') {
                    $count_check_2++;
                    array_push($primary_id_2, $results[$i]->primary_id);
                    array_push($in_out_time_record_2, (date('H:i', strtotime($results[$i]->datetime)) . ':' . $results[$i]->type));
                }
            }

            for ($i = 0; $i < count($results); $i++) {
                if ($results[0]->type == 'OUT') {
                    $count_check_3++;
                    array_push($primary_id_3, $results[0]->primary_id);
                    array_push($in_out_time_record_3, (date('H:i', strtotime($results[$i]->datetime)) . ':' . $results[$i]->type));

                    if ($i != 0 && $results[$i]->type == 'IN') {
                        $count_check_3++;
                        array_push($primary_id_3, $results[$i]->primary_id);
                        array_push($in_out_time_record_3, (date('H:i', strtotime($results[$i]->datetime)) . ':' . $results[$i]->type));
                    }
                }
            }

            if ($count_check == count($results)) {

                $attendance_data['date'] = date('Y-m-d', strtotime($results[0]->datetime));
                $attendance_data['in_time'] = date('Y-m-d H:i:s', strtotime($results[0]->datetime));
                $attendance_data['finger_print_id'] = $finger_id->finger_id;
                $attendance_data['out_time'] = \null;
                $attendance_data['working_time'] = \null;
                $attendance_data['working_hour'] = \null;
                $attendance_data['device_name'] = \null;
                $attendance_data['status'] = 2;
                $attendance_data['attendance_status'] = AttendanceStatus::$ONETIMEINPUNCH;
                $attendance_data['created_at'] = date('Y-m-d H:i:s');
                $attendance_data['updated_at'] = date('Y-m-d H:i:s');
                $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
                $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
                $attendance_data['in_out_time'] = $this->in_out_time_list($in_out_time_record);
                $update = DB::table('ms_sql')->whereIn('primary_id', $primary_id)->update(['status' => 1]);

                return $attendance_data;

            } elseif ($count_check_2 == count($results)) {

                $attendance_data['date'] = date('Y-m-d', strtotime($results[0]->datetime));
                $attendance_data['out_time'] = date('Y-m-d H:i:s', strtotime($results[count($results) - 1]->datetime));
                $attendance_data['finger_print_id'] = $finger_id->finger_id;
                $attendance_data['in_time'] = \null;
                $attendance_data['working_time'] = \null;
                $attendance_data['working_hour'] = \null;
                $attendance_data['device_name'] = \null;
                $attendance_data['status'] = 2;
                $attendance_data['attendance_status'] = AttendanceStatus::$ONETIMEOUTPUNCH;
                $attendance_data['created_at'] = date('Y-m-d H:i:s');
                $attendance_data['updated_at'] = date('Y-m-d H:i:s');
                $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
                $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
                $attendance_data['in_out_time'] = $this->in_out_time_list($in_out_time_record_2);
                $update = DB::table('ms_sql')->whereIn('primary_id', $primary_id_2)->update(['status' => 1]);

                return $attendance_data;
            } elseif ($count_check_3 == count($results)) {

                $attendance_data['date'] = date('Y-m-d', strtotime($results[0]->datetime));
                $attendance_data['in_time'] = date('Y-m-d H:i:s', strtotime($results[1]->datetime));
                $attendance_data['finger_print_id'] = $finger_id->finger_id;
                $attendance_data['out_time'] = \null;
                $attendance_data['working_time'] = \null;
                $attendance_data['working_hour'] = \null;
                $attendance_data['device_name'] = \null;
                $attendance_data['status'] = 2;
                $attendance_data['attendance_status'] = AttendanceStatus::$ONETIMEINPUNCH;
                $attendance_data['created_at'] = date('Y-m-d H:i:s');
                $attendance_data['updated_at'] = date('Y-m-d H:i:s');
                $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
                $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
                $attendance_data['in_out_time'] = $this->in_out_time_list($in_out_time_record_3);
                $update = DB::table('ms_sql')->whereIn('primary_id', $primary_id_3)->update(['status' => 1]);

                return $attendance_data;
            }
        }

        foreach ($results as $key => $row) {

            $init = MsSql::where('primary_id', $row->primary_id)->update(['status' => 2]);

            $attendance_data['device_name'] = $row->device_name;

            if ($first_row == 0 && $row->type == "OUT") {
                array_push($at_id, $row->primary_id);
                array_push($in_out_time_at, $row->type);
                array_push($device_name_at, $row->device_name);
                // echo 'at first row OUT <br>';
                continue;
            } elseif (!isset($at[$k]['fromdate']) && $row->type == "OUT" && $first_row_2 == 0) {
                $j = $k;
                $j--;
                if (!isset($at[$j]['fromdate'])) {
                    array_push($at_id, $row->primary_id);
                    array_push($in_out_time_at, $row->type);
                    array_push($device_name_at, $row->device_name);
                    // echo 'at first row 2 OUT <br>';

                    continue;
                }
            } elseif (isset($at[$k]['fromdate']) && $row->type == "IN" && $first_row_2 == 0) {

                $datetime1 = new DateTime($at[$k]['fromdate']);
                $datetime2 = new DateTime($row->datetime);
                $subtotal = $this->calculate_hours_mins($datetime1, $datetime2);
                $pieces = explode(":", $subtotal);
                if ($pieces[0] > 13) {

                    $bt[$a]['fromdate'] = $row->datetime;
                    $bt[$a]['statusin'] = $row->type;
                    array_push($bt_id, $row->primary_id);
                    array_push($in_out_time_bt, $row->type);
                    array_push($device_name_bt, $row->device_name);

                    $first_row_2 = 1;
                    // echo ' bt first_row_2 = 0  - >9 <br>';

                    continue;
                }

                array_push($at_id, $row->primary_id);
                array_push($in_out_time_at, $row->type);
                array_push($device_name_at, $row->device_name);
                // echo ' at first_row_2 = 0 - <9 <br>';

                continue;
            }

            if ($row->type == "IN") {
                $j = $k;
                $j--;
                if ($first_row_2 == 1) {
                    if (isset($bt[$a]['fromdate'])) {
                        array_push($at_id, $row->primary_id);
                        array_push($in_out_time_at, $row->type);
                        array_push($device_name_at, $row->device_name);
                        // echo 'IN at first_row_2 = 1  <br>';

                        continue;
                    }
                    $bt[$a]['fromdate'] = $row->datetime;
                    $bt[$a]['statusin'] = $row->type;
                    array_push($bt_id, $row->primary_id);
                    array_push($in_out_time_bt, $row->type);
                    array_push($device_name_bt, $row->device_name);

                    $first_row_2 = 1;
                    // echo 'IN at first_row_2 = 1  <br>';
                    continue;
                }
                if ($k > 0) {
                    $j = $k;
                    $j--;

                    $datetime1 = new DateTime($at[$j]['todate']);
                    $datetime2 = new DateTime($row->datetime);
                    $subtotal = $this->calculate_hours_mins($datetime1, $datetime2);
                    $pieces = explode(":", $subtotal);
                    if ($pieces[0] > 13) {
                        if (isset($bt[$a]['fromdate'])) {
                            array_push($bt_id, $row->primary_id);
                            array_push($in_out_time_bt, $row->type);
                            array_push($device_name_bt, $row->device_name);
                            // echo 'IN bt first_row_2 = 1 - > 9 <br>';

                            continue;
                        }
                        $bt[$a]['fromdate'] = $row->datetime;
                        $bt[$a]['statusin'] = $row->type;
                        array_push($bt_id, $row->primary_id);
                        array_push($in_out_time_bt, $row->type);
                        array_push($device_name_bt, $row->device_name);
                        // echo 'IN bt first_row_2 = 1 - < 9 <br>';

                        $first_row_2 = 1;
                        continue;
                    }
                }
                array_push($at_id, $row->primary_id);
                array_push($in_out_time_at, $row->type);
                array_push($device_name_at, $row->device_name);

                $at[$k]['fromdate'] = $row->datetime;
                $at[$k]['statusin'] = $row->type;
                $first_row = 1;
                continue;
            }

            if ($row->type == "OUT") {
                if ($first_row_2 == 0) {
                    if (isset($at[$k]['fromdate']) && $at[$k]['fromdate'] != "") {
                        $datetime1 = new DateTime($at[$k]['fromdate']);
                        $datetime2 = new DateTime($row->datetime);
                        $subtotal = $this->calculate_hours_mins($datetime1, $datetime2);
                        $pieces = explode(":", $subtotal);
                        if ($pieces[0] > 13) {
                            array_push($at_id, $row->primary_id);
                            array_push($in_out_time_at, $row->type);
                            array_push($device_name_at, $row->device_name);
                            // echo 'OUT at first_row_2 = 0  <br>';

                            continue;
                        }
                        $at[$k]['statusout'] = $row->type;
                        $at[$k]['todate'] = $row->datetime;
                        $at[$k]['subtotalhours'] = $subtotal;
                        array_push($at_id, $row->primary_id);
                        array_push($in_out_time_at, $row->type);
                        array_push($device_name_at, $row->device_name);
                        // echo 'OUT at first_row_2 = 0  <br>';

                        $k++;
                        continue;
                    } elseif (!isset($at[$k]['todate'])) {
                        if (isset($at[$j]['fromdate'])) {
                            $j = $k;
                            $j--;
                            $datetime1 = new DateTime($at[$j]['fromdate']);
                            $datetime2 = new DateTime($row->datetime);
                            $subtotal = $this->calculate_hours_mins($datetime1, $datetime2);
                            $at[$j]['todate'] = $row->datetime;
                            $at[$j]['statusout'] = $row->type;
                            $at[$j]['subtotalhours'] = $subtotal;
                            array_push($at_id, $row->primary_id);
                            array_push($in_out_time_at, $row->type);
                            array_push($device_name_at, $row->device_name);
                            //  echo 'OUT at first_row_2 != 0 <br>';

                            continue;
                        }
                    }
                } else {
                    if (isset($bt[$a]['fromdate']) && $bt[$a]['fromdate'] != "") {
                        $bt[$a]['statusout'] = $row->type;
                        $bt[$a]['todate'] = $row->datetime;
                        $datetime1 = new DateTime($bt[$a]['fromdate']);
                        $datetime2 = new DateTime($row->datetime);
                        $subtotal = $this->calculate_hours_mins($datetime1, $datetime2);
                        $bt[$a]['subtotalhours'] = $subtotal;
                        array_push($bt_id, $row->primary_id);
                        array_push($in_out_time_bt, $row->type);
                        array_push($device_name_bt, $row->device_name);
                        // echo 'isset bt fromdate  <br>';

                        $a++;
                        continue;
                    } elseif (!isset($bt[$a]['todate'])) {
                        $j = $a;
                        $j--;
                        if (isset($bt[$j]['fromdate'])) {
                            $datetime1 = new DateTime($bt[$j]['fromdate']);
                            $datetime2 = new DateTime($row->datetime);
                            $subtotal = $this->calculate_hours_mins($datetime1, $datetime2);
                            $bt[$j]['todate'] = $row->datetime;
                            $bt[$j]['statusout'] = $row->type;
                            $bt[$j]['subtotalhours'] = $subtotal;
                            array_push($bt_id, $row->primary_id);
                            array_push($in_out_time_bt, $row->type);
                            array_push($device_name_bt, $row->device_name);
                            // echo 'isset bt fromdate !todate  <br>';

                            continue;
                        }
                    }
                }
            }
        }

        if (count($at) > 0) {
            if (!isset($at[count($at) - 1]['todate'])) {
                unset($at[count($at) - 1]);
            }
        }

        if (count($bt) > 0) {
            if (!isset($bt[count($bt) - 1]['todate'])) {
                unset($bt[count($bt) - 1]);
            }
        }

        for ($i = 0; $i < count($at); $i++) {
            echo $at[$i]['fromdate'] . "  -  " . $at[$i]['todate'] . "  ---  " . $at[$i]['subtotalhours'];
            echo "<br>";
        }
        $work1 = $this->calculate_total_working_hours($at);

        "<br>-------------------------<br>";
        for ($i = 0; $i < count($bt); $i++) {
            $bt[$i]['fromdate'] . "  -  " . $bt[$i]['todate'] . "  ---  " . $bt[$i]['subtotalhours'];
            "<br>";
        }
        $work2 = $this->calculate_total_working_hours($bt);
        $work2;

        if (count($bt) > 0) {
            $work1_hours = explode(":", $work1);
            $work2_hours = explode(":", $work2);
            if ($work2_hours > $work1_hours) {
                $at = $bt;
                $at_id = $bt_id;
                $in_out_time_at = $in_out_time_bt;
                $device_name_at = $device_name_bt;
            }
        }

        for ($i = 0; $i <= count($at_id) - 1; $i++) {
            $sql = "update ms_sql3 set status=1 where primary_id=" . $at_id[$i];
            //echo "<br>".$sql."</br>";
            //$mysqli->query($sql);
        }
        // echo "<br><pre>";
        // print_r($at_id);
        // print_r($in_out_time_at);
        // echo "</br>";

        if (isset($at[0]['fromdate'])) {
            $currnet_date = Carbon::createFromFormat('Y-m-d H:i:s', $at[0]['fromdate'])->format('Y-m-d');
            $from_date = Carbon::createFromFormat('Y-m-d H:i:s', $date_from)->format('Y-m-d');
            if ($currnet_date != $from_date) {
                $k = 0;
                $a = 0;
                $first_row = 0;
                $at = [];
                $bt = [];
                $first_row_2 = 0;
                $at_id = [];
                $bt_id = [];
                $in_out_time_at = [];
                $in_out_time_bt = [];
                $device_name_at = [];
                $device_name_bt = [];
                $attendance_data = [];
            }
        }

        $update_status = true;

        if (count($at) > 0) {
            foreach ($at_id as $primary_id) {

                // echo 'Primary ID ' . $primary_id;
                $upd_to_date = $at[count($at) - 1]['todate'];
                $check_by_primary = MsSql::where('primary_id', $primary_id)->first();

                if ($update_status == true) {

                    $update = DB::table('ms_sql')->where('primary_id', $primary_id)->update(['status' => 1]);
                    // echo "<br>";
                    // echo "Update status = " . $update_status;
                    // echo "<br>";
                }
                if ($upd_to_date == $check_by_primary->datetime) {
                    $update_status = false;
                }
            }
        }

        // Attendance data set return values...................................
        for ($i = 0; $i < count($at); $i++) {

            if ($i == 0) {
                $attendance_data['date'] = date('Y-m-d', strtotime($at[$i]['fromdate']));
                $attendance_data['in_time'] = $at[$i]['fromdate'];
                $attendance_data['finger_print_id'] = $finger_id->finger_id;
                // $attendance_data['finger_print_id'] = $finger_id['ID'];
            }
            $attendance_data['out_time'] = $at[count($at) - 1]['todate'];
            // $attendance_data['working_time'] = $this->calculate_total_working_hours($at);
            $attendance_data['working_time'] = $this->workingtime($at[0]['fromdate'], $at[count($at) - 1]['todate']);
            $attendance_data['working_hour'] = $this->calculate_total_working_hours($at);
            // $attendance_data['working_hour'] = $this->workingtime($at[0]['fromdate'], $at[count($at) - 1]['todate']);
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            $attendance_data['updated_at'] = date('Y-m-d H:i:s');
            $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
        }

        if ($attendance_data != []) {
            if (count($at) > 0 && count($at_id) > 0) {

                $attendance_data['in_out_time'] = $this->in_out_time($at_id, $in_out_time_at, $device_name_at);
            }
        }

        // echo "<pre>";
        // print_r($at);
        //  print_r($attendance_data);
        //  echo "</pre>";

        return $attendance_data;
    }

    public function in_out_time($at_id, $in_out_time_at, $device_name_at)
    {
        $result = [];
        $array_values = array_values($at_id);
        $array_values = json_encode($at_id);

        foreach ($at_id as $key => $primary_id) {
            $in_out_time = DB::table('ms_sql')->where('primary_id', $primary_id)->select('datetime')->first();
            $result[] = date('H:i', strtotime($in_out_time->datetime)) . ':' . $in_out_time_at[$key] . ' ' . '(' . $device_name_at[$key] . ')';
        }
        // dd($result);
        $str = json_encode($result);
        $str = str_replace('[', ' ', $str);
        $str = str_replace(']', ' ', $str);
        $str = str_replace('"', ' ', $str);
        // dd($str);
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

    public function find_work_shift()
    {
        // $actual_datetime, $shift_datetime

        $shift_list = WorkShift::all();

        $day = 5;
        $finger_id['ID'] = 'P001';
        // dd($finger_id);

        $start = sprintf("%02d", $day);
        $date = '2022-07' . '-' . $start . '';
        // dd($date);

        $start_date = DATE('Y-m-d', strtotime($date)) . " 05:00:00";
        $end_date = DATE('Y-m-d', strtotime($date . " +1 day")) . " 08:00:00";

        $data_format = $this->calculate_attendance($start_date, $end_date, $finger_id, false, false);
        dump($data_format);

        //     if (isset($data_format)) {
        //         foreach ($shift_list as $key => $value) {
        //             // dd();
        //             $datetime1 = new DateTime($data_format['in_time']);
        //             $datetime2 = new DateTime($value['start_time']);
        //             if ($datetime1 >=  $datetime2) {
        //                 $interval = $datetime1->diff($datetime2);
        //                 echo $interval->format('%h') . " Hours " . $interval->format('%i') . " Minutes";
        //             } else {
        //                 $interval = $datetime2->diff($datetime1);
        //                 echo $interval->format('%h') . " Hours " . $interval->format('%i') . " Minutes";
        //             }
        //         }
        //     }
    }

    public function find_closest_time($dates, $first_in)
    {

        function closest($dates, $findate)
        {
            $newDates = array();

            foreach ($dates as $date) {
                $newDates[] = strtotime($date);
            }

            // echo "<pre>";
            // print_r($newDates);
            // echo "</pre>";

            sort($newDates);
            foreach ($newDates as $a) {
                if ($a >= strtotime($findate)) {
                    return $a;
                }
            }
            return end($newDates);
        }

        $values = closest($dates, date('Y-m-d H:i:s', \strtotime($first_in)));
        // echo date('Y-m-d H:i:s', $values);
    }

    public function shift_timing_array($in_time, $start_shift, $end_shift)
    {
        $shift_status = $in_time <= $end_shift && $in_time >= $start_shift;
        return $shift_status;
    }

    public function find_device_name($mystring)
    {
        // $mystring = "Main Door Exit";
        $devices_name = '';
        $devices = ['Service Door', 'Main Door'];
        // $devices = ['Service Door Exit', 'service door entry', 'Main Door entry', 'Main door exit'];

        // Test if string contains the word
        if (strpos($mystring, $devices[0]) !== false) {
            $devices_name = 'SD';
        } elseif (strpos($mystring, $devices[1]) !== false) {
            $devices_name = 'MD';
        }

        // echo '<br>';
        // echo $devices_name;
        return $devices_name;
    }

    public function workingtime($from, $to)
    {
        $date1 = new DateTime($to);
        $date2 = $date1->diff(new DateTime($from));
        $hours = ($date2->days * 24);
        $hours = $hours + $date2->h;

        return $hours . ":" . $date2->i . ":" . $date2->s;
    }

    public function in_out_time_list($in_out_time_list)
    {
        $result = [];

        foreach ($in_out_time_list as $key => $in_out_time) {
            $result[] = $in_out_time;
        }

        $str = json_encode($result);
        $str = str_replace('[', ' ', $str);
        $str = str_replace(']', ' ', $str);
        $str = str_replace('"', ' ', $str);
        return $str;
    }
}
