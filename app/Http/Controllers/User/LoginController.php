<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Lib\Enumerations\UserStatus;
use App\Model\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{

    public function index()
    {
        if (Auth::check()) {
            return redirect()->intended(url('/dashboard'));
        }

        return view('admin.login');

    }

    public function Auth(LoginRequest $request)
    {
        if (Auth::attempt(['user_name' => $request->user_name, 'password' => $request->user_password])) {
            $userStatus = Auth::user()->status;
            if ($userStatus == UserStatus::$ACTIVE) {
                $employee = Employee::where('user_id', Auth::user()->user_id)->first();
                $user_data = [
                    "user_id" => Auth::user()->user_id,
                    "user_name" => Auth::user()->user_name,
                    "role_id" => Auth::user()->role_id,
                    "employee_id" => $employee->employee_id,
                    "email" => $employee->email,
                ];
                session()->put('logged_session_data', $user_data);
                return redirect()->intended(url('/dashboard'));
            } elseif ($userStatus == UserStatus::$INACTIVE) {
                Auth::logout();
                return redirect(url('login'))->withInput()->with('error', 'You are temporary blocked. please contact to admin');
            } else {
                Auth::logout();
                return redirect(url('login'))->withInput()->with('error', 'You are terminated. please contact to admin');
            }
        } else {
            return redirect(url('login'))->withInput()->with('error', 'User name or password does not matched');
        }
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();
        return redirect(url('login'))->with('success', 'logout successful ..!');
    }

    public function ajaxlogout()
    {
        try {
            Auth::logout();
            Session::flush();
            return 'success';
        } catch (\Throwable $th) {
            return 'error';
        }
    }

}
