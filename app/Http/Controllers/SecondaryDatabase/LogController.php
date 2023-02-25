<?php

namespace App\Http\Controllers\SecondaryDatabase;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Model\Employee;
use App\Model\EmployeeAttendance;
use App\Model\WorkShift;
use DateTime;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Razorpay\Api\Card;

class LogController extends Controller
{

    public function findTable()
    {
        $tables  = DB::connection('mysql2')->select('SHOW TABLES');
        $results = [];
        foreach ($tables as $table) {
            $data[] = $table->Tables_in_biostar2_ac;
        }
        return $results = $data;
    }

    public function sample(Request $request)
    {

        $customdate   = '2022-04-01';
        $date2        = Carbon::today();
        $carbon_parse = Carbon::parse($customdate)->format("Ym");
        $table_name   = 't_lg' . $carbon_parse;
        $tb_head      = $this->findTable();
        $bool         = in_array($table_name, $tb_head, true);
        // dd($bool);
        if ($bool == true) {
            $results = DB::connection('mysql2')->table($table_name)->get();
            $columns = Schema::Connection('mysql2')->getColumnListing($table_name);
            // return $data;
            if (request()->ajax()) {
                $dateField = $request->dateField;
                // dd($date);
                $date1         = $request->dateField;
                $carbon_parse1 = Carbon::parse($dateField)->format("Ym");
                $table_name1   = 't_lg' . $carbon_parse1;
                $results       = DB::connection('mysql2')->table($table_name1)->get();
                return \view('admin.secondDb.table', compact('results'))->render();
            }

            return \view('admin.secondDb.sample', ['results' => $results, 'columns' => $columns, 'dateField' => $request->dateField]);
        } else {
            return redirect('sample')->with('error', 'Table not found');
        }
    }

    public function getTableColumns()
    {
        $date = '2022-06-01';
        // {   $date = $request->date;
        // $date  = Carbon::today()->format("Ym");
        $carbon_parse   = Carbon::parse($date)->format("Ym");
        $table_name     = 't_lg' . $carbon_parse;
        return $columns = Schema::Connection('mysql2')->getColumnListing($table_name);
    }

    public function demo()
    {
        Log::info("Controller is working fine!");
        return DB::Connection('sqlsrv')->table('dbo.sample')->get();
    }

    public function demo1()
    {
        Log::info("Controller is working fine!");
        $date = Carbon::now()->subDay(3)->format('Y-m-d');
        $time = Carbon::today()->subDay(3)->subHour(6)->format('H:i:s');
        $array = [4864, 4865, 4866, 4868, 4869, 4870, 4871, 4872];
        $carbon_parse = Carbon::parse($date)->format("Ym");
        // $carbon_today = Carbon::parse($date)->format("Y-m-d");
        $table_name = 't_lg' . $carbon_parse;
        // $tb_head = $this->findTable();
        // $bool = in_array($table_name, $tb_head, true);
        // $t_usr = DB::connection('mysql2')->table('t_usr')->get()->groupBy('ID');


        $results = DB::connection('mysql2')->table('t_usr')->select(
            't_usr.ID',
            't_usr.NM',
            DB::raw('(SELECT MIN(atteninfo.datetime)  FROM atteninfo
            WHERE  CAST(atteninfo.datetime AS DATE) = "' . $date . '" AND atteninfo.ID = t_usr.ID ) AS inTime'),
            DB::raw('(SELECT MAX(atteninfo.datetime) FROM atteninfo
                                                         WHERE CAST(atteninfo.datetime AS DATE) =  "' . $date . '" AND atteninfo.ID = t_usr.ID ) AS outTime')
        )
            ->join($table_name, $table_name . '.ID', '=', 't_usr.ID')
            ->whereDate('atteninfo', $date)
            ->whereTime('atteninfo', '>=', $time)
            ->whereBetween($table_name . '.EVT', [4096, 4129])
            ->orWhereBetween($table_name . '.EVT', [4864, 4872])
            ->groupBy('t_usr.ID', 't_usr.NM')
            ->get();
        $results = DB::connection('sqlsrv')->table('$table_name')->select(MIN('atteninfo'),  MAX('atteninfo'))->get();
        dd($results);
        foreach ($results as $result) {
            $inTime = date('Y-m-d H:i:s', strtotime($result->inTime));
            $outTime = date('Y-m-d H:i:s', strtotime($result->outTime));
            DB::table('employee_attendance')->insert([
                'employee_id' => $result->ID,
                'finger_print_id' => $result->ID,
                'in_out_time' => $inTime,
            ]);
            DB::table('employee_attendance')->insert([
                'employee_id' => $result->ID,
                'finger_print_id' => $result->ID,
                'in_out_time' => $outTime,
            ]);
        }
        return;
    }
    public function demo2()
    {
        Log::info("Controller is working fine!");
        $date = Carbon::today()->format('Y-m-d');
        $array = [4864, 4865, 4866, 4868, 4869, 4870, 4871, 4872];
        $carbon_parse = Carbon::parse($date)->format("Ym");

        $results = DB::connection('sqlsrv')->table('atteninfo')->select(
            'atteninfo.ID',
            'atteninfo.Empname',
            DB::raw("(SELECT MIN(atteninfo.datetime)  FROM atteninfo
            WHERE atteninfo.status = 'I' AND CAST(atteninfo.datetime AS DATE) = '" . $date . "'  ) AS inTime"),
            DB::raw("(SELECT MAX(atteninfo.datetime)  FROM atteninfo
            WHERE atteninfo.status = 'O' AND CAST(atteninfo.datetime AS DATE) = '" . $date . "'  ) AS outTime"),
        )
            ->where('atteninfo.date', $date)
            ->groupBy('atteninfo.ID', 'atteninfo.Empname',)
            ->get();

        dd($results);
        foreach ($results as $result) {
            $inTime = date('Y-m-d H:i:s', strtotime($result->inTime));
            $outTime = date('Y-m-d H:i:s', strtotime($result->outTime));
            DB::table('employee_attendance')->insert([
                'employee_id' => $result->ID,
                'finger_print_id' => $result->ID,
                'in_out_time' => $inTime,
            ]);
            DB::table('employee_attendance')->insert([
                'employee_id' => $result->ID,
                'finger_print_id' => $result->ID,
                'in_out_time' => $outTime,
            ]);
        }
        return;
    }

    public function insertLog1()
    {
        Log::info("Controller is working fine!");
        $date = Carbon::today()->subdays(2)->format('Y-m-d');
        $inPunch = [];
        $outPunch = [];
        $results = DB::connection('sqlsrv')->table('atteninfo')->select(
            'atteninfo.ID',
            'atteninfo.Empname',
            DB::raw("(SELECT MIN(atteninfo.datetime)  FROM atteninfo
            WHERE atteninfo.status = 'I' AND CAST(atteninfo.datetime AS DATE) = '" . $date . "'  ) AS inTime"),
            DB::raw("(SELECT MAX(atteninfo.datetime)  FROM atteninfo
            WHERE atteninfo.status = 'O' AND CAST(atteninfo.datetime AS DATE) = '" . $date . "'  ) AS outTime"),
        )
            ->where('atteninfo.date', $date)
            ->whereBetween('atteninfo.time', [Carbon::now(), Carbon::now()->subHours(1)])
            ->groupBy('atteninfo.ID', 'atteninfo.Empname',)
            ->where(\strlen('Empname'), '>', '2')
            ->get();
        //  dd($results);
        foreach ($results as $result) {
            $inTime = date('Y-m-d H:i:s', strtotime($result->inTime));
            $outTime = date('Y-m-d H:i:s', strtotime($result->outTime));
            $inPunch = DB::table('employee_attendance')->insert([

                'finger_print_id' => $result->ID,
                'in_out_time' => $inTime,
            ]);
            $outPunch = DB::table('employee_attendance')->insert([

                'finger_print_id' => $result->ID,
                'in_out_time' => $outTime,
            ]);
        }
        return [$outPunch, $inTime];
    }

    public function insertLog()
    {
        Log::info("Controller is working fine!");

        $date = Carbon::today()->subdays(2)->format('Y-m-d');
        $time = Carbon::today()->subDay(3)->subHour(6)->format('H:i:s');
        $array = [4864, 4865, 4866, 4868, 4869, 4870, 4871, 4872];
        $carbon_parse = Carbon::parse($date)->format("Ym");
        // $carbon_today = Carbon::parse($date)->format("Y-m-d");
        $table_name = 't_lg' . $carbon_parse;
        $inPunch = [];
        $outPunch = [];
        $results = DB::connection('sqlsrv')->table('atteninfo')->select(
            'atteninfo.ID',
            'atteninfo.Empname',
            DB::raw("(SELECT MIN(atteninfo.datetime)  FROM atteninfo
            WHERE atteninfo.status = 'I' AND CAST(atteninfo.datetime AS DATE) = '" . $date . "'  ) AS inTime"),
            DB::raw("(SELECT MAX(atteninfo.datetime)  FROM atteninfo
            WHERE atteninfo.status = 'O' AND CAST(atteninfo.datetime AS DATE) = '" . $date . "'  ) AS outTime"),
        )
            ->where('atteninfo.date', $date)
            ->whereBetween('atteninfo.time', [Carbon::now(), Carbon::now()->subHours(1)])
            ->groupBy('atteninfo.ID', 'atteninfo.Empname',)
            ->where(\strlen('Empname'), '>', '2')
            ->get();
        //  dd($results);
        foreach ($results as $result) {
            $inTime = date('Y-m-d H:i:s', strtotime($result->inTime));
            $outTime = date('Y-m-d H:i:s', strtotime($result->outTime));
            $inPunch = DB::table('employee_attendance')->insert([

                'finger_print_id' => $result->ID,
                'in_out_time' => $inTime,
            ]);
            $outPunch = DB::table('employee_attendance')->insert([

                'finger_print_id' => $result->ID,
                'in_out_time' => $outTime,
            ]);
        }
        return [$outPunch, $inTime];
    }

    public function logRegularShift()
    {
        Log::info("Controller is working fine!");
        $date = Carbon::now()->subDay(3)->format('Y-m-d');
        $time = Carbon::today()->subDay(3)->subHour(6)->format('H:i:s');
        $array = [4864, 4865, 4866, 4868, 4869, 4870, 4871, 4872];
        $carbon_parse = Carbon::parse($date)->format("Ym");
        // $carbon_today = Carbon::parse($date)->format("Y-m-d");
        $table_name = 't_lg' . $carbon_parse;
        // $tb_head = $this->findTable();
        // $bool = in_array($table_name, $tb_head, true);
        // $t_usr = DB::connection('mysql2')->table('t_usr')->get()->groupBy('ID');


        $results = DB::connection('mysql2')->table('t_usr')->select(
            't_usr.ID',
            't_usr.NM',
            DB::raw("(SELECT MIN(atteninfo.datetime)  FROM atteninfo
            WHERE  CAST(atteninfo.datetime AS DATE) = '" . $date . "' AND atteninfo.ID = t_usr.ID ) AS inTime"),
            DB::raw('(SELECT MAX(atteninfo.datetime) FROM atteninfo
                                                         WHERE CAST(atteninfo.datetime AS DATE) =  "' . $date . '" AND atteninfo.ID = t_usr.ID ) AS outTime')
        )
            ->join($table_name, $table_name . '.ID', '=', 't_usr.ID')
            ->whereDate('atteninfo', $date)
            ->whereTime('atteninfo', '>=', $time)
            ->whereBetween($table_name . '.EVT', [4096, 4129])
            ->orWhereBetween($table_name . '.EVT', [4864, 4872])
            ->groupBy('t_usr.ID', 't_usr.NM')
            ->get();
        $results = DB::connection('sqlsrv')->table('$table_name')->select(MIN('atteninfo'),  MAX('atteninfo'))->get();
        dd($results);
        foreach ($results as $result) {
            $inTime = date('Y-m-d H:i:s', strtotime($result->inTime));
            $outTime = date('Y-m-d H:i:s', strtotime($result->outTime));
            DB::table('employee_attendance')->insert([
                'employee_id' => $result->ID,
                'finger_print_id' => $result->ID,
                'in_out_time' => $inTime,
            ]);
            DB::table('employee_attendance')->insert([
                'employee_id' => $result->ID,
                'finger_print_id' => $result->ID,
                'in_out_time' => $outTime,
            ]);
        }
        return;
    }
    public function logNightShift()
    {
        Log::info("Controller is working fine!");
        $date = Carbon::now()->subDay(3)->format('Y-m-d');
        $time = Carbon::today()->subDay(3)->subHour(6)->format('H:i:s');
        $array = [4864, 4865, 4866, 4868, 4869, 4870, 4871, 4872];
        $carbon_parse = Carbon::parse($date)->format("Ym");
        // $carbon_today = Carbon::parse($date)->format("Y-m-d");
        $table_name = 't_lg' . $carbon_parse;
        // $tb_head = $this->findTable();
        // $bool = in_array($table_name, $tb_head, true);
        // $t_usr = DB::connection('mysql2')->table('t_usr')->get()->groupBy('ID');


        $results = DB::connection('mysql2')->table('t_usr')->select(
            't_usr.ID',
            't_usr.NM',
            DB::raw("(SELECT MIN(atteninfo.datetime)  FROM atteninfo
            WHERE  CAST(atteninfo.datetime AS DATE) = '" . $date . "' AND atteninfo.ID = t_usr.ID ) AS inTime"),
            DB::raw('(SELECT MAX(atteninfo.datetime) FROM atteninfo
                                                         WHERE CAST(atteninfo.datetime AS DATE) =  "' . $date . '" AND atteninfo.ID = t_usr.ID ) AS outTime')
        )
            ->join($table_name, $table_name . '.ID', '=', 't_usr.ID')
            ->whereDate('atteninfo', $date)
            ->whereTime('atteninfo', '>=', $time)
            ->whereBetween($table_name . '.EVT', [4096, 4129])
            ->orWhereBetween($table_name . '.EVT', [4864, 4872])
            ->groupBy('t_usr.ID', 't_usr.NM')
            ->get();
        $results = DB::connection('sqlsrv')->table('$table_name')->select(MIN('atteninfo'),  MAX('atteninfo'))->get();
        dd($results);
        foreach ($results as $result) {
            $inTime = date('Y-m-d H:i:s', strtotime($result->inTime));
            $outTime = date('Y-m-d H:i:s', strtotime($result->outTime));
            DB::table('employee_attendance')->insert([
                'employee_id' => $result->ID,
                'finger_print_id' => $result->ID,
                'in_out_time' => $inTime,
            ]);
            DB::table('employee_attendance')->insert([
                'employee_id' => $result->ID,
                'finger_print_id' => $result->ID,
                'in_out_time' => $outTime,
            ]);
        }
        return;
    }

    public function logReport()
    {
        $results = Employee::with('workShift')->get();
        // $results = Employee::with(['workShift' => function($q){
        //     $q->where('shift_name', '=', 'Night');
        // }])->get();
        foreach ($results as $value) {
            $data[] = [
                'data' =>  $value['workShift']['shift_name'],
                'value' =>  $value
            ];
        }
        return $data;

        // return $results;
    }

    public function logs()
    {
        Log::info("Controller is working fine!");
        $date = Carbon::now()->subDay(3)->format('Y-m-d');
        $time = Carbon::today()->subDay(3)->subHour(6)->format('H:i:s');
        $timeNow = Carbon::now()->subDay(3)->format('H:i:s');
        $array = [4864, 4865, 4866, 4868, 4869, 4870, 4871, 4872];
        $carbon_parse = Carbon::parse($date)->format("Ym");
        // $carbon_today = Carbon::parse($date)->format("Y-m-d");
        $table_name = 't_lg' . $carbon_parse;

        $results = DB::connection('sqlsrv')->table('atteninfo')->select(
            'atteninfo.ID',
            'atteninfo.Empname',
            DB::raw("(SELECT MIN(atteninfo.datetime)  FROM atteninfo
            WHERE atteninfo.status = 'I' AND CAST(atteninfo.datetime AS DATE) = '" . $date . "'  ) AS inTime"),
            DB::raw("(SELECT MAX(atteninfo.datetime)  FROM atteninfo
            WHERE atteninfo.status = 'O' AND CAST(atteninfo.datetime AS DATE) = '" . $date . "'  ) AS outTime"),
        )
            ->where('atteninfo.date', $date)
            ->whereBetween('atteninfo.time', [Carbon::now(), Carbon::now()->subHours(1)])
            ->groupBy('atteninfo.ID', 'atteninfo.Empname',)
            ->where(\strlen('Empname'), '>', '2')
            ->get();

        $results = DB::connection('sqlsrv')->table('t_usr')->select(
            't_usr.USRID',
            't_usr.NM',
            DB::raw("(SELECT MIN(" . $table_name . ".SRVDT)  FROM " . $table_name . "
                WHERE  CAST(" . $table_name . ".SRVDT AS DATE) = '" . $date . "' AND " . $table_name . ".USRID = t_usr.USRID ) AS inTime"),
            DB::raw("(SELECT MAX(" . $table_name . ".SRVDT) FROM " . $table_name . "
                                                             WHERE CAST(" . $table_name . ".SRVDT AS DATE) =  '" . $date . "' AND " . $table_name . ".USRID = t_usr.USRID ) AS outTime")
        )
            ->join($table_name, $table_name . '.USRID', '=', 't_usr.USRID')
            ->whereDate($table_name . '.SRVDT', $date)
            ->whereTime($table_name . '.SRVDT', '<=', Carbon::now()->format('H:i:s'))
            ->whereBetween($table_name . '.EVT', [4096, 4129])
            ->orWhereBetween($table_name . '.EVT', [4864, 4872])
            ->groupBy('t_usr.USRID', 't_usr.NM')
            ->get();
        return $results;
    }

    public function report()
    {
        // $results = EmployeeAttendance::join('employee', 'employee.finger_id', '=', 'employee_attendance.finger_print_id')
        //     ->select('employee_attendance.finger_print_id', min(['employee_attendance.in_out_time as inTime']), max(['employee_attendance.in_out_time as outTime']), DB::raw("DATE_FORMAT(employee_attendance.in_out_time, '%d-%M-%Y') as date"))
        //     ->groupBy('employee_attendance.finger_print_id', 'employee_attendance.in_out_time')
        //     ->get();
        // abs(value - $myvalue)
        $date = Carbon::today()->format('Y-m-d');
        $results = DB::table('employee_attendance')->select(
            'employee_attendance.finger_print_id',
            DB::raw("(SELECT DATE_FORMAT(MIN(employee_attendance.in_out_time), '%H:%I')  FROM employee_attendance
            WHERE CAST(employee_attendance.in_out_time AS DATE) = '" . $date . "' AND employee_attendance.finger_print_id = employee.finger_id) AS inTime"),
            DB::raw("(SELECT DATE_FORMAT(MAX(employee_attendance.in_out_time), '%H:%I')  FROM employee_attendance
            WHERE CAST(employee_attendance.in_out_time AS DATE) = '" . $date . "'  AND employee_attendance.finger_print_id = employee.finger_id) AS outTime"),
        )
            ->join('employee', 'employee_attendance.finger_print_id', '=', 'employee.finger_id')
            ->join('work_shift', 'work_shift.work_shift_id', '=', 'employee.work_shift_id')
            ->whereDate('employee_attendance.in_out_time', $date)
            ->groupBy('employee_attendance.finger_print_id')
            ->get();

        $results = DB::table('employee_attendance')->select(
            'employee_attendance.finger_print_id',
            DB::raw("(SELECT DATE_FORMAT(MIN(employee_attendance.in_out_time), '%H:%I')  FROM employee_attendance
            WHERE CAST(employee_attendance.in_out_time AS DATE) = '" . $date . "' AND employee_attendance.finger_print_id = employee.finger_id) AS inTime"),
            DB::raw("(SELECT DATE_FORMAT(MAX(employee_attendance.in_out_time), '%H:%I')  FROM employee_attendance
            WHERE CAST(employee_attendance.in_out_time AS DATE) = '" .  $date . "'  AND employee_attendance.finger_print_id = employee.finger_id) AS outTime"),
        )
            ->join('employee', 'employee_attendance.finger_print_id', '=', 'employee.finger_id')
            ->join('work_shift', 'work_shift.work_shift_id', '=', 'employee.work_shift_id')
            ->whereDate('employee_attendance.in_out_time', $date)
            ->groupBy('employee_attendance.finger_print_id')
            ->get();
        $start_time = WorkShift::join('employee', 'work_shift.work_shift_id', '=', 'employee.work_shift_id')->where('employee.employee_id', 1)->select('work_shift.start_time')->first();
        $end_time = WorkShift::join('employee', 'work_shift.work_shift_id', '=', 'employee.work_shift_id')->where('employee.employee_id', 1)->select('work_shift.end_time')->first();

        dump([$start_time->start_time, $end_time->end_time]);


        return $results;
    }


    public function inOutData(Request $request)
    {
        $today = Carbon::today()->subDay(3);
        $tomorrow = Carbon::today()->subDay(3)->addDay(1)->addHours(8);
        // dd($today,$tomorrow);
        $request->finger_id = 1001;
        $results = EmployeeAttendance::select('in_out_time')->where('finger_print_id', $request->finger_id)
            ->whereBetween('in_out_time', [$today, $tomorrow])
            // ->where('in_out_time', '>=', $today)
            ->orderByDesc('in_out_time')->get();

        // foreach ($results as $key => $val) {
        //     // dd($val->in_out_time);
        //     $d1 = new DateTime($val->in_out_time);
        //     $d2 = new DateTime($val->in_out_time);
        //     // dd($d1->format('Y-m-d H:i:s'));
        // }
        // $index = 0;

        // for ($i = 0; $i < count($results); $i++) {
        //     // return \response()->json(['results' => $results[0]->in_out_time]);
        //     $index++;
        //     $d1 = new DateTime($results[$i]->in_out_time);
        //     $d2 = new DateTime($results[$i + 1]->in_out_time);
        //     dd($results[$i+1]);
        // }



        // $number = $d1->diff($d2);
        // return  $number->format('%H:%I');
        return $results;
    }


    
}
