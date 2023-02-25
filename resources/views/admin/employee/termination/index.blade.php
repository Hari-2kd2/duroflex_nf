@extends('admin.master')
@section('content')
@section('title')
    @lang('termination.termination_list')
@endsection

<style>
    .custom-file-upload {
        color: grey !important;
        display: inline-block;
        padding: 4px 4px 4px 4px;
        cursor: pointer;
        font-weight: normal;
        /* border: 2px solid #3f729b; */
        border-radius: 6px;
        width: 500px;
        height: 32px;

    }

    input::file-selector-button {
        display: inline-block;
        font-weight: bolder;
        color: white;
        border-radius: 4px;
        cursor: pointer;
        background: #41b3f9;
        /* background: #3f729b; */
        /* background: #7ace4c; */
        border-width: 1px;
        border: none;
        font-size: 12px;
        overflow: hidden;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        background-size: 12px 12px;
        padding: 4px 4px 4px 4px;
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
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <a href="{{ route('termination.create') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i
                    class="fa fa-plus-circle" aria-hidden="true"></i> @lang('termination.add_new_termination')</a>
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

                        <div class="border bg-title"
                            style="border: 1px solid #b9b8b5;border-radius:4px;margin:12px;padding:12px">
                            <a class="pull-right" href="{{ route('termination.terminationTemplate') }}">
                                <div id="template1" class="btn btn-info btn-sm template1" value="Sample Format"
                                    type="submit">
                                    <i class="fa fa-download" aria-hidden="true"></i><span>
                                        Sample Format</span>
                                </div>
                            </a>
                            <div class="row hidden-xs hidden-sm">
                                <p class="border" style="margin-left:18px">
                                    <span><i class="fa fa-upload"></i></span>
                                    <span style="margin-left: 4px"><b>Upload Excel Document.</b></span>
                                </p>
                                <form action="{{ route('termination.terminationimport') }}" method="post"
                                    enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <div class="row">
                                        <div>
                                            <div class="col-md-4 text-right" style="margin-left:14px">
                                                <input type="file" name="termination"
                                                    class="form-control custom-file-upload">
                                            </div>
                                            <div class="col-sm-1">
                                                <button class="btn btn-success btn-sm" type="submit"><span><i
                                                            class="fa fa-upload" aria-hidden="true"></i></span>
                                                    Upload</button>
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                        <br>
                        {{-- <div id="searchBox" hidden>
                            <div class="col-md-3"></div>
                            {{ Form::open([
                                'route' => 'termination.index',
                                'id' => 'terminationReport',
                                'class' => 'form-horizontal',
                                'method' => 'GET',
                            ]) }}
                            <div class="form-group">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label" for="email">Terminated By:</label>
                                        <select class="form-control terminate_by select2" name="terminate_by">
                                            <option value="">--- @lang('common.all') ---</option>
                                            @foreach ($employeeList as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if (isset($_REQUEST['terminate_by'])) @if ($_REQUEST['terminate_by'] == $value->employee_id) {{ 'selected' }} @endif
                                                    @endif
                                                    >{{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
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

                        </div> --}}

                        @if (count($results) > 0)
                            <div class="row" style="margin-right: 0px;">
                                <a href="{{ route('termination.report', ['terminate_by' => $terminate_by]) }}"
                                    class="btn btn-success btn-sm pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"
                                    style="color:white;"><i class="fa fa-download" aria-hidden="true"></i>
                                    @lang('salary.download_salaryreport')</a>
                            </div><br>
                        @endif

                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered">
                                <thead>
                                    <tr class="tr_header">
                                        <th>@lang('common.serial')</th>
                                        <th>@lang('common.employee_name')</th>
                                        <th>@lang('termination.subject')</th>
                                        <th>@lang('termination.termination_type')</th>
                                        <th>@lang('termination.notice_date')</th>
                                        <th>@lang('termination.termination_date')</th>
                                        <th>@lang('termination.terminated_by')</th>
                                        <th>@lang('common.status')</th>
                                        <th style="text-align: center;">@lang('common.action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $sl = null !!}
                                    @foreach ($results as $value)
                                        <tr class="{!! $value->termination_id !!}">
                                            <td style="width: 100px;">{!! ++$sl !!}</td>
                                            <td>
                                                @if (isset($value->terminateTo->first_name))
                                                    {{ $value->terminateTo->first_name }}
                                                    {{ $value->terminateTo->last_name }}
                                                @endif
                                            </td>
                                            <td>{!! $value->subject !!}</td>
                                            <td>{!! $value->termination_type !!}</td>
                                            <td>{!! dateConvertDBtoForm($value->notice_date) !!}</td>
                                            <td>{!! dateConvertDBtoForm($value->termination_date) !!}</td>
                                            <td>
                                                @if (isset($value->terminateBy->first_name))
                                                    {{ $value->terminateBy->first_name }}
                                                    {{ $value->terminateBy->last_name }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($value->status == 1)
                                                    <span class="label label-info">Pending</span>
                                                @else
                                                    <span class="label label-success">Approved</span>
                                                @endif
                                            </td>
                                            <td style="width: 100px;">
                                                <a title="View Details"
                                                    href="{{ route('termination.show', $value->termination_id) }}"
                                                    class="btn btn-primary btn-xs btnColor">
                                                    <i class="glyphicon glyphicon-th-large" aria-hidden="true"></i>
                                                </a>
                                                @if ($value->status != 2)
                                                    <a href="{!! route('termination.edit', $value->termination_id) !!}"
                                                        class="btn btn-success btn-xs btnColor">
                                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                    </a>
                                                    <a href="{!! route('termination.delete', $value->termination_id) !!}"
                                                        data-token="{!! csrf_token() !!}"
                                                        data-id="{!! $value->termination_id !!}"
                                                        class="delete btn btn-danger btn-xs deleteBtn btnColor"><i
                                                            class="fa fa-trash-o" aria-hidden="true"></i></a>
                                                @endif
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
