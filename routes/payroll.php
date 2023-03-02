<?php

use App\Http\Controllers\Payroll\DailyCtcController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['preventbackbutton', 'auth']], function () {

    Route::get('calculate_ctc' , [DailyCtcController::class, 'calculate_ctc']);

    Route::group(['prefix' => 'taxSetup'], function () {
        Route::get('/', ['as' => 'taxSetup.index', 'uses' => 'Payroll\TaxSetupController@index']);
        Route::post('updateTaxRule', 'Payroll\TaxSetupController@updateTaxRule');
    });

    Route::group(['prefix' => 'overtimeApproval'], function () {
        Route::get('/', ['as' => 'overtimeApproval.overtimeApproval', 'uses' => 'Payroll\OvertimeApprovalController@overtimeApproval']);
        Route::post('/', ['as' => 'overtimeApproval.overtimeApproval', 'uses' => 'Payroll\OvertimeApprovalController@overtimeApproval']);
        Route::post('/changeOvertimeStatus', ['as' => 'overtimeApproval.changeOvertimeStatus', 'uses' => 'Payroll\OvertimeApprovalController@changeOvertimeStatus']);
    });

    Route::group(['prefix' => 'monthlyWorkingDay'], function () {
        Route::get('/', ['as' => 'monthlyWorkingDay.index', 'uses' => 'Payroll\MonthlyWorkingDayController@index']);
        Route::get('/create', ['as' => 'monthlyWorkingDay.create', 'uses' => 'Payroll\MonthlyWorkingDayController@create']);
        Route::post('/store', ['as' => 'monthlyWorkingDay.store', 'uses' => 'Payroll\MonthlyWorkingDayController@store']);
        Route::get('/{monthlyWorkingDay}/edit', ['as' => 'monthlyWorkingDay.edit', 'uses' => 'Payroll\MonthlyWorkingDayController@edit']);
        Route::put('/{monthlyWorkingDay}', ['as' => 'monthlyWorkingDay.update', 'uses' => 'Payroll\MonthlyWorkingDayController@update']);
        Route::delete('/{monthlyWorkingDay}/delete', ['as' => 'monthlyWorkingDay.delete', 'uses' => 'Payroll\MonthlyWorkingDayController@destroy']);
    });

    Route::group(['prefix' => 'salaryInfo'], function () {
        Route::get('/', ['as' => 'salaryInfo.index', 'uses' => 'Payroll\SalaryController@index']);
        Route::get('/details', ['as' => 'salaryInfo.details', 'uses' => 'Payroll\SalaryController@details']);
        Route::post('/store', ['as' => 'salaryInfo.store', 'uses' => 'Payroll\SalaryController@store']);
    });
    Route::group(['prefix' => 'salaryReport'], function () {
        Route::get('/', ['as' => 'salaryReport.report', 'uses' => 'Payroll\SalaryController@report']);
        Route::get('/reportdetails', ['as' => 'salaryReport.reportdetails', 'uses' => 'Payroll\SalaryController@reportdetails']);
        Route::get('/download', ['as' => 'salaryReport.reportdownload', 'uses' => 'Payroll\SalaryController@download']);
    });

    Route::group(['prefix' => 'settlementReport'], function () {
        Route::get('/', ['as' => 'settlementReport.report', 'uses' => 'Payroll\SettlementController@report']);
        Route::get('/report-details', ['as' => 'settlementReport.reportdetails', 'uses' => 'Payroll\SettlementController@reportdetails']);
        Route::get('/download', ['as' => 'settlementReport.download', 'uses' => 'Payroll\SettlementController@download']);
    });

    Route::group(['prefix' => 'wageSheet'], function () {
        Route::get('/', ['as' => 'wageSheet.generation', 'uses' => 'Payroll\SalaryController@generation']);
        Route::get('/sheet', ['as' => 'wageSheet.sheet', 'uses' => 'Payroll\SalaryController@sheet']);
    });

    Route::group(['prefix' => 'settlementInfo'], function () {
        Route::get('/', ['as' => 'settlementInfo.index', 'uses' => 'Payroll\SettlementController@index']);
        Route::get('/details', ['as' => 'settlementInfo.details', 'uses' => 'Payroll\SettlementController@details']);
        Route::get('/form', ['as' => 'settlementInfo.form', 'uses' => 'Payroll\SettlementController@form']);
        Route::post('/store', ['as' => 'settlementInfo.store', 'uses' => 'Payroll\SettlementController@store']);
    });

    Route::group(['prefix' => 'settlementPendingInfo'], function () {
        Route::get('/', ['as' => 'settlementPendingInfo.pending', 'uses' => 'Payroll\SettlementController@pending']);
        Route::get('pending-details', ['as' => 'settlementPendingInfo.pendingdetails', 'uses' => 'Payroll\SettlementController@pendingdetails']);
    });

    Route::group(['prefix' => 'Payslip'], function () {
        Route::get('/', ['as' => 'payslip.generation', 'uses' => 'Payroll\PayslipController@index']);
        Route::get('/payslipCollection', ['as' => 'payslip.payslipCollection', 'uses' => 'Payroll\PayslipController@payslipCollection']);
    });

    Route::group(['prefix' => 'regeneratePayroll'], function () {
        Route::get('/', ['as' => 'regeneratePayroll.regeneratePayroll', 'uses' => 'Payroll\RegeneratePayrollController@index']);
        Route::post('/', ['as' => 'regeneratePayroll.regeneratePayroll', 'uses' => 'Payroll\RegeneratePayrollController@index']);
        Route::get('/verifyWageSheet', ['as' => 'regeneratePayroll.verifyWageSheet', 'uses' => 'Payroll\RegeneratePayrollController@verifyWageSheet']);
        Route::post('/storeWageReport', ['as' => 'regeneratePayroll.storeWageReport', 'uses' => 'Payroll\RegeneratePayrollController@storeWageReport']);
        Route::post('/WageReportExcel', ['as' => 'regeneratePayroll.WageReportExcel', 'uses' => 'Payroll\RegeneratePayrollController@WageReportExcel']);
    });

    Route::group(['prefix' => 'salaryDeductionRuleForLateAttendance'], function () {
        Route::get('/', ['as' => 'salaryDeductionRule.index', 'uses' => 'Payroll\SalaryDeductionRuleController@index']);
        Route::post('updateSalaryDeductionRule', 'Payroll\SalaryDeductionRuleController@updateSalaryDeductionRule');
    });

    Route::group(['prefix' => 'allowance'], function () {
        Route::get('/', ['as' => 'allowance.index', 'uses' => 'Payroll\AllowanceController@index']);
        Route::get('/create', ['as' => 'allowance.create', 'uses' => 'Payroll\AllowanceController@create']);
        Route::post('/store', ['as' => 'allowance.store', 'uses' => 'Payroll\AllowanceController@store']);
        Route::get('/{allowance}/edit', ['as' => 'allowance.edit', 'uses' => 'Payroll\AllowanceController@edit']);
        Route::put('/{allowance}', ['as' => 'allowance.update', 'uses' => 'Payroll\AllowanceController@update']);
        Route::delete('/{allowance}/delete', ['as' => 'allowance.delete', 'uses' => 'Payroll\AllowanceController@destroy']);
    });

    Route::group(['prefix' => 'payrollSettings'], function () {
        Route::get('/', ['as' => 'payrollSettings.index', 'uses' => 'Payroll\PayrollSettingController@index']);
        Route::get('/create', ['as' => 'payrollSettings.create', 'uses' => 'Payroll\PayrollSettingController@create']);
        Route::post('/store', ['as' => 'payrollSettings.store', 'uses' => 'Payroll\PayrollSettingController@store']);
        Route::get('/{payrollSettings}/edit', ['as' => 'payrollSettings.edit', 'uses' => 'Payroll\PayrollSettingController@edit']);
        Route::put('/{payrollSettings}', ['as' => 'payrollSettings.update', 'uses' => 'Payroll\PayrollSettingController@update']);
        Route::delete('/{payrollSettings}/delete', ['as' => 'payrollSettings.delete', 'uses' => 'Payroll\PayrollSettingController@destroy']);
    });

    Route::group(['prefix' => 'deduction'], function () {
        Route::get('/', ['as' => 'deduction.index', 'uses' => 'Payroll\DeductionController@index']);
        Route::get('/create', ['as' => 'deduction.create', 'uses' => 'Payroll\DeductionController@create']);
        Route::post('/store', ['as' => 'deduction.store', 'uses' => 'Payroll\DeductionController@store']);
        Route::get('/{deduction}/edit', ['as' => 'deduction.edit', 'uses' => 'Payroll\DeductionController@edit']);
        Route::put('/{deduction}', ['as' => 'deduction.update', 'uses' => 'Payroll\DeductionController@update']);
        Route::delete('/{deduction}/delete', ['as' => 'deduction.delete', 'uses' => 'Payroll\DeductionController@destroy']);
    });

    Route::group(['prefix' => 'advanceDeduction'], function () {
        Route::get('/', ['as' => 'advanceDeduction.index', 'uses' => 'Payroll\AdvanceDeductionController@index']);
        Route::get('/create', ['as' => 'advanceDeduction.create', 'uses' => 'Payroll\AdvanceDeductionController@create']);
        Route::post('/store', ['as' => 'advanceDeduction.store', 'uses' => 'Payroll\AdvanceDeductionController@store']);
        Route::get('/{advanceDeduction}/edit', ['as' => 'advanceDeduction.edit', 'uses' => 'Payroll\AdvanceDeductionController@edit']);
        Route::put('/{advanceDeduction}', ['as' => 'advanceDeduction.update', 'uses' => 'Payroll\AdvanceDeductionController@update']);
        Route::delete('/{advanceDeduction}/delete', ['as' => 'advanceDeduction.delete', 'uses' => 'Payroll\AdvanceDeductionController@destroy']);
        // Route::get('adv/{employee_id}', ['uses' => 'Payroll\AdvanceDeductionController@calculateEmployeeAdvanceDeduction']);
    });

    Route::post('/monthlyDeductionImport', ['as' => 'monthlyDeduction.monthlyDeductionImport', 'uses' => 'Payroll\FoodAndTelephoneDeductionController@monthlyDeductionImport']);
    Route::get('/monthlyDeductionTemplate', ['as' => 'monthlyDeduction.monthlyDeductionTemplate', 'uses' => 'Payroll\FoodAndTelephoneDeductionController@monthlyDeductionTemplate']);

    Route::group(['prefix' => 'foodDeductionConfigure'], function () {
        Route::get('/', ['as' => 'foodDeductionConfigure.index', 'uses' => 'Payroll\FoodAllowanceConfigureController@index']);
        Route::post('updateFoodDeductionConfigure', 'Payroll\FoodAllowanceConfigureController@updateFoodDeductionConfigure');
    });

    Route::group(['prefix' => 'telephoneDeductionConfigure'], function () {
        Route::get('/', ['as' => 'telephoneDeductionConfigure.index', 'uses' => 'Payroll\TelephoneAllowanceConfigureController@index']);
        Route::post('updateTelephoneDeductionConfigure', 'Payroll\TelephoneAllowanceConfigureController@updateTelephoneDeductionConfigure');
    });

    Route::get('monthlyDeduction', ['as' => 'monthlyDeduction.monthlyDeduction', 'uses' => 'Payroll\FoodAndTelephoneDeductionController@monthlyManualDeductions']);
    Route::get('monthlyDeduction/filter', ['as' => 'monthlyDeduction.filter', 'uses' => 'Payroll\FoodAndTelephoneDeductionController@filterEmployeeData']);
    Route::post('monthlyDeduction/store', ['as' => 'monthlyDeduction.store', 'uses' => 'Payroll\FoodAndTelephoneDeductionController@store']);
    //-------------------------------------

    Route::group(['prefix' => 'payGrade'], function () {
        Route::get('/', ['as' => 'payGrade.index', 'uses' => 'Payroll\PayGradeController@index']);
        Route::get('/create', ['as' => 'payGrade.create', 'uses' => 'Payroll\PayGradeController@create']);
        Route::post('/store', ['as' => 'payGrade.store', 'uses' => 'Payroll\PayGradeController@store']);
        Route::get('/{payGrade}/edit', ['as' => 'payGrade.edit', 'uses' => 'Payroll\PayGradeController@edit']);
        Route::put('/{payGrade}', ['as' => 'payGrade.update', 'uses' => 'Payroll\PayGradeController@update']);
        Route::delete('/{payGrade}/delete', ['as' => 'payGrade.delete', 'uses' => 'Payroll\PayGradeController@destroy']);
    });

    Route::group(['prefix' => 'hourlyWages'], function () {
        Route::get('/', ['as' => 'hourlyWages.index', 'uses' => 'Payroll\HourlyWagesPayrollController@index']);
        Route::get('/create', ['as' => 'hourlyWages.create', 'uses' => 'Payroll\HourlyWagesPayrollController@create']);
        Route::post('/store', ['as' => 'hourlyWages.store', 'uses' => 'Payroll\HourlyWagesPayrollController@store']);
        Route::get('/{hourlyWages}/edit', ['as' => 'hourlyWages.edit', 'uses' => 'Payroll\HourlyWagesPayrollController@edit']);
        Route::put('/{hourlyWages}', ['as' => 'hourlyWages.update', 'uses' => 'Payroll\HourlyWagesPayrollController@update']);
        Route::delete('/{hourlyWages}/delete', ['as' => 'hourlyWages.delete', 'uses' => 'Payroll\HourlyWagesPayrollController@destroy']);
    });

    Route::group(['prefix' => 'generateSalarySheet'], function () {
        Route::get('/', ['as' => 'generateSalarySheet.index', 'uses' => 'Payroll\GenerateSalarySheet@index']);
        Route::get('/create', ['as' => 'generateSalarySheet.create', 'uses' => 'Payroll\GenerateSalarySheet@create']);
        Route::get('/calculateEmployeeSalary', ['as' => 'generateSalarySheet.calculateEmployeeSalary', 'uses' => 'Payroll\GenerateSalarySheet@calculateEmployeeSalary']);
        Route::post('/store', ['as' => 'saveEmployeeSalaryDetails.store', 'uses' => 'Payroll\GenerateSalarySheet@store']);
        Route::post('/calculateEmployeeSalaryToAll', ['as' => 'generateSalarySheet.calculateEmployeeSalaryToAll', 'uses' => 'Payroll\GenerateSalarySheet@generateSalarySheetToAllEmployees']);
        Route::post('/makePaymentToAllEmployees', ['as' => 'generateSalarySheet.makePaymentToAllEmployees', 'uses' => 'Payroll\GenerateSalarySheet@makePaymentToAllEmployees']);
        Route::post('/makePayment', 'Payroll\GenerateSalarySheet@makePayment');
        Route::get('/generatePayslip/{id}', 'Payroll\GenerateSalarySheet@generatePayslip');
        Route::get('/monthSalary', ['as' => 'generateSalarySheet.monthSalary', 'uses' => 'Payroll\GenerateSalarySheet@monthSalary']);
        Route::post('/razorpayPayment', ['as' => 'generateSalarySheet.razorpayPayment', 'uses' => 'Payroll\GenerateSalarySheet@razorpayPayment']);
        Route::post('/makerazorPayment', ['as' => 'generateSalarySheet.makerazorPayment', 'uses' => 'Payroll\GenerateSalarySheet@makerazorPayment']);
        Route::get('/razorpay', ['as' => 'generateSalarySheet.razorpay', 'uses' => 'Payroll\RazorpayController@razorpay']);
        Route::get('/payment', ['as' => 'generateSalarySheet.payment', 'uses' => 'Payroll\RazorpayController@payment']);
    });

    Route::get('downloadPayslip', ['as' => 'downloadPayslip.payslip', 'uses' => 'Payroll\GenerateSalarySheet@payslip']);
    Route::get('downloadPayslip/generatePayslip/{id}', 'Payroll\GenerateSalarySheet@generatePayslip');
    Route::get('downloadPayslipForAllEmployee', 'Payroll\GenerateSalarySheet@downloadPayslipForAllEmployee');

    Route::get('paymentHistory', ['as' => 'paymentHistory.paymentHistory', 'uses' => 'Payroll\GenerateSalarySheet@paymentHistory']);
    Route::post('paymentHistory', ['as' => 'paymentHistory.paymentHistory', 'uses' => 'Payroll\GenerateSalarySheet@paymentHistory']);
    Route::get('paymentHistory/generatePayslip/{id}', 'Payroll\GenerateSalarySheet@generatePayslip');

    Route::get('myPayroll', ['as' => 'myPayroll.myPayroll', 'uses' => 'Payroll\GenerateSalarySheet@myPayroll']);
    Route::get('myPayroll/generatePayslip/{id}', 'Payroll\GenerateSalarySheet@generatePayslip');

    Route::get('downloadPayslip/{id}', 'Payroll\GenerateSalarySheet@downloadPayslip');
    Route::get('downloadMyPayroll', 'Payroll\GenerateSalarySheet@downloadMyPayroll');

    Route::get('workHourApproval', ['as' => 'workHourApproval.create', 'uses' => 'Payroll\WorkHourApprovalController@create']);
    Route::get('workHourApproval/filter', ['as' => 'workHourApproval.filter', 'uses' => 'Payroll\WorkHourApprovalController@filter']);
    Route::post('workHourApproval', ['as' => 'workHourApproval.store', 'uses' => 'Payroll\WorkHourApprovalController@store']);

    Route::group(['prefix' => 'bonusSetting'], function () {
        Route::get('/', ['as' => 'bonusSetting.index', 'uses' => 'Payroll\BonusSettingController@index']);
        Route::get('/create', ['as' => 'bonusSetting.create', 'uses' => 'Payroll\BonusSettingController@create']);
        Route::post('/store', ['as' => 'bonusSetting.store', 'uses' => 'Payroll\BonusSettingController@store']);
        Route::get('/{bonusSetting}/edit', ['as' => 'bonusSetting.edit', 'uses' => 'Payroll\BonusSettingController@edit']);
        Route::put('/{bonusSetting}', ['as' => 'bonusSetting.update', 'uses' => 'Payroll\BonusSettingController@update']);
        Route::delete('/{bonusSetting}/delete', ['as' => 'bonusSetting.delete', 'uses' => 'Payroll\BonusSettingController@destroy']);
    });

    Route::group(['prefix' => 'generateBonus'], function () {
        Route::get('/', ['as' => 'generateBonus.index', 'uses' => 'Payroll\GenerateBonusController@index']);
        Route::get('/create', ['as' => 'generateBonus.create', 'uses' => 'Payroll\GenerateBonusController@create']);
        Route::post('/store', ['as' => 'saveEmployeeBonus.store', 'uses' => 'Payroll\GenerateBonusController@store']);
        Route::get('/filter', ['as' => 'generateBonus.filter', 'uses' => 'Payroll\GenerateBonusController@filter']);
    });

    Route::group(['prefix' => 'bonusday'], function () {
        Route::get('/', ['as' => 'bonusday.index', 'uses' => 'Payroll\BonusDayController@index']);
    });

    Route::group(['prefix' => 'uploadSalaryDetails'], function () {
        Route::get('/', ['as' => 'uploadSalaryDetails.uploadSalaryDetails', 'uses' => 'Payroll\UploadSalaryDetailController@index']);
        Route::post('/import', ['as' => 'uploadSalaryDetails.import', 'uses' => 'Payroll\UploadSalaryDetailController@import']);
        // Route::get('/export', ['as' => 'uploadSalaryDetails.export', 'uses' => 'Payroll\UploadSalaryDetailController@export']);
        Route::get('/export/{type}', ['as' => 'uploadSalaryDetails.export', 'uses' => 'Payroll\UploadSalaryDetailController@export']);
        Route::get('downloadSalaryDetails/{month}', 'Payroll\UploadSalaryDetailController@SalaryDetails');
        Route::get('/downloadFile', ['as' => 'uploadSalaryDetails.downloadFile', 'uses' => 'Payroll\UploadSalaryDetailController@downloadFile']);
    });
});
