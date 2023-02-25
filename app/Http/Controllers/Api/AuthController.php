<?php

namespace App\Http\Controllers\Api;

use App\User;
use Carbon\Carbon;
use App\Model\Employee;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\EmployeeRequest;
use DateTime;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'migrate', 'sample']]);
    }

    public function sample(Request $request)
    {
        $var  = Carbon::now('Asia/Kolkata');
        $time = $var->toTimeString();
        return response()->json([
            'message' => "API works fine",
            'date' =>  \date('y-m-d H:i:s'),
            'time' =>  $time,
        ], 200);
    }

    public function login(Request $request)
    {

        $credentials = ['user_name' => $request->user_name, 'password' => $request->password];


        if ($token = JWTAuth::attempt($credentials)) {

            $userStatus = Auth::user()->status;

            if ($userStatus == UserStatus::$ACTIVE) {

                $employee = Employee::where('user_id', Auth::user()->user_id)->first();

                $user_data = [
                    "user_id"     => Auth::user()->user_id,
                    "user_name"   => Auth::user()->user_name,
                    "role_id"     => Auth::user()->role_id,
                    "employee_id" => $employee->employee_id,
                    "finger_id" => $employee->finger_id,
                ];

                return response()->json([
                    'message'      => "Login Successful !!!",
                    'status'       => true,
                    'access_token' => $token,
                    'user'         => $user_data,
                ], 200);
            } elseif ($userStatus == UserStatus::$INACTIVE) {

                Auth::logout();

                return response()->json([
                    'status'  => false,
                    'message' => 'You are temporary blocked. please contact to admin'
                ], 200);
            } else {

                Auth::logout();

                return response()->json([
                    'status'  => false,
                    'message' => 'You are terminated. please contact to admin'
                ], 200);
            }
        } else {

            return response()->json([
                'status'  => false,
                'message' => 'User name or password does not matched'
            ], 200);
        }
    }

    public function register(EmployeeRequest $employeeRequest, UserRequest $userRequest)
    {
        $now = Carbon::now();

        $user = User::create([
            'user_name' => $userRequest['user_name'],
            'password'  => Hash::make($userRequest['password']),
            'role_id'   => $userRequest['role_id'],
        ]);

        $employee = Employee::create([
            'first_name'      => $user->user_name,
            'finger_id'      => $employeeRequest['finger_id'],
            'user_id'         => $user->user_id,
            'department_id'   => $employeeRequest['department_id'],
            'designation_id'  => $employeeRequest['designation_id'],
            'branch_id'       => $employeeRequest['branch_id'],
            'supervisor_id'   => $employeeRequest['supervisor_id'],
            'work_shift_id'   => $employeeRequest['work_shift_id'],
            'pay_grade_id'    => $employeeRequest['pay_grade_id'],
            'work_shift_id'   => $employeeRequest['work_shift_id'],
            'date_of_birth'   => $employeeRequest['date_of_birth'],
            'date_of_joining' => $employeeRequest['date_of_joining'],
            'gender'          => $employeeRequest['gender'],
            'phone'           => $employeeRequest['phone'],
            'status'          => $employeeRequest['status'],
            'created_by'      => 1,
            'updated_by'      => 1,
        ]);

        return response()->json([
            'message' => 'User successfully registered',
            'user'    => $user,
        ], 201);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    protected function createNewToken($token)
    {

        $employee = Employee::where('user_id', Auth::user()->user_id)->first();

        $user_data = [
            "user_id"     => Auth::user()->user_id,
            "user_name"   => Auth::user()->user_name,
            "role_id"     => Auth::user()->role_id,
            "employee_id" => $employee->employee_id,
            "finger_id" => $employee->finger_id,
        ];
        return response()->json([
            'message'      => "Authentication Successful !!!",
            'status'       => true,
            'access_token' => $token,
            'user'         => $user_data,
        ], 200);
    }

    public function migrate(Request $request)
    {
        // $migrate = Artisan::call('migrate:fresh');
        // return response()->json([
        //     'message' => "Migration Success",
        //     "migrate" => $migrate,
        // ]);

        return response()->json([
            'message' => "migration process disables",
        ], 200);
    }
}
