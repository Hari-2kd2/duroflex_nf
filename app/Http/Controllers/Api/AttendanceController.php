<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Model\MsSql;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Model\EmployeeInOutData;
use App\Model\EmployeeAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Repositories\ApiAttendanceRepository;

class AttendanceController extends Controller
{

    public function import(Request $request)
    {

        DB::beginTransaction();
        $att = new EmployeeInOutData();
        $att->employee_attendance_id = $request->employee_attendance_id;
        $att->finger_print_id = $request->finger_print_id;
        $att->date = $request->date;
        $att->in_time_from = $request->in_time_from;
        $att->in_time = $request->in_time;
        $att->out_time = $request->out_time;
        $att->out_time_upto = $request->out_time_upto;
        $att->working_time = $request->working_time;
        $att->working_hour = $request->working_hour;
        $att->status = $request->status;
        $att->created_at = $request->created_at;
        $att->updated_at = $request->updated_at;
        $att->in_out_time = $request->in_out_time;
        $att->save();
        DB::commit();

        return json_encode(['status' => 'success', 'message' => 'Attendance imported Successfully updated !'], 200);
    }

    public function reporthistory()
    {
        $localReportID = EmployeeInOutData::orderBy('employee_attendance_id', 'DESC')->first();
        return json_encode(['status' => 'success', 'message' => 'Successfully updated !', 'data' => $localReportID], 200);
    }
    public function loghistory()
    {
        $localLogID = MsSql::orderBy('primary_id', 'DESC')->first();
        return json_encode(['status' => 'success', 'message' => 'Successfully updated !', 'data' => $localLogID], 200);
    }

    protected $apiAttendanceRepository;

    public function __construct(ApiAttendanceRepository $apiAttendanceRepository)
    {
        $this->apiAttendanceRepository = $apiAttendanceRepository;
    }

    public function sample(Request $request)
    {

        $full_data = date('Y-m-d');
        $date = \explode('-', $full_data);
        return response()->json([
            'message' => "API works fine",
            'date' => $date[2],
        ], 200);
    }

    public function apiattendance(Request $request)
    {

        $base64_image = $request->face_id;

        $uri = Route::getFacadeRoot()->current()->uri();

        if ($base64_image != 'null' && isset($base64_image)) {
            @list($type, $file_data) = explode(';', $base64_image);
            @list(, $file_data) = explode(',', $file_data);
            $imageName = \md5(Str::random(30) . time() . '_' . uniqid()) . '.' . 'jpg';
            $employeePhoto['face_id'] = $imageName;
            Storage::disk('faceid')->put($imageName, base64_decode($base64_image));
        }

        $employeeAtendanceDataFormat = $this->apiAttendanceRepository->makeEmployeeAttendacneInformationDataFormat($uri, $request->all());

        if ($employeeAtendanceDataFormat == false) {
            return response()->json([
                'status' => \false,
                'message' => 'Something Error Found !, Please try again.',
            ], 200);
        }

        if (isset($employeePhoto)) {
            $employeeData = $employeeAtendanceDataFormat + $employeePhoto;
        } else {
            $employeeData = $employeeAtendanceDataFormat;
        }

        try {

            DB::beginTransaction();
            $attendanceData = EmployeeAttendance::create($employeeData);

            $ms_sql = MsSql::create([
                'ID' => $employeeData['finger_print_id'],
                'datetime' => $employeeData['in_out_time'],
                'type' => $employeeData['check_type'],
                'employee' => $employeeData['employee_id'],
                'device_name' => 'mobile',
            ]);

            DB::commit();
            $bug = 0;
        } catch (\Exception$e) {
            DB::rollback();
            $bug = 1;
        }

        if ($bug == 0) {
            return response()->json([
                'status' => \true,
                'message' => 'Employee attendance successfully saved.',
                'data' => $attendanceData,
            ], 200);
        } else {
            return response()->json([
                'status' => \false,
                'message' => 'Something Error Found !, Please try again.',
                'data' => $attendanceData,
            ], 200);
        }
    }

    public function apiattendanceList(Request $request)
    {

        $array = \json_decode($request->data);
        $count = count($array);
        foreach ($array as $key => $value) {

            $base64_image = $value->face_id;

            if ($base64_image != 'null') {
                @list($type, $file_data) = explode(';', $base64_image);
                @list(, $file_data) = explode(',', $file_data);
                $imageName = \md5(str_random(30) . time() . '_' . uniqid()) . '.' . 'jpg';
                $employeePhoto['face_id'] = $imageName;
                Storage::disk('faceid')->put($imageName, base64_decode($base64_image));
            }

            $employeeAtendanceDataFormat = $this->apiAttendanceRepository->makeBulkEmployeeAttendacneInformationDataFormat($value);

            if (isset($employeePhoto)) {
                $employeeData = $employeeAtendanceDataFormat + $employeePhoto;
            } else {
                $employeeData = $employeeAtendanceDataFormat;
            }

            try {

                DB::beginTransaction();
                $attendanceData = EmployeeAttendance::create($employeeData);
                $ms_sql = MsSql::create([
                    'ID' => $employeeData['finger_print_id'],
                    'datetime' => $employeeData['in_out_time'],
                    'type' => $employeeData['check_type'],
                    'employee' => $employeeData['employee_id'],
                    'device_name' => 'mobile',
                ]);
                DB::commit();
                $bug = 0;
            } catch (\Exception$e) {
                return $e;
                DB::rollback();
                $bug = 1;
            }
        }

        if ($bug == 0) {
            return response()->json([
                'status' => \true,
                'count' => $count,
                'message' => 'Employee attendance successfully saved.',
                'data' => $attendanceData,
            ], 200);
        } else {
            return response()->json([
                'status' => \false,
                'message' => 'Something Error Found !, Please try again.',
                'data' => $attendanceData,
            ], 200);
        }
    }

    public function history()
    {
        for ($j = -2; $j <= 0; $j++) {
            $month[] = date('Y-m', strtotime("$j month"));
        }

        foreach ($month as $i) {

            $tempArr['date'] = date($i . '-d');
            $tempArr['month'] = date($i);
            $tempArr['carbon_parse'] = Carbon::parse($tempArr['date'])->format("Ym");
            $tempArr['tableName'] = 'T_LG' . $tempArr['carbon_parse'];

            $tempArr['mySql'] = DB::table('ms_sql')->whereRaw("datetime >= '" . date('Y-m-01 00:00:00', strtotime($i)) . "' AND datetime <= '" . date('Y-m-t 00:00:00', strtotime($i)) . "'")->count();

            if ($tempArr['mySql'] == 0) {
                $tempArr['mySql'] = '0';
            }

            $dataSet[] = $tempArr;

        }

        return response()->json(['data' => $dataSet]);
    }

    public function apiattendanceIn(Request $request)
    {
        Log::info("apiattendance");

        $face_id = $request->file('face_id');

        if ($face_id) {
            $imgName = md5(str_random(30) . time() . '_' . $request->file('face_id')) . '.' . $request->file('face_id')->getClientOriginalExtension();
            $request->file('face_id')->move('uploads/faceId/', $imgName);
            $employeePhoto['face_id'] = $imgName;
        }

        $status = \false;

        $employeeAtendacneDataFormat = $this->apiAttendanceRepository->makeEmployeeAttendacneInformationDataFormat($status, $request->all());

        if (isset($employeePhoto)) {
            $employeeData = $employeeAtendacneDataFormat + $employeePhoto;
        } else {
            $employeeData = $employeeAtendacneDataFormat;
        }

        try {

            DB::beginTransaction();
            $attendanceData = EmployeeAttendance::create($employeeData);
            DB::commit();
            $bug = 0;
        } catch (\Exception$e) {
            return $e;
            DB::rollback();
            $bug = 1;
        }

        if ($bug == 0) {
            return response()->json([
                'status' => \true,
                'message' => 'Employee attendance successfully saved.',
                'data' => $attendanceData,
            ], 200);
        } else {
            return response()->json([
                'status' => \false,
                'message' => 'Something Error Found !, Please try again.',
                'data' => $attendanceData,
            ], 200);
        }
    }

}
