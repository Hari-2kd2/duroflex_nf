@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.daily_attendance')
@endsection
<script>
    jQuery(function() {
        $("#dailyAttendanceReport").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
    </div>
    @php
        // dd($departmentList);
    @endphp
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div id="searchBox">
                            <div class="col-md-0 col-lg-1 col-sm-0"></div>
                            {{ Form::open([
                                'route' => 'dailyAttendance.dailyAttendance',
                                'id' => 'dailyAttendanceReport',
                                'class' => 'form-horizontal',
                            ]) }}
                            <div class="form-group">

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label" for="branch_id">@lang('common.branch'):</label>
                                        <select name="branch_id" class="form-control branch_id  select2">
                                            <option value="">--- @lang('common.all') ---</option>
                                            @foreach ($branchList as $value)
                                                <option value="{{ $value->branch_id }}"
                                                    @if ($value->branch_id == $branch_id) {{ 'selected' }} @endif>
                                                    {{ $value->branch_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2" style="margin-left:24px;">
                                    <div class="form-group">
                                        <label class="control-label" for="department_id">@lang('common.department'):</label>
                                        <select name="department_id" class="form-control department_id  select2">
                                            <option value="">--- @lang('common.all') ---</option>
                                            @foreach ($departmentList as $value)
                                                <option value="{{ $value->department_id }}"
                                                    @if ($value->department_id == $department_id) {{ 'selected' }} @endif>
                                                    {{ $value->department_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @php
                                    $listStatus = [
                                        '1' => 'Present Report',
                                        '2' => 'Absent Report',
                                        '8' => 'Missing OUT Punch Report',
                                        '9' => 'Missing IN Punch Report',
                                        '10' => 'Less Hours Report',
                                    ];
                                @endphp
                                <div class="col-md-2" style="margin-left:24px;">
                                    <div class="form-group">
                                        <label class="control-label" for="email">@lang('common.status'):</label>
                                        <select name="attendance_status"
                                            class="form-control attendance_status  select2">
                                            <option value="">--- @lang('common.all') ---</option>
                                            @foreach ($listStatus as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if ($key == $attendance_status) {{ 'selected' }} @endif>
                                                    {{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-2" style="margin-left:24px;">
                                    <div class="form-group">
                                        <label class="control-label" for="email">@lang('common.date')<span
                                                class="validateRq">*</span>:</label>
                                        <input type="text" class="form-control dateField" style="height: 35px;"
                                            required readonly placeholder="@lang('common.date')" id="date"
                                            name="date"
                                            value="@if (isset($date)) {{ $date }}@else {{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                    </div>
                                </div>
                                <div class="col-sm-1">
                                    <label class="control-label col-sm-1 text-white"
                                        for="email">@lang('common.date')</label>
                                    <input type="submit" id="filter" style="margin-top: 2px; width: 100px;"
                                        class="btn btn-info " value="@lang('common.filter')">
                                </div>
                            </div>
                            {{ Form::close() }}

                        </div>
                        <hr>

                        @if (count($results) > 0 && $results != '')
                            <h4 class="text-right">
                                <a class="btn btn-success btn-sm" style="color: #fff"
                                    href="{{ URL('downloadDailyAttendanceExcel/?department_id=' . $department_id . '&date=' . $date . '&attendance_status=' . $attendance_status) }}"><i
                                        class="fa fa-download fa-lg" aria-hidden="true"></i> @lang('common.download')
                                </a>
                            </h4>
                        @endif

                        {{-- @if (count($results) > 0 && $results != '')
                            <h4 class="text-right">
                                <div id="excelexport" style="margin-top: 13px;margin-bottom: 12px;margin-right: 12px;">
                                    <button id="pdfexport" onclick="" class="btn btn-success">Export
                                        Report .xls</button>
                                </div>
                            </h4>
                        @endif --}}

                        <div id="btableData">
                            <div class="table-responsive">
                                <table id="" class="table table-bordered" style="font-size: 12px">
                                    <thead class="tr_header bg-title">
                                        <tr>
                                            <th style="width:50px;">@lang('common.serial')</th>
                                            <th style="font-size:12px;">@lang('common.date')</th>
                                            <th style="font-size:12px;width:200px;">@lang('common.employee_name')</th>
                                            <th style="font-size:12px;">@lang('common.id')</th>
                                            <th style="font-size:12px;">Contractor</th>
                                            <th style="font-size:12px;">Department</th>
                                            <th style="font-size:12px;">Shift</th>
                                            <th style="font-size:12px;">@lang('attendance.in_time')</th>
                                            <th style="font-size:12px;">@lang('attendance.out_time')</th>
                                            <th style="font-size:12px;">Duration</th>
                                            <th style="font-size:12px;">Early By</th>
                                            <th style="font-size:12px;">Late By</th>
                                            <th style="font-size:12px;">Overtime</th>
                                            <th style="font-size:12px;width:350px;">IN/OUT Records</th>
                                            <th style="font-size:12px;width:50px;">Status</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @php
                                            $sl = 0;
                                        @endphp
                                        @forelse ($results as $key => $data)
                                            {{-- <tr>
                                                <td colspan="14">
                                                    <strong>{{ $key }}</strong>
                                                </td>
                                            </tr> --}}

                                            @forelse ($data as $key1 => $value)
                                                @php
                                                    $zero = '00:00';
                                                    $isHoliday = false;
                                                    $holidayDate = '';
                                                @endphp
                                                <tr>
                                                    <td style="font-size:12px;">{{ ++$sl }}</td>
                                                    <td style="font-size:12px;">{{ $value->date }}</td>
                                                    <td style="font-size:12px;">{{ $value->fullName }}</td>
                                                    <td style="font-size:12px;">{{ $value->finger_print_id }}</td>
                                                    <td style="font-size:12px;">{{ $value->branch_name }}</td>
                                                    <td style="font-size:12px;">{{ $key }}</td>
                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->shift_name != '' && $value->shift_name != null) {
                                                                echo $value->shift_name;
                                                            } else {
                                                                echo 'NA';
                                                            }
                                                        @endphp
                                                    </td>
                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->in_time != '') {
                                                                echo $value->in_time;
                                                            } else {
                                                                echo '00:00';
                                                            }
                                                        @endphp
                                                    </td>
                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->out_time != '') {
                                                                echo $value->out_time;
                                                            } else {
                                                                echo '00:00';
                                                            }
                                                        @endphp
                                                    </td>

                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->working_time != null) {
                                                                echo date('H:i', strtotime($value->working_time));
                                                                // echo "<b style='color: black'>" . date('H:i', strtotime($value->working_time)) . '</b>';
                                                            } else {
                                                                echo '00:00';
                                                            }
                                                        @endphp
                                                    </td>

                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->early_by != null) {
                                                                echo date('H:i', strtotime($value->early_by));
                                                                // echo "<b style='color: black'>" . date('H:i', strtotime($value->working_time)) . '</b>';
                                                            } else {
                                                                echo '00:00';
                                                            }
                                                        @endphp
                                                    </td>
                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->late_by != null) {
                                                                echo date('H:i', strtotime($value->late_by));
                                                                // echo "<b style='color: black'>" . date('H:i', strtotime($value->working_time)) . '</b>';
                                                            } else {
                                                                echo '00:00';
                                                            }
                                                        @endphp
                                                    </td>
                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->over_time != null) {
                                                                echo date('H:i', strtotime($value->over_time));
                                                                // echo "<b style='color: black'>" . date('H:i', strtotime($value->working_time)) . '</b>';
                                                            } else {
                                                                echo '00:00';
                                                            }
                                                        @endphp
                                                    </td>

                                                    <td style="font-size:12px;">
                                                        @php
                                                            if ($value->in_out_time != null) {
                                                                echo $value->in_out_time;
                                                                // echo "<b style='color: green'>" . date('H:i', strtotime($value->late_by)) . '</b>';
                                                            } else {
                                                                echo '00/00/00 00:00:NA';
                                                                // echo "<b style='color: blue'>" . $zero . '</b>';
                                                            }
                                                        @endphp
                                                    </td>

                                                    <td style="font-size:12px;">
                                                        <?php
                                                        echo attStatus($value->attendance_status);
                                                        ?>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="19">@lang('common.no_data_available') !</td>
                                                </tr>
                                            @endforelse
                                        @empty
                                            <tr>
                                                <td colspan="19">@lang('common.no_data_available') !</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page_scripts')
<script>
    $(document).ready(function() {
        $("#excelexport").click(function(e) {
            //getting values of current time for generating the file name
            var dt = new Date();
            var day = dt.getDate();
            var month = dt.getMonth() + 1;
            var year = dt.getFullYear();
            var hour = dt.getHours();
            var mins = dt.getMinutes();
            var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
            //creating a temporary HTML link element (they support setting file names)
            var a = document.createElement('a');
            //getting data from our div that contains the HTML table
            var data_type = 'data:application/vnd.ms-excel';
            var table_div = document.getElementById('btableData');
            var table_html = table_div.outerHTML.replace(/ /g, '%20');
            a.href = data_type + ', ' + table_html;
            //setting the file name
            a.download = 'attendance_details_' + postfix + '.xls';
            //triggering the function
            a.click();
            //just in case, prevent default behaviour
            e.preventDefault();
        });


    });
</script>
@endsection
