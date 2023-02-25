@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.employee_attendance')
@endsection
<style>
    .departmentName {
        position: relative;
    }

    #department_id-error {
        position: absolute;
        top: 66px;
        left: 0;
        width: 100%;
        width: 100%;
        height: 100%;
    }
</style>
<script>
    jQuery(function() {
        $("#employeeAttendance").validate();
    });
</script>
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
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">ï¿½</button>
                                <i
                                    class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">ï¿½</button>
                                <i
                                    class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif
                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open(['route' => 'manualAttendance.filter', 'id' => 'employeeAttendance', 'method' => 'GET']) }}
                                <div class="col-md-2"></div>
                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.name')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <select class="form-control employee_id select2 required" required
                                            name="employee_id">
                                            <option value="">---- @lang('common.please_select') ----</option>
                                            @foreach ($employeeList as $value)
                                                <option value="{{ $value->employee_id }}"
                                                    @if (isset($_REQUEST['employee_id'])) @if ($_REQUEST['employee_id'] == $value->employee_id) {{ 'selected' }} @endif
                                                    @endif>
                                                    {{ $value->first_name . ' ' . $value->last_name . ' (' . $value->finger_id . ')' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.date')" name="date" id="manualDate"
                                            value="@if (isset($_REQUEST['date'])) {{ $_REQUEST['date'] }}@else{{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter"
                                            style="margin-top: 25px; width: 100px;height:36px" class="btn btn-info "
                                            value="@lang('common.filter')">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        <hr>
                        @if (isset($attendanceData))
                            {{ Form::open(['route' => 'manualAttendance.store', 'id' => 'employeeAttendance']) }}

                            <input type="hidden" name="employee_id" value="{{ $_REQUEST['employee_id'] }}">
                            <input type="hidden" name="date" value="{{ $_REQUEST['date'] }}">

                            <div class="table-responsive" style="height: 40vh;overflow:auto">
                                <table class="table table-bordered"
                                    style="position:absolute;table-layout:fixed;max-width:94%;min-width:92%;margin:auto">
                                    <thead class="tr_header">
                                        <tr>
                                            <th style="width:60px;">@lang('common.serial')</th>
                                            <th>Employee</th>
                                            <th>@lang('attendance.in_time')</th>
                                            <th>@lang('attendance.out_time')</th>
                                            <th>Brief Details</th>
                                            <th>@lang('attendance.updated_by')</th>
                                            {{-- <th>Action</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($attendanceData) > 0)
                                            @foreach ($attendanceData as $key => $value)
                                                <tr>
                                                    <td style="font-weight: 400">{{ $key + 1 }}</td>
                                                    <td style="font-weight: 400">{{ $value->fullName }}
                                                        <br>{{ $value->finger_id }}
                                                    </td>
                                                    <td style="font-weight: 400">
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-clock-o"></i>
                                                            </div>
                                                            <div class="">
                                                                <input type="hidden" name="finger_print_id[]"
                                                                    value="{{ $value->finger_id }}">
                                                                <input class="form-control" id="datetimepicker1"
                                                                    type="text" placeholder="@lang('attendance.in_time')"
                                                                    name="inTime[]" value="{{ $value->inTime }}"
                                                                    autocomplete="off" required>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td style="font-weight: 400;">
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-clock-o"></i>
                                                            </div>
                                                            <div class="">
                                                                <input class="form-control" id="datetimepicker2"
                                                                    type="text" placeholder="@lang('attendance.out_time')"
                                                                    name="outTime[]" value="{{ $value->outTime }}"
                                                                    autocomplete="off" required>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td style="font-weight: 400">
                                                        {{ 'Shift: ' }}
                                                        <b>{{ $value->shiftName ? $value->shiftName : 'NA' . ',' }}</b>
                                                        {{ 'EarlyBy: ' }}
                                                        <b>{{ $value->earlyBy != null ? date('H:i', strtotime($value->earlyBy)) : '00:00' . ',' }}</b>
                                                        {{ 'LateBy: ' }}
                                                        <b>{{ $value->lateBy != null ? date('H:i', strtotime($value->lateBy)) : '00:00' . ',' }}
                                                        </b><br>
                                                        {{ 'Work.Time: ' }}
                                                        <b>{{ $value->workingTime != null ? date('H:i', strtotime($value->workingTime)) : '00:00' . ',' }}</b>
                                                        {{ 'O.T: ' }}
                                                        <b>{{ $value->overTime != null ? date('H:i', strtotime($value->overTime)) : '00:00' . ',' }}</b>
                                                    </td>
                                                    <td style="font-weight: 400">
                                                        @php
                                                            $employee = App\Model\Employee::where('employee_id', $value->updatedBy)
                                                                ->select('first_name', 'last_name')
                                                                ->first();
                                                        @endphp
                                                        @if ($employee && $value->updatedAt && $value->updatedAt != null)
                                                            {{ $employee->first_name . ' ' . $employee->last_name }}
                                                            <br>
                                                            {{ date('Y-m-d h:i A', strtotime($value->updatedAt)) }}
                                                        @else
                                                            {{ 'NA' }} <br>
                                                            {{ '0000-00-00 00:00:00' }}
                                                        @endif
                                                    </td>
                                                    {{-- <td>
                                                        @if (count($attendanceData) > 0)
                                                            <button type="submit" class="btn btn-info btn_style"><i
                                                                    class="fa fa-check"></i>
                                                                @lang('common.save')</button>
                                                        @endif
                                                    </td> --}}

                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5">@lang('attendance.no_data_available')</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @if (count($attendanceData) > 0)
                                <div class="form-actions text-center">
                                    <div class="row">
                                        <div class="col-md-12 ">
                                            <button type="submit" id="save" class="btn btn-info btn_style"><i
                                                    class="fa fa-check"></i> @lang('common.save')</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {{ Form::close() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page_scripts')
<script>
    // date object conversion from string
    var startDate = $('.dateField').val().split("/");
    var startDay = new Date(startDate[2], startDate[1] - 1, startDate[0]);
    var year = startDay.getFullYear();
    var month = (startDay.getMonth() + 1) >= 9 ? (startDay.getMonth() + 1) : '0' + (startDay.getMonth() + 1);
    var date = startDay.getDate() >= 9 ? startDay.getDate() : '0' + startDay.getDate();
    var nextDate = startDay.getDate() >= 9 ? (startDay.getDate() + +1) : '0' + (startDay.getDate() + +1);
    var formattedDate = [year, month, date].join('-');
    var formattedNextDate = [year, month, nextDate].join('-');


    $('#datetimepicker1').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:ss',
        maxDate: new Date(),
    }).on('dp.change', function(e) {
        // disable submit button
        $('#save').prop('disabled', false);

        var inTime = $('#datetimepicker1').val();
        var filterDate = formattedDate + ' 00:00:00';

        if (inTime < filterDate) {

            $('#save').prop('disabled', true);

            // toasting error message 
            $.toast({
                heading: 'Warning',
                text: 'IN-TIME is Invalid...!',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'success',
                hideAfter: 3000,
                stack: 1
            });

            $('#datetimepicker1').val('')
        }
    });

    $('#datetimepicker2').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:ss',
        maxDate: new Date(),
    }).on('dp.change', function(e) {
        // disable submit button
        $('#save').prop('disabled', false);

        var outTime = $('#datetimepicker2').val();
        var filterNextDate = formattedNextDate + ' 00:00:00';

        if (outTime > filterNextDate) {

            $('#save').prop('disabled', true);

            // toasting error message 
            $.toast({
                heading: 'Warning',
                text: 'OUT-TIME is Invalid...!',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'success',
                hideAfter: 3000,
                stack: 1
            });

            $('#datetimepicker2').val('')
        }
    });
</script>
@endsection
