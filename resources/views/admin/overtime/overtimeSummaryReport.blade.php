@extends('admin.master')
@section('content')
@section('title')
    @lang('overtime.overtime_summary_report')
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
        $("#overtimeSummaryReport").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('overtime.overtime_summary')</a></li>
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
                        <div id="loader" class="center"></div>
                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open(['route' => 'overtimeSummaryReport.overtimeSummaryReport', 'id' => 'overtimeSummaryReport']) }}
                                <div class="col-md-3"></div>

                                <div class="col-md-4">
                                    <label class="control-label" for="email">@lang('common.month')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control monthField required" readonly
                                            placeholder="@lang('common.month')" name="month"
                                            value="@if (isset($month)) {{ $month }}@else {{ date('Y-m') }} @endif">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter" style="margin-top: 25px; width: 100px;"
                                            class="btn btn-info filter" value="@lang('common.filter')">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        @if (count($results) > 0)
                            <h4 class="text-right">
                                @if (isset($month))
                                    <a target="_blank" class="btn btn-success" style="color: #fff"
                                        href="{{ URL('downloadOverTimeSummaryReport/' . $month) }}"><i
                                            class="fa fa-download fa-lg" aria-hidden="true"></i>
                                        @lang('common.download') PDF</a>
                                @else
                                    <a class="btn btn-success" style="color: #fff"
                                        href="{{ URL('downloadOverTimeSummaryReport/' . date('Y-m')) }}"><i
                                            class="fa fa-download fa-lg" aria-hidden="true"></i>
                                        @lang('common.download') PDF</a>
                                @endif
                            </h4>
                        @endif
                        <div class="data">
                            @include('admin.overtime.table.summaryTable')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

