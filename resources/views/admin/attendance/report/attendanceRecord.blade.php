@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.attendance_record')
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
        $("#attendanceRecord").validate();
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

    <hr>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {{-- <p class="text-center">
                            <b style="font-size: 12px"><span style="color: green">Attendance Devices -
                                    Green,</span>
                                <span style="color: blue">Mobile Devices - Blue,</span>
                                <span style="color: red">Manual Correction - Red,</span>
                                <span style="color: orange">Access Device - Orange.</span>
                            </b>
                        </p> --}}
                        <div class="row">
                            <div id="searchBox">

                                {{ Form::open(['route' => 'attendanceRecord.attendanceRecord', 'id' => 'attendanceRecord']) }}

                                <div class="form-group">

                                    <div class="col-md-2"></div>

                                    <div class="col-sm-3" style="margin-left:24px;">
                                        <div class="form-group">
                                            <label class="control-label" for="fdate">@lang('common.from_date')<span
                                                    class="validateRq">*</span>:</label>
                                            <input type="text" class="form-control dateField" style="height: 35px;"
                                                readonly placeholder="@lang('common.from_date')" id="fdate" name="fdate"
                                                value="@if (isset($fdate)) {{ $fdate }}@else {{ dateConvertDBtoForm(date('Y-m-01')) }} @endif"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-sm-3" style="margin-left:24px;">
                                        <div class="form-group">
                                            <label class="control-label" for="tdate">@lang('common.to_date')<span
                                                    class="validateRq">*</span>:</label>
                                            <input type="text" class="form-control dateField" style="height: 35px;"
                                                readonly placeholder="@lang('common.to_date')" id="tdate" name="tdate"
                                                value="@if (isset($tdate)) {{ $tdate }}@else {{ dateConvertDBtoForm(date('Y-m-t')) }} @endif"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-sm-0"></div>
                                    <div class="col-sm-1">
                                        <label class="control-label col-sm-1 text-white"
                                            for="email">@lang('common.date')</label>
                                        <input type="submit" id="filter" style="margin-top: 2px; width: 100px;"
                                            class="btn btn-info " value="@lang('common.filter')">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        <br>
                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered" style="font-size: 12px">
                                <thead class="tr_header">
                                    <tr>
                                        <th style="width:80px;">@lang('common.serial')</th>
                                        <th>Employee Id</th>
                                        <th>@lang('common.name')</th>
                                        <th>DateTime</th>
                                        <th>Retrived at</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{ $serial = null }}
                                    @foreach ($results as $value)
                                        <tr>
                                            <td style="width:100px;">
                                                <p style="color: black">{{ ++$serial }}</p>
                                            </td>
                                            <td>
                                                <p style="color: black">{{ $value->ID }} </p>
                                            </td>
                                            <td>
                                                <p style="color: black">
                                                    {{ $value->fullName != " " ? $value->fullName : 'User Not Found' }}
                                                </p>
                                            </td>
                                            <td>
                                                <p style="color: black">{{ $value->datetime }} </p>
                                            </td>
                                            <td>
                                                <p style="color: black">{{ $value->updated_at }} </p>
                                            </td>
                                        </tr>
                                    @endforeach
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
