<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::get('/optimize-clear', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('debugbar:clear');
    return response()->json(['status' => true, 'message' => 'Success'], 200); //Return anything
});

Route::get('api-test', function () {
    return "oh Yaa";
});

Route::post('attendnance', 'Attendance\ApiAttendanceController@store');

Route::post('addEmployee', 'Api\EmployeeController@add');
Route::post('editEmployee', 'Api\EmployeeController@update');
Route::post('deleteEmployee', 'Api\EmployeeController@destroy');

Route::post('addDepartment', 'Api\DepartmentController@add');
Route::post('editDepartment', 'Api\DepartmentController@update');
Route::post('deleteDepartment', 'Api\DepartmentController@destroy');

Route::post('addDevice', 'Api\DeviceController@add');
Route::post('editDevice', 'Api\DeviceController@update');
Route::post('importlogs', 'Api\DeviceController@importlogs');
Route::post('deleteDevice', 'Api\DeviceController@destroy');

Route::post('importattendance', 'Api\AttendanceController@import');
Route::get('reporthistory', 'Api\AttendanceController@reporthistory');
Route::get('loghistory', 'Api\AttendanceController@loghistory');
Route::get('history', 'Api\AttendanceController@history');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
