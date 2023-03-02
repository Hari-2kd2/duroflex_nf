@extends('admin.master')
@section('content')
@section('title')
    @lang('holiday.company_holiday_list')
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
        width: 320px;
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
        <div class="col-lg-6 col-md-6 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
        <div>
            <a href="{{ route('companyHoliday.create') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i
                    class="fa fa-plus-circle" aria-hidden="true"></i> @lang('company_holiday.add_company_holiday')</a>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                        aria-hidden="true">×</span></button>
                                @foreach ($errors->all() as $error)
                                    <strong>{!! $error !!}</strong><br>
                                @endforeach
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

                        <div class="row">
                            <div class="border bg-title"
                                style="border: 1px solid #b9b8b5;border-radius:4px;margin:12px;padding:12px">
                                <a class="pull-right" href="{{ route('companyHoliday.companyHolidayTemplate') }}">
                                    <div id="template1" class="btn btn-info btn-sm template1" value="Sample Format"
                                        type="submit">
                                        <i class="fa fa-download" aria-hidden="true"></i><span>
                                            Sample Format</span>
                                    </div>
                                </a>
                                <div class="row col-md-8 hidden-xs hidden-sm">
                                    <p class="border" style="margin-left:18px">
                                        <span><i class="fa fa-upload"></i></span>
                                        <span style="margin-left: 4px"><b>Upload Document Here (.xlsx)</b></span>
                                    </p>
                                    <form action="{{ route('companyHoliday.import') }}" method="post"
                                        enctype="multipart/form-data">
                                        {{ csrf_field() }}
                                        <div class="row">
                                            <div>
                                                <div class="col-md-4 text-right" style="margin-left:14px">
                                                    <input type="file" name="select_file"
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
                        </div>
                        <br>
                        <hr>

                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered">
                                <thead class="tr_header">
                                    <tr>
                                        <th>@lang('common.serial')</th>
                                        <th>@lang('common.id')</th>
                                        <th>@lang('common.name')</th>
                                        <th>@lang('common.branch')</th>
                                        <th>@lang('common.from_date')</th>
                                        <th>@lang('common.to_date')</th>
                                        <th>@lang('holiday.comment')</th>
                                        <th>Updated By</th>
                                        <th style="text-align: center;">@lang('common.action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $sl = null !!}
                                    @foreach ($results as $value)
                                        <tr
                                            class="{!! $value->company_holiday_id !!} @if (date('Y-m-d') <= $value->date) {{ 'success' }} @endif">
                                            <td style="width: 100px;">{!! ++$sl !!}</td>
                                            <td>{!! $value->employee->finger_id !!}</td>
                                            <td>{!! $value->employee->first_name . ' ' . $value->employee->last_name !!}</td>
                                            <td>{!! $value->employee->branch->branch_name !!}</td>
                                            <td>{!! dateConvertDBtoForm($value->fdate) !!}</td>
                                            <td>{!! dateConvertDBtoForm($value->tdate) !!}</td>
                                            <td>{!! $value->comment !!}</td>
                                            <td>{!! $value->updated_user->first_name . ' ' . $value->updated_user->last_name !!}
                                                {!! $value->updated_at !!}</td>
                                            <td style="width: 100px;">
                                                <a href="{!! route('companyHoliday.edit', $value->company_holiday_id) !!}"
                                                    class="btn btn-success btn-xs btnColor">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                </a>
                                                <a href="{!! route('companyHoliday.delete', $value->company_holiday_id) !!}" data-token="{!! csrf_token() !!}"
                                                    data-id="{!! $value->company_holiday_id !!}"
                                                    class="delete btn btn-danger btn-xs deleteBtn btnColor"><i
                                                        class="fa fa-trash-o" aria-hidden="true"></i></a>
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
