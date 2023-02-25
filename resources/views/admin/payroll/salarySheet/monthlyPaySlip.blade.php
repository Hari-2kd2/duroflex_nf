@extends('admin.master')
@section('content')
@section('title')
    @lang('salary_sheet.employee_payslip')
@endsection
<style>
    .table>tbody>tr>td {
        padding: 5px 7px;
    }

    table {
        border-collapse: collapse;
    }

    body {
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        height: auto;
    }

    table,
    td,
    th {
        border: 1px solid #000;
        padding: 5px;
        margin-top: -1px;
    }

    @media print {

        header,
        footer,
        aside,
        form,
        … {
            display: none;
        }

        article {
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        td {
            width: auto;
        }
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
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>
                    @lang('salary_sheet.employee_payslip')</div>
                <div class="col-md-12 text-right">
                    <h4 style="">
                        {{-- <a class="btn btn-success" style="color: #fff"
                            href="{{ URL('downloadPayslip/' . $paySlipId) }}"><i class="fa fa-download fa-lg"
                                aria-hidden="true"></i> @lang('common.download') PDF</a> --}}
                    </h4>
                </div>

                <div class="row" style="margin-top: 25px">

                    {{-- <div class="col-md-12 text-center">

                        <h3><strong> @lang('salary_sheet.employee_payslip') </strong>
                        </h3>
                    </div> --}}
                </div>

                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body" style="    padding: 18px 49px;">
                        <div class="row" style="border: 1px solid #ddd;padding: 26px 9px">
                            <div class="col-md-12" style="margin-bottom: 100px">

                                @php
                                    // dd($salaryDetails);
                                    
                                    $date = $salaryDetails->month_of_salary;
                                    $explode = explode('-', $date);
                                    $yearNum = $explode[0];
                                    $monthNum = $explode[1];
                                    $dateObj = DateTime::createFromFormat('!m', $monthNum);
                                    $monthName = $dateObj->format('F');
                                    $totalAllowance = 0;
                                    $otherAllowance = 0;
                                    $totalDeduction = 0;
                                @endphp

                                <div>
                                    <h1 style="text-align: center; font-size: 20px;font-weight: bold; margin: 0;">FORM
                                        NO.XXVIII</h1>
                                    <h2 style="text-align: center; font-size: 16px;font-weight: bold;margin: 0;">(See
                                        Rule 78 (1) (b) of
                                        Tamil
                                        Nadu Contract Labour (Regulation and Abolition) Rules, 1975)</h2>
                                    <p style="text-align: center;margin: 0;font-weight: bold;">WAGE SLIP for the month
                                        of <span
                                            style="border-bottom: 1px solid #000; font-weight: bold; font-size: 15px;">{{ $monthName . ' ' . $yearNum }}</span>
                                    </p>
                                </div>

                                <table class="col-md-12" cellpadding="0" cellspacing="0" style="margin-top: 40px;">
                                    <tbody>

                                        <tr>
                                            <td colspan="12">1. Name and Address of the Establishment : (Contract
                                                Name) : <strong>{{ $salaryDetails->branch_name }}</strong></td>
                                        </tr>

                                        <tr>
                                            <td colspan="10">2. Employee ID :
                                                <strong>{{ $salaryDetails->finger_id }}</strong>
                                            </td>
                                            <td colspan="1" style="text-align: left;">7. Total Working Days </td>
                                            <td colspan="1"><strong>{{ $salaryDetails->total_working_days }}</strong>
                                            </td>

                                        </tr>

                                        <tr>
                                            <td colspan="10">3. Name of the Employee:
                                                <strong>{{ $salaryDetails->first_name . ' ' . $salaryDetails->last_name }}</strong>
                                            </td>
                                            <td colspan="1">8. OT Hours</td>
                                            <td colspan="1">
                                                <strong>{{ $salaryDetails->total_over_time_hour }}</strong>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="10">4. Father's / Husband's Name :
                                                <strong>{{ $salaryDetails->father_name }}</strong>
                                            </td>
                                            <td colspan="1">9. UAN No</td>
                                            <td colspan="1"><strong>{{ $salaryDetails->pf_account_number }}</strong>
                                            </td>

                                        </tr>

                                        <tr>
                                            <td colspan="10">5. Date of Joining :
                                                <strong>{{ $salaryDetails->date_of_joining }}</strong>
                                            </td>
                                            <td colspan="1">10. ESIC No </td>
                                            <td colspan="1"><strong>{{ $salaryDetails->esi_card_number }}</strong>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td colspan="12">6. Designation :
                                                <strong>{{ $salaryDetails->designation_name }}</strong>
                                            </td>
                                        </tr>

                                        <table class="col-md-12">
                                            <tbody>

                                                <tr>
                                                    <td colspan="4"><strong> Wages Earned</strong></td>
                                                    <td colspan="2" class="text-center" style="font-weight: bold;">Rs
                                                    </td>
                                                    <td colspan="4" style="text-align: left; font-weight: bold;">
                                                        Deductions
                                                    </td>
                                                    <td colspan="2" class="text-center" style="font-weight: bold;">Rs
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td valign="top" colspan="4" class="text-left">
                                                        {{ '1)' }} @lang('salary_sheet.basic_salary'): <br>
                                                        @if (count($salaryDetailsToAllowance) > 0)
                                                            @foreach ($salaryDetailsToAllowance as $key => $allowance)
                                                                {{ $key + 2 . ') ' . $allowance->allowance_name }}:
                                                                <br>
                                                            @endforeach
                                                        @endif
                                                        {{ '4)' }} {{ 'OT Wages' }}: <br>
                                                        {{ '5)' }} {{ 'Leave Wages' }}: <br>
                                                        {{ '6)' }} {{ 'Other Allowance' }}: <br>
                                                    </td>
                                                    <td valign="top" colspan="2" class="text-center">
                                                        {{ number_format($salaryDetails->basic_salary) }} <br>
                                                        @if (count($salaryDetailsToAllowance) > 0)
                                                            @foreach ($salaryDetailsToAllowance as $key => $allowance)
                                                                @php
                                                                    $totalAllowance += $allowance->amount_of_allowance;
                                                                @endphp
                                                                {{ number_format($allowance->amount_of_allowance) }}
                                                                <br>
                                                            @endforeach
                                                        @endif
                                                        {{ number_format($salaryDetails->total_overtime_amount) }} <br>
                                                        @php
                                                            $leaveWage = $salaryDetails->total_leave * $salaryDetails->per_day_salary;
                                                            $attendacne_bonus = $salaryDetails->total_present >= 2 ? 250 : 0;
                                                            $otherAllowance = $salaryDetails->gross_salary - ($salaryDetails->basic_salary + $leaveWage + $totalAllowance);
                                                            $otherAllowance += $attendacne_bonus;
                                                            
                                                        @endphp
                                                        {{ number_format($leaveWage) }} <br>
                                                        {{ number_format($otherAllowance) }}
                                                        <br>
                                                    </td>
                                                    <td valign="top" colspan="3" class="text-left">
                                                        {{ '1)' }} Canteen: <br>
                                                        @if (count($salaryDetailsToDeduction) > 0)
                                                            @foreach ($salaryDetailsToDeduction as $key => $deduction)
                                                                {{ $key + 2 . ') ' . $deduction->deduction_name }}:
                                                                <br>
                                                            @endforeach
                                                        @endif
                                                        {{ '4)' }} LWF: <br>
                                                        {{ '5)' }} LOP / Other Deduction : <br>
                                                    </td>
                                                    <td valign="top" colspan="2" class="text-center">
                                                        {{ number_format($canteenDeduction['totalMonthlyDeduction']) }}
                                                        <br>
                                                        @if (count($salaryDetailsToDeduction) > 0)
                                                            @foreach ($salaryDetailsToDeduction as $key => $deduction)
                                                                @php
                                                                    $totalDeduction += $deduction->amount_of_deduction;
                                                                @endphp
                                                                {{ number_format($deduction->amount_of_deduction) }}
                                                                <br>
                                                            @endforeach
                                                        @endif
                                                        {{ $lwf }} <br>
                                                        {{ number_format($salaryDetails->total_absence_amount) }} <br>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" class="text-right"><strong>Gross Wage:</strong>
                                                    </td>
                                                    <td colspan="2" class="text-center" class="text-center"
                                                        style="font-weight:bold">
                                                        @php
                                                            $grossWage = $salaryDetails->basic_salary + $salaryDetails->total_overtime_amount + $totalAllowance + $leaveWage + $otherAllowance;
                                                        @endphp
                                                        {{ $grossWage }}
                                                    </td>
                                                    <td colspan="4" class="text-left"><strong>Total
                                                            Deduction:</strong></td>
                                                    <td colspan="2" class="text-center" style="font-weight:bold">
                                                        @php
                                                            $totalDeductionAmount = $totalDeduction + $salaryDetails->total_absence_amount + $lwf + $canteenDeduction['totalMonthlyDeduction'];
                                                        @endphp
                                                        {{ number_format($totalDeductionAmount) }}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td colspan="4" class="text-right"><strong>Net Amount:</strong>
                                                    </td>
                                                    <td colspan="2" class="text-center">
                                                        <strong
                                                            style="font-size: 20px;">{{ $salaryDetails->net_salary + $attendacne_bonus }}</strong>
                                                    </td>
                                                    <td colspan="6" class="text-left"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-md-6"></div>
                                <div class="col-md-6"></div>
                            </div>
                            <div class="col-md-6">
                                <p style="font-weight: 500;">@lang('salary_sheet.adminstrator_signature') ....</p>
                            </div>
                            {{-- <div class="col-md-4 text-center">
                                <p style="font-weight: 500;"> @lang('common.date') .... </p>
                            </div> --}}
                            <div class="col-md-6 text-right">
                                <p style="font-weight: 500;"> @lang('salary_sheet.employee_signature') .... </p>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
