@extends('admin.master')
@section('content')
@section('title')
    @lang('overtime.my_overtime_report')
@endsection
<style>
    .employeeName {
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

    /*
  tbody {
   display:block;
   height:500px;
   overflow:auto;
  }
  thead, tbody tr {
   display:table;
   width:100%;
   table-layout:fixed;
  }
  thead {
   width: calc( 100% - 1em )
  }*/

</style>
<script>
    jQuery(function() {
        $("#monthlyOvertime").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('overtime.my_over_time')</a></li>
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
                                {{ Form::open(['route' => 'myOverTimeReport.myOverTimeReport', 'id' => 'monthlyOvertime']) }}
                                <div class="col-md-1"></div>
                                <div class="col-md-3">
                                    <div class="form-group employeeName">
                                        <label class="control-label" for="email">@lang('common.employee')<span
                                                class="validateRq">*</span></label>
                                        <select class="form-control employee_id select2 required" required
                                            name="employee_id">
                                            @foreach ($employeeList as $value)
                                                <option value="{{ $value->employee_id }}">{{ $value->first_name }}
                                                    {{ $value->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.from_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.from_date')" name="from_date"
                                            value="@if (isset($from_date)) {{ $from_date }}@else {{ dateConvertDBtoForm(date('Y-m-01')) }} @endif">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.to_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.to_date')" name="to_date"
                                            value="@if (isset($to_date)) {{ $to_date }}@else {{ dateConvertDBtoForm(date('Y-m-t', strtotime(date('Y-m-01')))) }} @endif">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter" style="margin-top: 25px; width: 100px;"
                                            class="btn btn-info " value="@lang('common.filter')">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        <hr>
                        <h4 class="text-right">
                            @if (isset($from_date))
                                @if (count($results) > 0)
                                    <a class="btn btn-success" style="color: #fff"
                                        href="{{ URL('downloadMyOverTime/?employee_id=' . $employee_id . '&from_date=' . $from_date . '&to_date=' . $to_date) }}"><i
                                            class="fa fa-download fa-lg" aria-hidden="true"></i>
                                        @lang('common.download') PDF</a>
                                @endif
                            @else
                                @if (count($results) > 0)
                                    <a class="btn btn-success" style="color: #fff"
                                        href="{{ URL('downloadMyOverTime/?employee_id=' .session('logged_session_data.employee_id') .'&from_date=' .dateConvertDBtoForm(date('Y-m-01')) .'&to_date=' .dateConvertDBtoForm(date('Y-m-t', strtotime(date('Y-m-01'))))) }}"><i
                                            class="fa fa-download fa-lg" aria-hidden="true"></i>
                                        @lang('common.download') PDF</a>
                                @endif
                            @endif
                        </h4>
                        <div class="table-responsive">
                            <table id="" class="table table-bordered">
                                <thead class="tr_header">
                                    <tr>
                                        <th style="width:100px;">@lang('common.serial')</th>
                                        <th>@lang('common.date')</th>
                                        <th>@lang('common.employee_name')</th>
                                        <th>@lang('attendance.in_time')</th>
                                        <th>@lang('attendance.out_time')</th>
                                        <th>@lang('attendance.working_time')</th>
                                        <th>@lang('attendance.over_time')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalPresent = 0;
                                    $totalHour = 0;
                                    $totalMinit = 0;
                                    $totalAbsence = 0;
                                    ?>

                                    {{ $serial = null }}
                                    @if (count($results) > 0)
                                        @foreach ($results as $value)
                                            @if ($value['workingHour'] < $value['working_time'])
                                                <tr>
                                                    <td style="width:100px;">{{ ++$serial }}</td>
                                                    <td>{{ $value['date'] }}</td>
                                                    <td>{{ $value['fullName'] }}</td>
                                                    <td>
                                                        <?php
                                                        if ($value['in_time'] != '') {
                                                            echo $value['in_time'];
                                                        } else {
                                                            echo '--';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        if ($value['out_time'] != '') {
                                                            echo $value['out_time'];
                                                        } else {
                                                            echo '--';
                                                        }
                                                        ?>
                                                    </td>

                                                    <td>
                                                        <?php
                                                        if ($value['working_time'] == '') {
                                                            echo '--';
                                                        } else {
                                                            if ($value['working_time'] != '00:00:00') {
                                                                echo $d = date('H:i', strtotime($value['working_time']));
                                                        
                                                                $hour_minit = explode(':', $d);
                                                        
                                                                $totalHour += $hour_minit[0];
                                                                $totalMinit += $hour_minit[1];
                                                            } else {
                                                                echo 'One Time Punch';
                                                            }
                                                        }
                                                        
                                                        ?>
                                                    </td>

                                                    <td>
                                                        <?php
                                                        if ($value['workingHour'] == '') {
                                                            echo '--';
                                                        } else {
                                                            $workingHour = new DateTime($value['workingHour']);
                                                            $workingTime = new DateTime($value['working_time']);
                                                            if ($workingHour < $workingTime) {
                                                                $interval = $workingHour->diff($workingTime);
                                                                $overtime = $interval->format('%H:%I');
                                                                echo $overtime;
                                                            } else {
                                                                echo '--';
                                                            }
                                                        }
                                                        ?>
                                                    </td>

                                                </tr>
                                            @endif
                                        @endforeach
                                        {{-- @else
                                        <tr>
                                            <td colspan="7">@lang('common.no_data_available') !</td>
                                        </tr> --}}
                                    @endif
                                    <?php
                                    $total_working_hour = ($totalHour * 60 + $totalMinit) / 60;
                                    $total_work_minit = $totalHour * 60 + $totalMinit;
                                    $totaltime = $total_work_minit / 60;
                                    $totalHour = floor($total_work_minit / 60);
                                    $totalMinit = ($totaltime - $totalHour) * 60;
                                    $total_workHour = sprintf('%02d', $totalHour) . ':' . sprintf('%02d', $totalMinit);
                                    ?>
                                    @if (count($results) > 0 && $total_working_hour > $value['working_time'])
                                        <?php
                                        $totalPresent += 1;
                                        ?>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td style="background: #eee"><b>@lang('overtime.expected_working_hour'):
                                                    &nbsp;</b></td>
                                            <td style="background: #eee">
                                                @php
                                                    $expected_hour = $totalPresent * 8.5 * $serial;
                                                    $actual_time = $totalPresent * 8.5 * $serial * 60;
                                                    $total_actual_time = $actual_time / 60;
                                                    $total_actual_Hour = floor($actual_time / 60);
                                                    $total_actual_Minit = ($total_actual_time - $total_actual_Hour) * 60;
                                                    $total_actual_workHour = sprintf('%02d', $total_actual_Hour) . ':' . sprintf('%02d', $total_actual_Minit);
                                                @endphp
                                                <b>{{ $total_actual_workHour }}</b>
                                                @lang('common.hours')
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td style="background: #eee"><b>@lang('overtime.actual_working_hour'):
                                                    &nbsp;</b></td>
                                            <td style="background: #eee"><b>{{ $total_workHour }}</b>
                                                @lang('common.hours')</td>
                                        </tr>
                                        <?php
                                        $overtime_hour = $totalHour - $total_actual_Hour;
                                        $overtime_minit = $totalMinit - $total_actual_Minit;
                                        $total_overtime_Hour = sprintf('%02d', $overtime_hour) . ':' . sprintf('%02d', $overtime_minit);
                                        ?>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td style="background: #eee"><b>@lang('overtime.over_time_hour'):
                                                    &nbsp;</b>
                                            </td>
                                            <td style="background: #eee"><b>
                                                    @if ($total_actual_Hour > 0)
                                                        {{ $total_overtime_Hour }}
                                                    @else
                                                        0
                                                    @endif
                                                </b> @lang('common.hours')</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="6">@lang('common.no_data_available') !</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
