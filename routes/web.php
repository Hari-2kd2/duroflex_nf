<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
// Auth
Auth::routes();

// Developer Routes

Route::get('/sample', 'Attendance\GenerateReportController@calculate_attendance');

Route::get('/clear', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('debugbar:clear');
    return redirect('/login'); //Return anything
});

// front page route
Route::get('/', 'Front\WebController@index');
Route::get('job/{id}/{slug?}', 'Front\WebController@jobDetails')->name('job.details');
Route::post('job-application', 'Front\WebController@jobApply')->name('job.application');
Route::get('admin/pushSwitch', function (Request $request) {
    DB::table('sync_to_live')->where('id', $request->id)->update(['status' => $request->status]);
});
// front page route

Route::get('login', 'User\LoginController@index');
Route::post('login', 'User\LoginController@Auth');

Route::get('mail', 'User\HomeController@mail');

Route::group(['prefix' => 'password'], function () {
    Route::get('/validatePassword', 'User\ForgotPasswordController@validatePassword');
    Route::get('/reset-password', 'User\ResetPasswordController@create');
    Route::get('/change-password', 'User\ResetPasswordController@store');
});

Route::group(['middleware' => ['preventbackbutton', 'auth']], function () {
    Route::get('sample/{employee_id}', 'SampleController@sample');
    Route::get('ot/{date}', 'OverTime\OverTimeController@samp');
    Route::get('dashboard', 'User\HomeController@index');
    Route::get('profile', 'User\HomeController@profile');
    Route::get('logout', 'User\LoginController@logout');
    Route::get('ajaxlogout', 'User\LoginController@ajaxlogout');
    Route::resource('user', 'User\UserController', ['parameters' => ['user' => 'user_id']]);
    Route::resource('userRole', 'User\RoleController', ['parameters' => ['userRole' => 'role_id']]);
    Route::resource('rolePermission', 'User\RolePermissionController', ['parameters' => ['rolePermission' => 'id']]);
    Route::post('rolePermission/get_all_menu', 'User\RolePermissionController@getAllMenu');
    Route::resource('changePassword', 'User\ChangePasswordController', ['parameters' => ['changePassword' => 'id']]);
});

Route::group(['prefix' => 'cronjob'], function () {
    Route::get('log', 'View\EmployeeAttendaceController@fetchRawLog');
    Route::get('report', 'View\EmployeeAttendaceController@attendance');
    Route::get('manualLogrun', 'View\ManualAttendanceReportController@fetchRawLog');
    // Route::get('manualLog', 'Employee\AccessController@log')->name('cronjob.manualLog');
    // Route::get('/manualLog', ['as' => 'cronjob.manualLog', 'uses' => 'Employee\AccessController@log']);
    Route::get('manualAttendance', 'View\ManualAttendanceReportController@attendance');
    Route::get('trainingEmployee', 'View\ManualAttendanceReportController@training');
    Route::get('newEmployees', 'View\EmployeeAttendaceController@newEmployee');
});

Route::get('ms_sql', 'Controller@ms_sql');
Route::get('testlog', 'Employee\DeviceController@testlog');
Route::post('testlog', 'Employee\DeviceController@testlog');

Route::get('local/{language}', function ($language) {

    session(['my_locale' => $language]);

    return redirect()->back();
});

Route::get('email', 'Mail\AttendanceMailController@index');
