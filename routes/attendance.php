<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::get('/mobile_attendance/{employee_id}', ['as' => 'mobileAttendance.mobile_attendance', 'uses' => 'Attendance\MobileAttendanceController@mobileAttendanceReport']);

    Route::get('mobileAttendance', ['as' => 'mobileAttendance.mobileAttendance', 'uses' => 'Attendance\MobileAttendanceController@mobileAttendance']);
    Route::post('mobileAttendance', ['as' => 'mobileAttendance.mobileAttendance', 'uses' => 'Attendance\MobileAttendanceController@mobileAttendance']);
    Route::get('downloadDailyAttendanceExcel', 'Attendance\AttendanceReportController@dailyExcel');
    Route::get('downloadMonthlyAttendanceExcel', 'Attendance\AttendanceReportController@monthlyExcel');
    Route::get('downloadSummaryAttendanceExcel', 'Attendance\AttendanceReportController@summaryExcel');
    // Route::get('downloadMusterAttendanceExcel', 'Attendance\AttendanceReportController@musterExcel');
    Route::get('downloadMusterAttendanceExcel', 'Attendance\AttendanceReportController@musterExcelExportFromCollection');
});

Route::group(['middleware' => ['preventbackbutton', 'auth']], function () {

    Route::get('uploadAttendance', ['as' => 'uploadAttendance.uploadAttendance', 'uses' => 'Attendance\AttendanceImportController@index']);
    Route::post('uploadAttendance/import', ['as' => 'uploadAttendance.import', 'uses' => 'Attendance\AttendanceImportController@import']);
    Route::get('uploadAttendance/export', ['as' => 'uploadAttendance.export', 'uses' => 'Attendance\AttendanceImportController@export']);
    Route::get('uploadAttendance/export/{type}', ['as' => 'uploadAttendance.export', 'uses' => 'Attendance\AttendanceImportController@export']);
    Route::get('uploadAttendance/downloadFile', ['as' => 'uploadAttendance.downloadFile', 'uses' => 'Attendance\AttendanceImportController@downloadFile']);
    // Route::get('/', ['as' => 'uploadAttendance.index', 'uses' => 'Attendance\AttendanceImportController@index']);
    // Route::post('/import', 'Attendance\AttendanceImportController@import')->name('uploadAttendance.import');
    // Route::get('/export', ['as' => 'uploadAttendance.export', 'uses' => 'Attendance\AttendanceImportController@export'])->name('export');
    // Route::get('export/{type}', 'Attendance\AttendanceImportController@export')->name('export');

    Route::group(['prefix' => 'workShift'], function () {
        Route::get('/', ['as' => 'workShift.index', 'uses' => 'Attendance\WorkShiftController@index']);
        Route::get('/create', ['as' => 'workShift.create', 'uses' => 'Attendance\WorkShiftController@create']);
        Route::post('/store', ['as' => 'workShift.store', 'uses' => 'Attendance\WorkShiftController@store']);
        Route::get('/{workShift}/edit', ['as' => 'workShift.edit', 'uses' => 'Attendance\WorkShiftController@edit']);
        Route::put('/{workShift}', ['as' => 'workShift.update', 'uses' => 'Attendance\WorkShiftController@update']);
        Route::delete('/{workShift}/delete', ['as' => 'workShift.delete', 'uses' => 'Attendance\WorkShiftController@destroy']);
    });

    Route::group(['prefix' => 'shiftDetails'], function () {
        Route::get('/', ['as' => 'shiftDetails.index', 'uses' => 'Attendance\ShiftDetailsController@index']);
        Route::post('/', ['as' => 'shiftDetails.index', 'uses' => 'Attendance\ShiftDetailsController@index']);
        Route::post('/import', ['as' => 'shiftDetails.import', 'uses' => 'Attendance\ShiftDetailsController@import']);
        Route::post('/export', ['as' => 'shiftDetails.export', 'uses' => 'Attendance\ShiftDetailsController@export']);
        Route::get('/download', ['as' => 'shiftDetails.download', 'uses' => 'Attendance\ShiftDetailsController@download']);
        Route::get('/employeeShiftTemplate', ['as' => 'shiftDetails.employeeShiftTemplate', 'uses' => 'Attendance\ShiftDetailsController@employeeShiftTemplate']);
    });

    Route::group(['prefix' => 'deviceConfigure'], function () {
        Route::get('/', ['as' => 'deviceConfigure.index', 'uses' => 'Attendance\DeviceConfigurationController@index']);
        Route::get('/create', ['as' => 'deviceConfigure.create', 'uses' => 'Attendance\DeviceConfigurationController@create']);
        Route::post('/store', ['as' => 'deviceConfigure.store', 'uses' => 'Attendance\DeviceConfigurationController@store']);
        Route::get('/{deviceConfigure}/edit', ['as' => 'deviceConfigure.edit', 'uses' => 'Attendance\DeviceConfigurationController@edit']);
        Route::put('/{deviceConfigure}', ['as' => 'deviceConfigure.update', 'uses' => 'Attendance\DeviceConfigurationController@update']);
        Route::delete('/{deviceConfigure}/delete', ['as' => 'deviceConfigure.delete', 'uses' => 'Attendance\DeviceConfigurationController@destroy']);
        Route::get('/refresh', ['as' => 'deviceConfigure.refresh', 'uses' => 'Attendance\DeviceConfigurationController@refresh']);
        //Route::get('device/refresh', 'Attendance\DeviceConfigurationController@refresh')->name('device.refresh');
    });

    Route::get('attendanceRecord', ['as' => 'attendanceRecord.attendanceRecord', 'uses' => 'Attendance\AttendanceReportController@attendanceRecord']);
    Route::post('attendanceRecord', ['as' => 'attendanceRecord.attendanceRecord', 'uses' => 'Attendance\AttendanceReportController@attendanceRecord']);

    Route::get('dailyAttendance', ['as' => 'dailyAttendance.dailyAttendance', 'uses' => 'Attendance\AttendanceReportController@dailyAttendance']);
    Route::post('dailyAttendance', ['as' => 'dailyAttendance.dailyAttendance', 'uses' => 'Attendance\AttendanceReportController@dailyAttendance']);
    Route::get('monthlyAttendance', ['as' => 'monthlyAttendance.monthlyAttendance', 'uses' => 'Attendance\AttendanceReportController@monthlyAttendance']);
    Route::post('monthlyAttendance', ['as' => 'monthlyAttendance.monthlyAttendance', 'uses' => 'Attendance\AttendanceReportController@monthlyAttendance']);

    Route::get('myAttendanceReport', ['as' => 'myAttendanceReport.myAttendanceReport', 'uses' => 'Attendance\AttendanceReportController@myAttendanceReport']);
    Route::post('myAttendanceReport', ['as' => 'myAttendanceReport.myAttendanceReport', 'uses' => 'Attendance\AttendanceReportController@myAttendanceReport']);

    Route::get('attendanceSummaryReport', ['as' => 'attendanceSummaryReport.attendanceSummaryReport', 'uses' => 'Attendance\AttendanceReportController@attendanceSummaryReport']);
    Route::post('attendanceSummaryReport', ['as' => 'attendanceSummaryReport.attendanceSummaryReport', 'uses' => 'Attendance\AttendanceReportController@attendanceSummaryReport']);

    Route::get('attendanceMusterReport', ['as' => 'attendanceMusterReport.attendanceMusterReport', 'uses' => 'Attendance\AttendanceReportController@attendanceMusterReport']);
    Route::post('attendanceMusterReport', ['as' => 'attendanceMusterReport.attendanceMusterReport', 'uses' => 'Attendance\AttendanceReportController@attendanceMusterReport']);

    Route::get('manualAttendance', ['as' => 'manualAttendance.manualAttendance', 'uses' => 'Attendance\ManualAttendanceController@manualAttendance']);
    Route::post('manualAttendance/filter', ['as' => 'manualAttendance.filter', 'uses' => 'Attendance\ManualAttendanceController@manualAttendance']);
    // Route::get('manualAttendance/filter', ['as' => 'manualAttendance.filter', 'uses' => 'Attendance\ManualAttendanceController@filterData']);
    Route::post('manualAttendanceStore', ['as' => 'manualAttendance.store', 'uses' => 'Attendance\ManualAttendanceController@store']);

    Route::post('manualAttendance', ['as' => 'manualAttendance.individualReport', 'uses' => 'Attendance\ManualAttendanceController@individualReport']);

    Route::get('downloadDailyAttendance', 'Attendance\AttendanceReportController@downloadDailyAttendance');
    Route::get('downloadMonthlyAttendance', 'Attendance\AttendanceReportController@downloadMonthlyAttendance');
    Route::get('downloadMyAttendance', 'Attendance\AttendanceReportController@downloadMyAttendance');
    Route::get('downloadAttendanceSummaryReport/{date}', 'Attendance\AttendanceReportController@downloadAttendanceSummaryReport');

    Route::get('generateReport', ['as' => 'generateReport', 'uses' => 'Attendance\GenerateReportController@regenerateAttendanceReport']);

    Route::get('calculateAttendance', ['as' => 'calculateAttendance.calculateAttendance', 'uses' => 'Attendance\GenerateReportController@calculateAttendance']);

    // get attendance by ip

    Route::post('ip-attendance', ['as' => 'ip.attendance', 'uses' => 'Attendance\ManualAttendanceController@ipAttendance']);

    // setup ip  attendance

    Route::get('setup-employee-attendance', ['as' => 'attendance.dashboard', 'uses' => 'Attendance\ManualAttendanceController@setupDashboardAttendance']);

    Route::post('setup-employee-attendance-post', ['as' => 'attendance.dashboard.post', 'uses' => 'Attendance\ManualAttendanceController@postDashboardAttendance']);
});
