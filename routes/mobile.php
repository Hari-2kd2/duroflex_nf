<?php

use Illuminate\Support\Facades\Route;


Route::group(['middleware' => 'api', 'prefix' => 'mobile'], function () {
    Route::post('login', 'Api\AuthController@login');
    Route::post('register', 'Api\AuthController@register');
    Route::get('logout', 'Api\AuthController@logout');
    Route::get('refresh', 'Api\AuthController@refresh');
    Route::get('migrate', 'Api\AuthController@migrate');
    Route::get('sample', 'Api\AuthController@sample');

    Route::group(['prefix' => 'attendance'], function () {
        Route::post('employee_attendance_in', 'Api\AttendanceController@apiattendance');
        Route::post('employee_attendance_out', 'Api\AttendanceController@apiattendance');
        Route::post('employee_attendance_list', 'Api\AttendanceController@apiattendancelist');
        Route::get('my_attendance_report', 'Api\AttendanceReportController@myAttendanceReport');
        Route::get('download_my_attendance', 'Api\AttendanceReportController@downloadMyAttendance');
        Route::get('sample', 'Api\AttendanceController@sample');
    });

    Route::group(['prefix' => 'leave'], function () {
        Route::get('index', 'Api\ApplyForLeaveController@index');
        Route::get('create', 'Api\ApplyForLeaveController@create');
        Route::post('store', 'Api\ApplyForLeaveController@store');
        Route::post('update', 'Api\ApplyForLeaveController@update');
        Route::get('sample', 'Api\ApplyForLeaveController@sample');
    });

    Route::group(['prefix' => 'payroll'], function () {
        Route::get('my_payroll', 'Api\PayslipController@myPayroll');
        Route::get('download_my_payroll', 'Api\PayslipController@downloadMyPayroll');
        Route::get('payslip', 'Api\PayslipController@payslip');
        Route::get('downloadPayslip', 'Api\PayslipController@downloadPayslip');
    });
});
