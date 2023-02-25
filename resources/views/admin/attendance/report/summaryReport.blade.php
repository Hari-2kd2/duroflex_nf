@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.attendance_summary_report')
@endsection
<style>
    .present {
        color: #7ace4c;
        font-weight: 700;
        cursor: pointer;
    }

    .absence {
        color: #f33155;
        font-weight: 700;
        cursor: pointer;
    }

    .leave {
        color: #41b3f9;
        font-weight: 700;
        cursor: pointer;
    }

    .bolt {
        font-weight: 700;
    }
</style>
<script>
    jQuery(function() {
        $("#attendanceSummaryReport").validate();
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

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open([
                                    'route' => 'attendanceSummaryReport.attendanceSummaryReport',
                                    'id' => 'attendanceSummaryReport',
                                ]) }}
                                {{-- <div class="col-md-3"></div>

                                <div class="col-md-4">
                                    <label class="control-label" for="email">@lang('common.month')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control monthField required" readonly
                                            placeholder="@lang('common.month')" name="month"
                                            value="@if (isset($month)) {{ $month }}@else {{ date(' Y-m') }} @endif">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter" style="margin-top: 25px; width: 100px;"
                                            class="btn btn-info " value="@lang('common.filter')">
                                    </div>
                                </div> --}}

                                <div class="row">
                                    <div class="col-md-1 col-sm-0"></div>
                                    <div class="col-md-3 col-sm-3">
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
                                    <div class="col-md-3 col-sm-3">
                                        <label class="control-label" for="email">@lang('common.from_date')<span
                                                class="validateRq">*</span></label>
                                        <div class="form-group">
                                            <input type="text" class="form-control dateField required" readonly
                                                placeholder="@lang('common.from_date')" name="from_date"
                                                value="@if (isset($from_date)) {{ $from_date }}@else {{ dateConvertDBToForm(date('Y-m-01')) }} @endif">
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <label class="control-label" for="email">@lang('common.to_date')<span
                                                class="validateRq">*</span></label>
                                        <div class="form-group">
                                            <input type="text" class="form-control dateField required" readonly
                                                placeholder="@lang('common.to_date')" name="to_date"
                                                value="@if (isset($to_date)) {{ $to_date }}@else {{ dateConvertDBToForm(date('Y-m-t', strtotime(date('Y-m-01')))) }} @endif">
                                        </div>
                                    </div>
                                    <div class="col-md-1 col-sm-1">
                                        <div class="form-group">
                                            <input type="submit" id="filter" style="margin-top: 28px;width:100px"
                                                class="btn btn-instagram" value="@lang('common.filter')">
                                        </div>
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        @if (count($results) > 0 && $results != '')
                            <h4 class="text-right">
                                <a class="btn btn-success btn-sm" style="color: #fff"
                                    href="{{ URL('downloadSummaryAttendanceExcel/?from_date=' . $from_date . '&to_date=' . $to_date . '&branch_id=' . $branch_id) }}"><i
                                        class="fa fa-download fa-lg" aria-hidden="true"></i> @lang('common.download')
                                </a>
                            </h4>
                        @endif

                        <div class="table-responsive">
                            <table id="" class="table table-bordered table-striped table-hover"
                                style="font-size: 12px">
                                <thead class="tr_header">
                                    <tr>
                                        <th>@lang('common.serial')</th>
                                        <th>@lang('common.year')</th>
                                        <th colspan="1" class="totalCol">@lang('common.month')

                                        </th>
                                        <th colspan="{{ count($monthToDate) + 2 }}">
                                            {{ 'FP - Full Present,' }}&nbsp;
                                            {{ 'HP - Half Present,' }}&nbsp;
                                            {{ ' LOP - Loss of Pay,' }}&nbsp;
                                            {{ ' AA - Absent,' }}&nbsp;
                                            {{ ' HD - Holiday,' }}&nbsp;
                                            {{ ' OTP - One time Punch.' }}
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>#</th>
                                        <th>
                                            @if (isset($month))
                                                @php
                                                    
                                                    $exp = explode('-', $month);
                                                    echo $exp[0];
                                                @endphp
                                            @else
                                                {{ date('Y') }}
                                            @endif
                                        </th>
                                        <th>{{ $monthName }}</th>
                                        <th>#</th>
                                        @foreach ($monthToDate as $head)
                                            <th>{{ $head['day_name'] }}</th>
                                        @endforeach
                                        <th>@lang('attendance.worked_days')</th>

                                    </tr>
                                </thead>
                                @if (count($results) > 0)
                                    <tbody>
                                        <tr>
                                            <td>#</td>
                                            <th>@lang('common.employee_name')</th>
                                            <th>@lang('common.employee_id')</th>
                                            <th>@lang('common.branch')</th>
                                            @foreach ($monthToDate as $head)
                                                <th>{{ $head['day'] }}</th>
                                            @endforeach
                                            <th>#</th>

                                        </tr>

                                        @php
                                            $sl = null;
                                            $totalPresent = 0;
                                            $leaveData = [];
                                            $totalCol = 0;
                                            $totalWorkHour = 0;
                                            $totalGovDayWorked = 0;
                                        @endphp
                                        @foreach ($results as $key => $value)
                                            <tr>
                                                <td>{{ ++$sl }}</td>
                                                <td>{{ $value[0]['fullName'] }}</td>
                                                <td>{{ $key }}</td>
                                                <td>{{ $value[0]['branch_name'] }}</td>
                                                @foreach ($value as $v)
                                                    @php
                                                        if ($sl == 1) {
                                                            $totalCol++;
                                                        }
                                                        if ($v['attendance_status'] == 'present') {
                                                            // $totalPresent++;
                                                            if (isset($v['working_time'])) {
                                                                $workingTime = new DateTime($v['working_time']);
                                                                $fullDay = new DateTime(\App\Lib\Enumerations\PayrollConstant::$FULL_DAY);
                                                                $halfDay = new DateTime(\App\Lib\Enumerations\PayrollConstant::$HALF_DAY);
                                                                if ($workingTime >= $fullDay) {
                                                                    (float) ($totalPresent += 1);
                                                                    echo "<td><span style='color:#7ace4c ;font-weight:bold'>FP</span></td>";
                                                                } elseif ($workingTime >= $halfDay) {
                                                                    (float) ($totalPresent += 0.5);
                                                                    echo "<td><span style='color:#7ace4c ;font-weight:bold'>HP</span></td>";
                                                                } elseif ($workingTime < $halfDay && $workingTime != null) {
                                                                    (float) ($totalPresent += 0);
                                                                    echo "<td><span style='color:#f33155 ;font-weight:bold'>LOP</span></td>";
                                                                }
                                                            } else {
                                                                // $totalGovDayWorked++;
                                                                echo "<td><span style='color: #7ace4c ;font-weight:bold'>OTP</span></td>";
                                                            }
                                                        } elseif ($v['attendance_status'] == 'absence') {
                                                            echo "<td><span style='color:#f33155 ;font-weight:bold'>AA</span></td>";
                                                        } elseif ($v['attendance_status'] == 'leave') {
                                                            $leaveData[$key][$v['leave_type']][] = $v['leave_type'];
                                                            echo "<td><span style='color:#41b3f9 ;font-weight:bold'>LL</span></td>";
                                                        } elseif ($v['attendance_status'] == 'holiday') {
                                                            echo "<td><span style='color:turquoise ;font-weight:bold'>HD</span></td>";
                                                        } else {
                                                            echo '<td></td>';
                                                        }
                                                    @endphp
                                                @endforeach
                                                <td><span class="bolt">{{ $totalPresent }}</span></td>

                                                @php
                                                    $totalPresent = 0;
                                                    $totalGovDayWorked = 0;
                                                    
                                                @endphp
                                            </tr>
                                        @endforeach
                                        {{-- <script>
                                        {!! "$('.totalCol').attr('colspan',$totalCol+3);" !!}
                                    </script> --}}
                                    </tbody>
                                @else
                                    <tr>
                                        <td colspan="{{ count($monthToDate) + 5 }}">No data found</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
