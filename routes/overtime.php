<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['preventbackbutton', 'auth']], function () {

    // Route::get('overtimeRuleConfigure', ['as' => 'overtimeRuleConfigure.overtimeRuleConfigure', 'uses' => 'OverTime\OverTimeController@overtimeRuleConfigure']);
    // Route::post('updateOvertimeRuleConfigure', ['as' => 'overtimeRuleConfigure.updateOvertimeRuleConfigure', 'uses' => 'OverTime\OverTimeController@updateOvertimeRuleConfigure']);

    Route::get('dailyOverTime', ['as' => 'dailyOverTime.dailyOverTime', 'uses' => 'OverTime\OverTimeController@dailyOverTime']);
    Route::post('dailyOverTime', ['as' => 'dailyOverTime.dailyOverTime', 'uses' => 'OverTime\OverTimeController@dailyOverTime']);
    Route::get('monthlyOverTime', ['as' => 'monthlyOverTime.monthlyOverTime', 'uses' => 'OverTime\OverTimeController@monthlyOverTime']);
    Route::post('monthlyOverTime', ['as' => 'monthlyOverTime.monthlyOverTime', 'uses' => 'OverTime\OverTimeController@monthlyOverTime']);

    Route::get('myOverTimeReport', ['as' => 'myOverTimeReport.myOverTimeReport', 'uses' => 'OverTime\OverTimeController@myOverTimeReport']);
    Route::post('myOverTimeReport', ['as' => 'myOverTimeReport.myOverTimeReport', 'uses' => 'OverTime\OverTimeController@myOverTimeReport']);

    Route::get('overtimeSummaryReport', ['as' => 'overtimeSummaryReport.overtimeSummaryReport', 'uses' => 'OverTime\OverTimeController@overtimeSummaryReport']);
    Route::post('overtimeSummaryReport', ['as' => 'overtimeSummaryReport.overtimeSummaryReport', 'uses' => 'OverTime\OverTimeController@overtimeSummaryReport']);

    Route::get('downloadDailyOverTime/{id}', 'OverTime\OverTimeController@downloadDailyOverTime');
    Route::get('downloadMonthlyOverTime', 'OverTime\OverTimeController@downloadMonthlyOverTime');
    Route::get('downloadMyOverTime', 'OverTime\OverTimeController@downloadMyOverTime');
    Route::get('downloadOverTimeSummaryReport/{date}', 'OverTime\OverTimeController@downloadovertimeSummaryReport');
});

Route::group(['prefix' => 'overtimeRuleConfigure'], function () {
    Route::get('/', ['as' => 'overtimeRuleConfigure.overtimeRuleConfigure', 'uses' => 'OverTime\OverTimeController@overtimeRuleConfigure']);
    Route::post('updateOvertimeRuleConfigure', 'OverTime\OverTimeController@updateOvertimeRuleConfigure');
});

