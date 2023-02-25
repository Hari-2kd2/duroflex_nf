@extends('admin.master')
@section('content')
@section('title')
    @lang('overtime.daily_overtime_report')
@endsection
<script>
    jQuery(function() {
        $("#dailyOverTimeReport").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('overtime.daily_overtime')</a></li>
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
                        <div id="searchBox">
                            <div class="col-md-1"></div>
                            {{ Form::open(['route' => 'dailyOverTime.dailyOverTime', 'id' => 'dailyOverTimeReport', 'class' => 'form-horizontal']) }}
                            <div class="form-group">
                                <label class="control-label col-sm-2" for="email">@lang('common.date')<span
                                        class="validateRq">*</span>:</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control dateField" required readonly
                                        placeholder="@lang('common.date')" id="date" name="date"
                                        value="@if (isset($formData)) {{ $formData }}@else {{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                </div>
                                <div class="col-sm-3">
                                    <input type="submit" id="filter" style="margin-top: 2px; width: 100px;"
                                        class="btn btn-info " value="@lang('common.filter')">
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                        <hr>
                        @if (count($results) > 0)
                            <h4 class="text-right">
                                @if (isset($formData))
                                    <a target="_blank" class="btn btn-success" style="color: #fff"
                                        href="{{ URL('downloadDailyOverTime/' . dateConvertFormtoDB($formData)) }}"><i
                                            class="fa fa-download fa-lg" aria-hidden="true"></i>
                                        @lang('common.download') PDF</a>
                                @else
                                    <a class="btn btn-success" style="color: #fff"
                                        href="{{ URL('downloadDailyOverTime/' . date('Y-m-d')) }}"><i
                                            class="fa fa-download fa-lg" aria-hidden="true"></i>
                                        @lang('common.download') PDF</a>
                                @endif
                            </h4>
                        @endif
                        <div class="table-responsive">
                            <table id="" class="table table-bordered">
                                <thead class="tr_header">
                                    <tr>
                                        <th style="width:100px;">@lang('common.serial')</th>
                                        <th>@lang('common.date')</th>
                                        <th>@lang('common.employee_name')</th>
                                        <th>@lang('overtime.in_time')</th>
                                        <th>@lang('overtime.out_time')</th>
                                        <th>@lang('overtime.working_time')</th>
                                        <th>@lang('overtime.over_time')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $ot = '00:00';
                                    @endphp
                                    @if (count($results) > 0)
                                        @foreach ($results as $key => $data)
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <strong>{{ $key . '-Department' }}</strong>
                                                </td>
                                            </tr>
                                            @foreach ($data as $key1 => $value)
                                                @if ($value->workingHour < $value->working_time)
                                                    <tr>
                                                        <td>{{ ++$key1 }}</td>
                                                        <td>{{ $value->date }}</td>
                                                        <td>{{ $value->fullName }}</td>
                                                        <td>{{ $value->in_time }}</td>
                                                        <td>
                                                            @php
                                                                if ($value->out_time != '') {
                                                                    echo $value->out_time;
                                                                } else {
                                                                    echo '--';
                                                                }
                                                            @endphp
                                                        </td>

                                                        <td>
                                                            @php
                                                                if ($value->working_time != '00:00:00') {
                                                                    echo date('H:i', strtotime($value->working_time));
                                                                } else {
                                                                    echo 'One Time Punch';
                                                                }
                                                            @endphp
                                                        </td>
                                                        <td>
                                                            @php
                                                                
                                                                // dd($value);
                                                                if ($value->shift_type != 2) {
                                                                    $workingHour = new DateTime($value->workingHour);
                                                                    $workingTime = new DateTime($value->working_time);
                                                                    // dd($value->workingHour, $workingTime);
                                                                    if ($workingHour < $workingTime) {
                                                                        $interval = $workingHour->diff($workingTime);
                                                                        $ot = $interval->format('%H:%I');
                                                                        echo $interval->format('%H:%I');
                                                                    } else {
                                                                        echo '00:00';
                                                                    }
                                                                } else {
                                                                    $explodeString = explode(':', $value->workingHour);
                                                                    $time1 = $explodeString[0];
                                                                    $time2 = $explodeString[1];
                                                                    $time3 = $explodeString[2];
                                                                    $dateString = abs($time1) . ':' . $time2 . ':' . $time3;
                                                                    // dd($dateString);
                                                                    $RT = new DateTime('23:59:59');
                                                                    $addTime = date('H:i:s', strtotime($dateString . ' +24 hours'));
                                                                    $workingHour = new DateTime($addTime);
                                                                    $workingTime = new DateTime($value->working_time);
                                                                    $workingHour = $workingHour->diff($RT);
                                                                    dd($RT, $addTime, $dateString, $workingHour, $workingTime, $workingTime > $workingHour);
                                                                
                                                                    if ($workingHour > $workingTime) {
                                                                        echo 'Hello';
                                                                        // $interval = $workingHour->diff($workingTime);
                                                                        // $ot = $interval->format('%H:%I');
                                                                        // dd($ot);
                                                                        // echo $interval->format('%H:%I');
                                                                    } else {
                                                                        echo '00:00';
                                                                    }
                                                                }
                                                                
                                                            @endphp
                                                        </td>
                                                    </tr>
                                                    {{-- @else
                                                    <tr>
                                                        <td colspan="6">@lang('common.no_data_available') !</td>
                                                    </tr> --}}
                                                @endif
                                            @endforeach
                                        @endforeach
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
