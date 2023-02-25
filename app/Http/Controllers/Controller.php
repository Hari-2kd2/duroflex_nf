<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Model\MsSql;
use App\Model\Employee;
use App\Model\Department;
use Illuminate\Http\Request;
use App\Exports\DailyAttendance;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\AttendanceRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function success($message, $data)
    {
        return response()->json([
            'status'  => \true,
            'message' => $message,
            'data' => $data,
        ], 200);
    }

    public function successdualdata($message, $data, $list)
    {
        return response()->json([
            'status'  => \true,
            'message' => $message,
            'data' => $data,
            'list' => $list,
        ], 200);
    }

    public function error()
    {
        return response()->json([
            'status'  => \false,
            'message' => "Something error found !, Please try again.",
        ], 200);
    }

    public function custom_error($custom_message)
    {
        return response()->json([
            'status'  => \false,
            'message' => $custom_message,
        ], 200);
    }

    public function ms_sql(Request $request)
    {
        // $attendanceRepository = new AttendanceRepository;

        // $departmentList = Department::get();
        // $results        = [];
        // if ($_POST) {
        //     $results = $this->attendanceRepository->getEmployeeDailyAttendance($request->date, $request->department_id);
        // }

        // $data = ['results' => $results, 'departmentList' => $departmentList, 'date' => $request->date, 'department_id' => $request->department_id];
        // \set_time_limit(0);
        // return Excel::download(new DailyAttendance($data), 'attendance.xlsx');

        $ms_sql2 = DB::table('ms_sql2')->orderBy('primary_id', 'asc')->where('primary_id', '>=', 5001)->limit('5000')->get();
        $insertData = [];
        foreach ($ms_sql2 as $key => $log) {

            $insertData[] = [
                'primary_id'    => $log->primary_id,
                'evtlguid'      => $log->evtgluid,
                'datetime'      => $log->datetime,
                'ID'            => $log->ID,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
                'device_name'    => 'biometric',
                'status' => 0,
                'type' => \strtoupper($log->type),
            ];
        }

        MsSql::insert($insertData);

        // $ms_sql2Emp = DB::table('ms_sql2')->groupby('ID')->get();

        // foreach ($ms_sql2Emp as $key => $emp) {
        //     Employee::insert([
        //         'finger_id' => $emp->ID,
        //         'first_name' => $emp->ID,
        //         'status' => 1,
        //     ]);
        // }
    }
}
