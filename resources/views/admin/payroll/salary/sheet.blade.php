@extends('admin.master')
@section('content')
@section('title')
    @lang('salary.salary_sheet') {{ $employeeAllInfo->first_name . ' ' . $employeeAllInfo->last_name }} (
    {{ $employeeAllInfo->finger_id }} )
@endsection

<style type="text/css">
    td {
        padding: 4px !important
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">

                        {{ Form::open(['route' => 'salaryInfo.store', 'enctype' => 'multipart/form-data', 'id' => 'salaryForm']) }}

                        @if ($errors->any())
                            <div class="alert alert-danger alert-block alert-dismissable">
                                <ul>
                                    <button type="button" class="close" data-dismiss="alert">x</button>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i
                                    class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i
                                    class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-12">

                                <h3>Employee Info</h3>
                                <hr style="margin-top:-12px;margin-bottom:10px">

                                <table class="table table-bordered table-hover table-striped">

                                    <tbody>

                                        <input type="number" hidden name="bank_name"
                                            value="{{ $employeeAllInfo->bank_name }}">
                                        <input type="number" hidden name="bank_branch"
                                            value="{{ $employeeAllInfo->bank_branch }}">
                                        <input type="number" hidden name="bank_account_no"
                                            value="{{ $employeeAllInfo->bank_account_no }}">
                                        <input type="number" hidden name="bank_of_the_city"
                                            value="{{ $employeeAllInfo->bank_of_the_city }}">

                                        <input type="text" hidden name="fdate"
                                            value="{{ $employeeSalaryDetails['fdate'] }}">
                                        <input type="text" hidden name="tdate"
                                            value="{{ $employeeSalaryDetails['tdate'] }}">
                                        <input type="text" hidden name="ph"
                                            value="{{ $employeeSalaryDetails['publicHolidays'] }}">
                                        <input type="text" hidden name="date" value="{{ date('Y-m-d') }}">
                                        <input type="text" hidden name="employee"
                                            value="{{ $employeeAllInfo->employee_id }}">
                                        <input type="text" hidden name="department"
                                            value="{{ $employeeAllInfo->department->department_id }}">
                                        <input type="text" hidden name="branch"
                                            value="{{ $employeeAllInfo->branch->branch_id }}">
                                        <input type="text" hidden name="unit"
                                            value="{{ $employeeAllInfo->subdepartment->sub_department_id }}">
                                        <input type="text" hidden name="costcenter"
                                            value="{{ $employeeAllInfo->costcenter->cost_center_id }}">
                                        <input type="text" hidden name="finger_print_id"
                                            value="{{ $employeeAllInfo->finger_id }}">
                                        <input type="number" hidden name="month"
                                            value="{{ date('m', strtotime($monthOfSalary)) }}">
                                        <input type="number" hidden name="year"
                                            value="{{ date('Y', strtotime($monthOfSalary)) }}">
                                        <tr>
                                            <td class="col-md-3">Name</td>
                                            <td class="col-md-3">
                                                {{ $employeeAllInfo->first_name . ' ' . $employeeAllInfo->last_name }}
                                            </td>
                                            <td class="col-md-3">Contactor</td>
                                            <td class="col-md-3">{{ $employeeAllInfo->branch->branch_name }}</td>
                                        </tr>
                                        <tr>
                                            <td>Sub Unit</td>
                                            <td>{{ $employeeAllInfo->subdepartment->sub_department_name }}</td>
                                            <td>Cost Center</td>
                                            <td>{{ $employeeAllInfo->costcenter->cost_center_number }}</td>
                                        </tr>
                                        <tr>
                                            <td>Department</td>
                                            <td>{{ $employeeAllInfo->department->department_name }}</td>
                                            <td>Designation</td>
                                            <td>{{ $employeeAllInfo->designation->designation_name }}</td>
                                        </tr>
                                        <tr>
                                            <td>Month</td>
                                            <td>{{ date('F', strtotime($monthOfSalary)) }}
                                            </td>
                                            <td>Year</td>
                                            <td>{{ date('Y', strtotime($monthOfSalary)) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Date of Joining</td>
                                            <td>{{ date('m/d/Y', strtotime($employeeAllInfo->date_of_joining)) }}</td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>


                            <div class="col-md-12">

                                <h3>Salary Info</h3>
                                <hr style="margin-top:-12px;margin-bottom:10px">

                                <table class="table table-bordered table-hover table-striped">

                                    <input type="number" hidden name="total_days"
                                        value="{{ $employeeSalaryDetails['totalWorkingDays'] }}">
                                    <input type="number" hidden name="company_holiday"
                                        value="{{ $employeeSalaryDetails['companyHolidays'] }}">

                                    <input type="number" hidden name="per_day_basic_da"
                                        value="{{ $employeeSalaryDetails['perDayBasicDaEarned'] }}">
                                    <input type="number" hidden name="per_day_basic"
                                        value="{{ $employeeSalaryDetails['perDayBasicEarned'] }}">
                                    <input type="number" hidden name="per_day_da"
                                        value="{{ $employeeSalaryDetails['perDayDaEarned'] }}">
                                    <input type="number" hidden name="per_day_hra"
                                        value="{{ $employeeSalaryDetails['perDayHraEarned'] }}">
                                    <input type="number" hidden name="per_day_wages"
                                        value="{{ $employeeSalaryDetails['oneDaysSalary'] }}">

                                    <tbody>

                                        <tr>
                                            <input type="number" hidden name="no_day_wages"
                                                value="{{ $employeeSalaryDetails['totalPresent'] }}">
                                            <td class="col-md-3">Worked Hours</td>
                                            <td class="col-md-3">{{ $employeeSalaryDetails['totalWorkingHour'] }}</td>
                                            <td class="col-md-3">Worked Days</td>
                                            <td class="col-md-3">
                                                {{ $employeeSalaryDetails['totalPresent'] }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>P.H</td>
                                            <td>{{ $employeeSalaryDetails['publicHolidays'] }}</td>
                                            <td>C.D.H</td>
                                            <td>{{ $employeeSalaryDetails['companyHolidays'] }}</td>
                                        </tr>

                                        <tr>
                                            <td>No of Working Days</td>
                                            <td>{{ $employeeSalaryDetails['totalWorkingDays'] }}</td>
                                            <td>Per Day Wages</td>
                                            <td>{{ $employeeSalaryDetails['oneDaysSalary'] }}</td>

                                        </tr>

                                        <tr>
                                            <td>Basic Per Day</td>
                                            <td>{{ $employeeSalaryDetails['perDayBasicEarned'] }}</td>
                                            <td>DA Per Day</td>
                                            <td>{{ $employeeSalaryDetails['perDayDaEarned'] }}</td>
                                        </tr>

                                        <tr>
                                            <td>HRA Per Day</td>
                                            <td>{{ $employeeSalaryDetails['perDayHraEarned'] }}</td>
                                            <td>OT Per Hour Amount</td>
                                            <td>{{ $employeeSalaryDetails['overtime_rate'] }}</td>
                                        </tr>

                                        <tr>
                                            <td>EL Balance</td>
                                            <td>{{ $employeeSalaryDetails['earnedLeaveBalance'] }}</td>

                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h3>Wages Earning</h3>
                                <hr style="margin-top:-12px;margin-bottom:10px">
                                <table class="table table-bordered table-hover table-striped">
                                    <tbody>



                                        <input type="number" hidden name="basic_da_amount"
                                            value="{{ $employeeSalaryDetails['basicDaEarned'] }}">
                                        <input type="number" hidden name="basic_amount"
                                            value="{{ $employeeSalaryDetails['basicEarned'] }}">
                                        <input type="number" hidden name="da_amount"
                                            value="{{ $employeeSalaryDetails['daEarned'] }}">
                                        <input type="number" hidden name="hra_amount"
                                            value="{{ $employeeSalaryDetails['hraEarned'] }}">
                                        <input type="number" hidden name="wages_amount"
                                            value="{{ $employeeSalaryDetails['wagesEarned'] }}">
                                        <input type="number" hidden name="attendance_bonus"
                                            value="{{ $employeeSalaryDetails['attendanceBonus'] }}">
                                        <input type="text" hidden name="ot_hours"
                                            value="{{ $employeeSalaryDetails['totalOverTimeHour'] }}">
                                        <input type="number" hidden name="ot_per_hours"
                                            value="{{ $employeeSalaryDetails['overtime_rate'] }}">
                                        <input type="number" hidden name="ot_amount"
                                            value="{{ $employeeSalaryDetails['totalOvertimeAmount'] }}">


                                        <tr>
                                            <td>Basic Allowance</td>
                                            <td>{{ $employeeSalaryDetails['basicEarned'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Daily Allowance</td>
                                            <td>{{ $employeeSalaryDetails['daEarned'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>House Rent Allowance</td>
                                            <td>{{ $employeeSalaryDetails['hraEarned'] }}</td>
                                        </tr>
                                        <tr hidden>
                                            <td>Attendance Bonus</td>
                                            <td>{{ $employeeSalaryDetails['attendanceBonus'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>OT Hour</td>
                                            <td>{{ $employeeSalaryDetails['totalOverTimeHour'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>OT Amount</td>
                                            <td>{{ $employeeSalaryDetails['totalOvertimeAmount'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Attendance Bonus</td>
                                            <td>{{ $employeeSalaryDetails['attendanceBonus'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Other Allowances</td>
                                            <td>{{ $employeeSalaryDetails['otherAllowance'] }}</td>
                                        </tr>
                                    </tbody>

                                </table>
                            </div>
                            <div class="col-md-6">
                                <h3>Deduction</h3>
                                <hr style="margin-top:-12px;margin-bottom:10px">
                                <table class="table table-bordered table-hover table-striped">
                                    <tbody>

                                        <input type="number" hidden name="employee_pf"
                                            value="{{ $employeeSalaryDetails['employeeEPF'] }}">
                                        <input type="number" hidden name="employee_pf_percentage"
                                            value="{{ $payrollSetting->employee_pf }}">

                                        <input type="number" hidden name="employee_esic"
                                            value="{{ $employeeSalaryDetails['employeeESIC'] }}">
                                        <input type="number" hidden name="employee_esic_percentage"
                                            value="{{ $payrollSetting->employee_esic }}">

                                        <input type="number" hidden name="canteen"
                                            value="{{ $employeeSalaryDetails['canteenDeduction'] }}">

                                        <input type="number" hidden name="net_salary"
                                            value="{{ $employeeSalaryDetails['netSalary'] }}">

                                        <input type="number" hidden name="employer_pf"
                                            value="{{ $employeeSalaryDetails['employerEPF'] }}">
                                        <input type="number" hidden name="employer_pf_percentage"
                                            value="{{ $payrollSetting->employer_pf }}">

                                        <input type="number" hidden name="employer_esic"
                                            value="{{ $employeeSalaryDetails['employerESIC'] }}">
                                        <input type="number" hidden name="employer_esic_percentage"
                                            value="{{ $payrollSetting->employer_esic }}">

                                        <input type="text" hidden name="manhours"
                                            value="{{ $employeeSalaryDetails['totalWorkingHour'] }}">
                                        <input type="text" hidden name="manhour_days"
                                            value="{{ $employeeSalaryDetails['totalPresent'] }}">

                                        <input type="text" hidden name="lwf"
                                            value="{{ $employeeSalaryDetails['employeeLWF'] }}">

                                        <tr>
                                            <td>Employee ESI ({{ $payrollSetting->employee_esic }}%)</td>
                                            <td>{{ $employeeSalaryDetails['employeeESIC'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Employee PF ({{ $payrollSetting->employee_pf }}%)</td>
                                            <td>{{ $employeeSalaryDetails['employeeEPF'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Employeer ESI ({{ $payrollSetting->employer_esic }}%)</td>
                                            <td>{{ $employeeSalaryDetails['employerESIC'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Employeer PF ({{ $payrollSetting->employer_pf }}%)</td>
                                            <td>{{ $employeeSalaryDetails['employerEPF'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Canteen Deduction</td>
                                            <td>{{ $employeeSalaryDetails['canteenDeduction'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>LWF {{ '(Rs.' . $payrollSetting->lwf . ')' }}</td>
                                            <td>{{ $employeeSalaryDetails['employeeLWF'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Earned Leave Amount</td>
                                            <td>{{ $employeeSalaryDetails['earnLeaveAmount'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Bonus Amount</td>
                                            <td>{{ $employeeSalaryDetails['bonusEarning'] }}</td>
                                        </tr>
                                        @if ($employeeAllInfo->service_charge == App\Lib\Enumerations\serviceCharge::$ENABLED)
                                            <tr>
                                                <td>Service Charge</td>
                                                <td>{{ $employeeSalaryDetails['serviceCharge'] }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td>Other Deduction</td>
                                            <td>{{ $employeeSalaryDetails['otherDeduction'] }}</td>
                                        </tr>
                                    </tbody>

                                </table>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <h3><b>Employee</b></h3>
                                <hr style="margin-top:-12px;margin-bottom:10px">
                                <table class="table table-bordered table-hover table-striped">

                                    <input type="number" hidden name="net_salary"
                                        value="{{ $employeeSalaryDetails['netSalary'] }}">
                                    <input type="number" hidden name="gross_salary"
                                        value="{{ $employeeSalaryDetails['grossSalary'] }}">
                                    <input type="number" hidden name="employee_total_deduction"
                                        value="{{ $employeeSalaryDetails['totalDeduction'] }}">

                                    <tr>
                                        <td>Gross salary</td>
                                        <td>{{ $employeeSalaryDetails['grossSalary'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Deduction</td>
                                        <td>{{ $employeeSalaryDetails['totalDeduction'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Net Salary to be Paid</b></td>
                                        <td>{{ $employeeSalaryDetails['netSalary'] }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h3><b>Employer</b></h3>
                                <hr style="margin-top:-12px;margin-bottom:10px">
                                <table class="table table-bordered table-hover table-striped">
                                    @php
                                        $employerDeduction = $employeeSalaryDetails['employerESIC'] + $employeeSalaryDetails['employerEPF'] + $employeeSalaryDetails['employeeLWF'];
                                        $totalEarnings = $employeeSalaryDetails['earnLeaveAmount'] + $employeeSalaryDetails['bonusEarning'] + $employeeSalaryDetails['serviceCharge'];
                                        $salaryOH = $employeeSalaryDetails['grossSalary'] + $totalEarnings + $employerDeduction;
                                    @endphp

                                    {{-- <input type="number" hidden name="salary" value="{{ $salaryOH }}"> --}}
                                    <input type="number" hidden name="employer_total_deduction"
                                        value="{{ $employerDeduction }}">

                                    <tr>
                                        <td>Gross salary</td>
                                        <td>{{ $employeeSalaryDetails['grossSalary'] }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Deduction</td>
                                        <td>{{ $employerDeduction }}</td>
                                    </tr>
                                    <tr>
                                        <td>Earned Leave, Bonus & Service Charge</td>
                                        <td>{{ $totalEarnings }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Sub. Total</b></td>
                                        <td>{{ $salaryOH }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">
                                <h3>Earning Leaves & Bonus ( Pay Later / Settlement )</h3>
                                <hr style="margin-top:-12px;margin-bottom:10px">
                                <table class="table table-bordered table-hover table-striped">
                                    <tbody>

                                        <input type="number" hidden name="bonus_amount"
                                            value="{{ $employeeSalaryDetails['bonusEarning'] }}">

                                        <input type="number" hidden name="bonus_percentage"
                                            value="{{ $payrollSetting->bonus }}">

                                        <input type="number" hidden name="leave_amount"
                                            value="{{ $employeeSalaryDetails['earnLeaveAmount'] }}">

                                        <input type="number" hidden name="earned_leave_balance"
                                            value="{{ $employeeSalaryDetails['earnedLeaveBalance'] }}">

                                        <input type="number" hidden name="earned_leave"
                                            value="{{ $employeeSalaryDetails['earnLeaveDays'] }}">

                                        <input type="number" hidden name="earned_leave_total"
                                            value="{{ $employeeSalaryDetails['earnedLeaveTotal'] }}">

                                        <input type="number" hidden name="service_charge"
                                            value="{{ $employeeSalaryDetails['serviceCharge'] }}">

                                        <input type="number" hidden name="service_charge_percentage"
                                            value="{{ $payrollSetting->service_charge }}">

                                        <input type="number" hidden name="retained_service_charge"
                                            value="{{ $employeeSalaryDetails['serviceCharge'] + $employeeSalaryDetails['retainedServiceChargeAmount'] }}">

                                        <input type="number" hidden name="retained_bonus"
                                            value="{{ $employeeSalaryDetails['bonusEarning'] + $employeeSalaryDetails['retainedBonusAmount'] }}">

                                        <input type="number" hidden name="retained_leave_amount"
                                            value="{{ $employeeSalaryDetails['earnLeaveAmount'] + $employeeSalaryDetails['retainedLeaveAmount'] }}">

                                        <input type="number" hidden name="retained_attendance_bonus"
                                            value="{{ $employeeSalaryDetails['attendanceBonus'] + $employeeSalaryDetails['retainedAttendanceBonusAmount'] }}">

                                        <tr>
                                            <td>Earned Leave Days</td>
                                            <td>{{ $employeeSalaryDetails['earnLeaveDays'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Earned Leave Amount</td>
                                            <td>{{ $employeeSalaryDetails['earnLeaveAmount'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Bonus Amount</td>
                                            <td>{{ $employeeSalaryDetails['bonusEarning'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Service Charge</td>
                                            <td>{{ $employeeSalaryDetails['serviceCharge'] }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Total EL, Service Charge & Bonus Amount</b></td>
                                            <td>{{ $employeeSalaryDetails['bonusAndEarnLeaveTotal'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Retained Earnings</td>
                                            <td>{{ $employeeSalaryDetails['BonusEarnLeaveAttendanceBonusServiceChargeTotal'] }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Amount</b></td>
                                            <td>{{ number_format($employeeSalaryDetails['BonusEarnLeaveAttendanceBonusServiceChargeTotal'] + $employeeSalaryDetails['bonusAndEarnLeaveTotal'], 2, '.', '') }}
                                            </td>
                                        </tr>
                                    </tbody>

                                </table>
                            </div>
                        </div>
                        <br>
                        <div class="form-actions">
                            <div class="row text-center">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-info btn_style"><i class="fa fa-check"></i>
                                        @lang('common.save')</button>
                                </div>
                            </div>
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
