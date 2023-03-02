@extends('admin.master')
@section('content')
@section('title')
    @lang('salary.generation')
@endsection
<style>
    .departmentName {
        position: relative;
    }

    #employee_id-error {
        position: absolute;
        top: 66px;
        left: 0;
        width: 100%he;
        width: 100%;
        height: 100%;
    }

    .animated-progress {
        width: 300px;
        height: 30px;
        border-radius: 5px;
        margin: 20px 10px;
        border: 1px solid rgb(189, 113, 113);
        overflow: hidden;
        position: relative;
    }

    .animated-progress span {
        height: 100%;
        display: block;
        width: 0;
        color: rgb(255, 251, 251);
        line-height: 30px;
        position: absolute;
        text-align: end;
        padding-right: 5px;
    }

    .progress-blue span {
        background-color: blue;
    }

    .progress-green span {
        background-color: green;
    }

    .progress-purple span {
        background-color: indigo;
    }

    .progress-red span {
        background-color: red;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 36px;
        border: 1px solid #E4E6EB;
        border-radius: 4px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #EEEFEF;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 20px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
        border-radius: 12px;
    }

    input:checked+.slider {
        background-color: #3E739B;
        /* animation: color 1s linear infinite;
          transform-origin: 0px 50px;
          animation-fill-mode: forwards;
          animation-direction: alternate; */
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #3E739B;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(20px);
        -ms-transform: translateX(20px);
        transform: translateX(20px);
        /* animation: rectangle 1s linear infinite;
          transform-origin: 0px 50px;
          animation-fill-mode: forwards;
          animation-direction: alternate; */
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 4px;
    }

    .slider.round:before {
        border-radius: 4px;
    }

    @keyframes rectangle {
        from {
            -webkit-transform: translateX(0px);
            -ms-transform: translateX(0px);
            transform: translateX(0px);
        }

        to {
            -webkit-transform: translateX(50px);
            -ms-transform: translateX(50px);
            transform: translateX(50px);
        }
    }

    @keyframes color {
        from {
            background-color: #EEEFEF;
        }

        to {
            background-color: #3E739B;
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
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
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

                        <div id="searchBox">
                            <div class="row">

                                {{ Form::open(['route' => 'regeneratePayroll.regeneratePayroll', 'id' => 'regeneratePayroll', 'method' => 'POST']) }}

                                <div class="col-md-1"></div>

                                <div class="col-md-4">
                                    <label for="exampleInput">@lang('common.department')<span class="validateRq">*</span></label>
                                    <div class="form-group">
                                        {{-- <span class="input-group-addon"><i class="fa fa-search"></i></span> --}}
                                        <select class="form-control department_id select2 required" id="department_id"
                                            name="department_id">
                                            <option value="">---- @lang('common.please_select') ----</option>
                                            @foreach ($departmentList as $value)
                                                <option value="{{ $value->department_id }}"
                                                    @if (isset($_REQUEST['department_id'])) @if ($_REQUEST['department_id'] == $value->department_id) {{ 'selected' }} @endif
                                                    @endif
                                                    >{{ $value->department_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="exampleInput">@lang('common.branch')<span class="validateRq">*</span></label>
                                    <div class="form-group">
                                        {{-- <span class="input-group-addon"><i class="fa fa-search"></i></span> --}}
                                        <select class="form-control branch_id select2 required" id="branch_id"
                                            name="branch_id">
                                            <option value="">---- @lang('common.please_select') ----</option>
                                            @foreach ($branchList as $value)
                                                <option value="{{ $value->branch_id }}"
                                                    @if (isset($_REQUEST['branch_id'])) @if ($_REQUEST['branch_id'] == $value->branch_id) {{ 'selected' }} @endif
                                                    @endif
                                                    >{{ $value->branch_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <label for="exampleInput">Preview</label>
                                    <div class="input-group">
                                        <label class="switch">
                                            <input type="checkbox" name="preview"
                                                @if (isset($preview) && $preview == true) checked @endif>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <br>

                            <div class="row">

                                <div class="col-md-1"></div>

                                <div class="col-md-3">
                                    <label for="exampleInput">From @lang('common.date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text(
                                            'fdate',
                                            isset($fdate) ? $fdate : '',
                                            $attributes = [
                                                'class' => 'form-control required dateField',
                                                'id' => 'fdate',
                                                'placeholder' => __('common.date'),
                                                'autocomplete' => 'off',
                                            ],
                                        ) !!}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="exampleInput">To @lang('common.date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text(
                                            'tdate',
                                            isset($tdate) ? $tdate : '',
                                            $attributes = [
                                                'class' => 'form-control required dateField',
                                                'id' => 'tdate',
                                                'placeholder' => __('common.date'),
                                                'autocomplete' => 'off',
                                            ],
                                        ) !!}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="exampleInput">@lang('common.month')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text(
                                            'month',
                                            isset($month) ? $month : '',
                                            $attributes = [
                                                'class' => 'form-control required monthPicker',
                                                'id' => 'month',
                                                'placeholder' => __('common.month'),
                                                'autocomplete' => 'off',
                                            ],
                                        ) !!}
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <div class="form-group">
                                        <input type="submit" id="filter"
                                            style="margin-top: 25px; width: 68px;height:36px;"
                                            class="btn btn-instagram btn-md" value="Filter">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>

                        </div>
                        <hr style="margin-top: 0">

                        @if ((isset($month) && isset($fdate) && isset($tdate) && $dataSetCount > 0) || count($wageSheet) > 0)
                            <div class="row">
                                <form class="row pull-right" id="payrollExcel" style="margin-right:6px;"
                                    action="{{ route('regeneratePayroll.WageReportExcel') }}" method="POST">

                                    <input type="text" class="auth_id" name="auth_id" value="{{ auth()->id() }}"
                                        hidden>
                                    <input type="text" class="fdate" name="fdate" value="{{ $fdate }}"
                                        hidden>
                                    <input type="text" class="tdate" name="tdate" value="{{ $tdate }}"
                                        hidden>
                                    <input type="text" class="month" name="month" value="{{ $month }}"
                                        hidden>
                                    <input type="text" class="branch_id" name="branch_id"
                                        value="{{ $branch_id }}" hidden>
                                    <input type="text" class="department_id" name="department_id"
                                        value="{{ $department_id }}" hidden>
                                    <input type="text" class="_token" name="_token" value="{{ csrf_token() }}"
                                        hidden>
                                    <input type="text" class="wageSheet" name="wageSheet"
                                        value="{{ json_encode($wageSheet) }}" hidden>

                                    <button class="btn btn-success btn-sm " style="margin:12px"
                                        type="submit">Download
                                        Payroll</button>
                                </form>

                                {{-- @if (isset($preview) && $preview == true)
                                    <form class="row pull-right" id="payroll" style="margin-right:12px;"
                                        action="#" method="POST"
                                        href="{{ route('regeneratePayroll.storeWageReport') }}">

                                        <input type="text" name="auth_id" value="{{ auth()->id() }}" hidden>
                                        <input type="text" name="_token" value="{{ csrf_token() }}" hidden>
                                        <input type="text" name="wageSheet" value="{{ json_encode($wageSheet) }}"
                                            hidden>

                                        <button class="btn btn-instagram btn-sm " style="margin:12px"
                                            type="submit">Confirm
                                            Payroll</button>
                                    </form>
                                @endif --}}

                                {{-- <div class="row">
                                    <div class="pull-right" id="payroll"><button class="btn btn-instagram btn-sm"
                                            style="margin:12px" type="submit">Confirm
                                            Payroll</button></div>
                                </div> --}}

                            </div>
                        @endif

                        <div class="table-responsive">
                            @if (isset($month) && isset($preview) && $preview == true)
                                <table id="myTable" class="table table-bordered" style="font-size: 12px">
                                    <thead class="tr_header">
                                        <tr>
                                            <th class="text-center" colspan="1">#</th>
                                            <th class="text-center" colspan="7">Employee Info</th>
                                            <th class="text-center" colspan="4">Working Days</th>
                                            <th class="text-center" colspan="8">Allowances</th>
                                            <th class="text-center" colspan="6">Deduction</th>
                                            <th class="text-center" colspan="2">#</th>
                                            <th class="text-center" colspan="5">Pay Later / Settlement</th>
                                            <th class="text-center" colspan="2">#</th>
                                            <th class="text-center" colspan="1">#</th>
                                        </tr>
                                        <tr>
                                            <th>Sl.No</th>
                                            <th>Month</th>
                                            <th>Emp.Id</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Unit</th>
                                            <th>CostCenter</th>
                                            <th>Contractor</th>
                                            <th>No.of.W.Days</th>
                                            <th>P.H</th>
                                            <th>C.D.H</th>
                                            <th>Tot.Days</th>
                                            <th>Basic</th>
                                            <th>DA</th>
                                            <th>HRA</th>
                                            <th>Att.Bonus</th>
                                            <th>OT.Hour</th>
                                            <th>OTP/Hour</th>
                                            <th>OT.Earned</th>
                                            <th>WagesEarned</th>
                                            <th>Canteen</th>
                                            <th title="Basic & Da *{{ $payrollSetting->basic }} %">EPF</th>
                                            <th title="Gross Salary *{{ $payrollSetting->employee_esic }} %">ESIC</th>
                                            <th title="Basic & Da *{{ $payrollSetting->employer_pf }} %">EPF</th>
                                            <th title="Gross Salary *{{ $payrollSetting->employer_esic }} %">ESIC</th>
                                            <th>LWF</th>
                                            <th>GrossSalary</th>
                                            <th>NetSalary</th>
                                            <th title="Service Charge *{{ $payrollSetting->service_charge }} %">
                                                Ser.Charge
                                            </th>
                                            <th
                                                title="Basic & DA > 7000, 7000 * {{ $payrollSetting->bonus }} %, Basic & DA*{{ $payrollSetting->bonus }} %">
                                                Bonus</th>
                                            <th title="Accumulated Earned Leave Days">Acc.EL.Days</th>
                                            <th title="Earned Leave Days">EL.Days</th>
                                            <th title="EL.Days * Basic & Da Amount">EL.Amount</th>
                                            <th title="Total Working Hours">Manhours</th>
                                            <th title="Total Present">Mandays</th>
                                            <th title="Bonus + ServiceCharge + EPF + ESIC + GrossSalary">CTC</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($wageSheet as $key => $sheet)
                                            <tr class="{!! $sheet['finger_print_id'] !!}">
                                                {{-- @if ($sheet['duplicate'] == false) style="background-color: rgb(237, 253, 237)" @else style="background-color:  rgb(250, 202, 202);" @endif> --}}
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ date('m', strtotime($sheet['month'])) . '-' . $sheet['year'] }}
                                                </td>
                                                <td>{{ $sheet['finger_print_id'] }}</td>
                                                <td>{{ $sheet['fullName'] }}</td>
                                                <td>{{ $sheet['department_name'] }}</td>
                                                <td>{{ $sheet['unit_name'] }}</td>
                                                <td>{{ $sheet['cost_center_number'] }}</td>
                                                <td>{{ $sheet['contractor_name'] }}</td>
                                                <td>{{ $sheet['no_day_wages'] }}</td>
                                                <td>{{ $sheet['ph'] }}</td>
                                                <td>{{ $sheet['company_holiday'] }}</td>
                                                <td>{{ $sheet['total_days'] }}</td>
                                                <td>{{ $sheet['basic_amount'] }}</td>
                                                <td>{{ $sheet['da_amount'] }}</td>
                                                <td>{{ $sheet['hra_amount'] }}</td>
                                                <td>{{ $sheet['attendance_bonus'] }}</td>
                                                <td>{{ $sheet['ot_hours'] }}</td>
                                                <td>{{ $sheet['ot_per_hours'] }}</td>
                                                <td>{{ $sheet['ot_amount'] }}</td>
                                                <td>{{ $sheet['wages_amount'] }}</td>
                                                <td>{{ $sheet['canteen'] }}</td>
                                                <td>{{ $sheet['employee_pf'] }}</td>
                                                <td>{{ $sheet['employee_esic'] }}</td>
                                                <td>{{ $sheet['employer_pf'] }}</td>
                                                <td>{{ $sheet['employer_esic'] }}</td>
                                                <td>{{ $sheet['lwf'] }}</td>
                                                <td>{{ $sheet['gross_salary'] }}</td>
                                                <td>{{ $sheet['net_salary'] }}</td>
                                                <td>{{ $sheet['service_charge'] }}</td>
                                                <td>{{ $sheet['bonus_amount'] }}</td>
                                                <td>{{ $sheet['earned_leave_balance'] }}</td>
                                                <td>{{ $sheet['earned_leave'] }}</td>
                                                <td>{{ $sheet['leave_amount'] }}</td>
                                                <td>{{ $sheet['manhours'] }}</td>
                                                <td>{{ $sheet['manhour_days'] }}</td>
                                                <td>{{ $sheet['salary'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                            <div class="animated-progress progress-green pull-right hidden" id="progress-bar">
                                <span data-progress="{{ 60 }}"></span>
                            </div>
                        </div>

                        @if ((isset($month) && isset($fdate) && isset($tdate) && $dataSetCount > 0) || count($wageSheet) > 0)
                            <hr>
                            <div class="row">
                                <div class="pull-right" id="payroll"><button class="btn btn-instagram btn-sm"
                                        style="margin:12px" type="submit">Confirm
                                        Payroll</button></div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page_scripts')
<script type="text/javascript">
    $(document).ready(function() {

        // dropDown();

        $('.branch_id, .department_id').change(function(e) {
            e.preventDefault();
            dropDown();
        });

        function dropDown() {
            $(':input[type="submit"]').prop('disabled', true);
            var branch_id = $('.branch_id').val();
            var department_id = $('.department_id').val();
            if (branch_id != '' || department_id != '') {
                $(':input[type="submit"]').prop('disabled', false);
            } else {
                // toasting error message 
                $.toast({
                    heading: 'Warning',
                    text: 'Select respective filter...',
                    position: 'top-right',
                    loaderBg: '#ff6849',
                    icon: 'success',
                    hideAfter: 3000,
                    stack: 1
                });
            }
        }

        $(document).on('click', '#payroll', function() {

            var auth_id = $('.auth_id').val();
            var fdate = $('.fdate').val();
            var tdate = $('.tdate').val();
            var month = $('.month').val();
            var branch_id = $('.branch_id').val();
            var department_id = $('.department_id').val();
            var _token = $('._token').val();
            var wageSheet = $('.wageSheet').val();

            swal({
                title: "Are you sure?",
                text: "You will not be able to revert this action!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, confirm it!",
                closeOnConfirm: true
            }, function(isConfirm) {

                if (isConfirm) {
                    $.ajax({
                        url: 'regeneratePayroll/storeWageReport',
                        type: 'post',
                        data: {
                            auth_id: auth_id,
                            tdate: tdate,
                            fdate: fdate,
                            month: month,
                            branch_id: branch_id,
                            department_id: department_id,
                            wageSheet: wageSheet,
                            _token: _token,
                        },

                        success: function(data) {
                            console.log(data);
                            if (data == 'success') {
                                swal({
                                    title: "Confirmed!",
                                    text: "Operation in Progress...",
                                    type: "success"
                                });
                                setInterval(() => {
                                    location.reload();
                                }, 1000);
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "Something Error Found !, Please try again.",
                                    type: "error"
                                });
                            }
                        }

                    });
                } else {
                    swal("Cancelled", "Operation failed.", "error");
                }
            });
            return;
        });

        $(".animated-progress span").each(function() {
            $(this).animate({
                    width: $(this).attr("data-progress") + "%",
                },
                1000
            );
            $(this).text($(this).attr("data-progress") + "%");
        });

        // bootstrap month picker
        $(".monthPicker").datepicker({

            // properties of datepicker 
            format: "yyyy-mm",
            minViewMode: "months",
            dateFormat: 'yyyy-mm',
            todayHighlight: true,

            // startDate: new Date(),
            // endDate: new Date(),

        }).on('changeDate', function(e) {

            dropDown();

            var branch_id = $('.branch_id').val();
            var department_id = $('.department_id').val();

            // split date string of input field
            var startDate = $('#fdate').val().split("/");
            var endDate = $('#tdate').val().split("/");

            // date object conversion from string
            var startDay = new Date(startDate[2], startDate[1] - 1, startDate[0]);
            var endDay = new Date(endDate[2], endDate[1] - 1, endDate[0]);

            // find month of selected date
            var month = e.format('yyyy/mm/dd');
            var month = month.split("/");

            // find no of days between date
            var days = daysdifference(startDay, endDay);

            $(this).datepicker('hide');

            // error message to show in toaster
            var e1 = 'Month is not valid. choose between seected dates. !';
            var e2 = days +
                ' Days are selected, selected days should not be more than 31 days. !';

            if ((month[1] - 1) != (startDate[1] - 1) && (month[1] - 1) != (endDate[1] -
                    1) || days > 31) {
                // $('.monthPicker').datepicker('setDate', null);
                $(':input[type="submit"]').prop('disabled', true);

                // toasting error message 
                $.toast({
                    heading: 'Warning',
                    text: days <= 31 ? e1 : e2,
                    position: 'top-right',
                    loaderBg: '#ff6849',
                    icon: 'success',
                    hideAfter: 3000,
                    stack: 1
                });
            }
        });

        function daysdifference(startDay, endDay) {

            // Determine the time difference between two dates     
            var millisBetween = startDay.getTime() - endDay.getTime();

            // Determine the number of days between two dates  
            var days = millisBetween / (1000 * 3600 * 24);

            // Show the final number of days between dates     
            return Math.round(Math.abs(days));
        }

    });
</script>
@endsection('page_scripts')
